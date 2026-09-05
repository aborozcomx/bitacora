<?php

namespace App\Http\Controllers;

use App\Models\BitacoraEmployee;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SalaryReportController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Default to current weekly cycle: Wednesday to following Thursday
        if (! $request->filled('start_date') || ! $request->filled('end_date')) {
            $now = now();
            if ($now->dayOfWeek < Carbon::WEDNESDAY) {
                $start = $now->copy()->previous(Carbon::WEDNESDAY);
            } else {
                $start = $now->copy()->startOfDay()->subDays($now->dayOfWeek - Carbon::WEDNESDAY);
            }
            $end = $start->copy()->addDays(8); // Wednesday to following week Thursday

            $startDate = $request->input('start_date', $start->toDateString());
            $endDate = $request->input('end_date', $end->toDateString());
        } else {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        $branchId = $request->input('branch_id');
        $userId = $request->input('user_id');
        $absenceFilter = $request->input('absence_filter', 'all');

        $userBranchIds = $user->hasRole('admin')
            ? null
            : $user->branches->pluck('id')->toArray();

        // Get employee payroll summary for period
        $payrollSummary = Employee::with('branch')
            ->when(! $user->hasRole('admin'), function ($query) use ($userBranchIds) {
                $query->whereIn('branch_id', $userBranchIds);
            })
            ->when($branchId, function ($query, $bId) {
                $query->where('branch_id', $bId);
            })
            ->get()
            ->map(function ($employee) use ($startDate, $endDate, $userId) {
                $entries = BitacoraEmployee::with(['bitacora.branch', 'bitacora.user'])
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('date', [$startDate, $endDate])
                            ->orWhere(function ($fallbackQ) use ($startDate, $endDate) {
                                $fallbackQ->whereNull('date')
                                    ->whereHas('bitacora', function ($bQ) use ($startDate, $endDate) {
                                        $bQ->whereBetween('date', [$startDate, $endDate]);
                                    });
                            });
                    })
                    ->whereHas('bitacora', function ($bQ) use ($userId) {
                        if ($userId) {
                            $bQ->where('user_id', $userId);
                        }
                    })
                    ->where('employee_id', $employee->id)->get();

                $totalRegularHours = $entries->where('is_absent', false)->sum('hours_worked');
                $totalOvertimeHours = $entries->where('is_absent', false)->sum('overtime_hours');

                $regularPay = $entries->where('is_absent', false)->sum(function ($entry) {
                    return $entry->hours_worked * $entry->base_rate_applied;
                });

                $overtimePay = $entries->where('is_absent', false)->sum(function ($entry) {
                    return $entry->overtime_hours * $entry->overtime_rate_applied;
                });

                $totalPay = $regularPay + $overtimePay;
                $bitacoraCount = $entries->pluck('bitacora_id')->unique()->count();

                $absentEntries = $entries->where('is_absent', true);
                $absencesCount = $absentEntries->count();
                $absencesDates = $absentEntries->map(function ($e) {
                    return $e->date ? (is_string($e->date) ? $e->date : $e->date->format('Y-m-d')) : null;
                })->filter()->values()->toArray();

                $partialEntries = $entries->where('is_absent', false)->where('is_partial_shift', true);
                $partialShiftsCount = $partialEntries->count();

                $bitacoras = $entries->map(function ($entry) {
                    $dateStr = $entry->date ? (is_string($entry->date) ? $entry->date : $entry->date->format('Y-m-d')) : ($entry->bitacora->date ?? null);
                    $isSunday = $dateStr ? Carbon::parse($dateStr)->isSunday() : false;

                    return [
                        'id' => $entry->bitacora_id,
                        'folio_number' => $entry->bitacora->folio_number ?? "Folio #{$entry->bitacora_id}",
                        'date' => $dateStr,
                        'is_sunday' => $isSunday,
                        'branch_name' => $entry->bitacora->branch->name ?? null,
                        'user_name' => $entry->bitacora->user->name ?? null,
                        'hours_worked' => (float) $entry->hours_worked,
                        'overtime_hours' => (float) $entry->overtime_hours,
                        'total_earned' => (float) $entry->total_earned,
                        'is_absent' => (bool) $entry->is_absent,
                        'is_partial_shift' => (bool) $entry->is_partial_shift,
                        'partial_shift_reason' => $entry->partial_shift_reason,
                    ];
                })->values()->toArray();

                $hasSunday = collect($bitacoras)->contains('is_sunday', true);

                // Group employee bitacoras by folio number to sum same folios across different dates
                $byFolioForEmployee = collect($bitacoras)
                    ->groupBy('folio_number')
                    ->map(function ($folioEntries, $folioNum) {
                        $datesList = $folioEntries->pluck('date')->filter()->unique()->values()->toArray();

                        return [
                            'folio_number' => $folioNum,
                            'dates' => $datesList,
                            'days_count' => count($datesList),
                            'total_hours_worked' => round((float) $folioEntries->where('is_absent', false)->sum('hours_worked'), 2),
                            'total_overtime_hours' => round((float) $folioEntries->where('is_absent', false)->sum('overtime_hours'), 2),
                            'total_earned' => round((float) $folioEntries->where('is_absent', false)->sum('total_earned'), 2),
                            'has_sunday' => $folioEntries->contains('is_sunday', true),
                            'daily_records' => $folioEntries->values()->toArray(),
                        ];
                    })
                    ->values()
                    ->toArray();

                return [
                    'employee_id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'full_name' => $employee->full_name,
                    'branch_name' => $employee->branch->name ?? 'N/A',
                    'base_hourly_rate' => $employee->base_hourly_rate,
                    'overtime_hourly_rate' => $employee->overtime_hourly_rate,
                    'total_regular_hours' => round($totalRegularHours, 2),
                    'total_overtime_hours' => round($totalOvertimeHours, 2),
                    'regular_pay' => round($regularPay, 2),
                    'overtime_pay' => round($overtimePay, 2),
                    'total_pay' => round($totalPay, 2),
                    'bitacora_count' => $bitacoraCount,
                    'unique_folios_count' => count($byFolioForEmployee),
                    'has_sunday' => $hasSunday,
                    'absences_count' => $absencesCount,
                    'absences_dates' => $absencesDates,
                    'partial_shifts_count' => $partialShiftsCount,
                    'bitacoras' => $bitacoras,
                    'by_folio' => $byFolioForEmployee,
                ];
            });

        if ($userId) {
            $payrollSummary = $payrollSummary->filter(fn ($item) => $item['bitacora_count'] > 0)->values();
        }

        if ($absenceFilter === 'with_absences') {
            $payrollSummary = $payrollSummary->filter(fn ($item) => $item['absences_count'] > 0)->values();
        } elseif ($absenceFilter === 'without_absences') {
            $payrollSummary = $payrollSummary->filter(fn ($item) => $item['absences_count'] === 0)->values();
        } elseif ($absenceFilter === 'with_partial_shifts') {
            $payrollSummary = $payrollSummary->filter(fn ($item) => $item['partial_shifts_count'] > 0)->values();
        }

        // Period-wide Folio Consolidation: sum across all employees for each folio
        $allEntries = BitacoraEmployee::with(['bitacora.branch', 'employee'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->orWhere(function ($fallbackQ) use ($startDate, $endDate) {
                        $fallbackQ->whereNull('date')
                            ->whereHas('bitacora', function ($bQ) use ($startDate, $endDate) {
                                $bQ->whereBetween('date', [$startDate, $endDate]);
                            });
                    });
            })
            ->whereHas('bitacora', function ($bQ) use ($userId, $branchId, $user, $userBranchIds) {
                if ($userId) {
                    $bQ->where('user_id', $userId);
                }
                if ($branchId) {
                    $bQ->where('branch_id', $branchId);
                }
                if (! $user->hasRole('admin')) {
                    $bQ->whereIn('branch_id', $userBranchIds);
                }
            })
            ->get();

        $byFolio = $allEntries
            ->groupBy(function ($entry) {
                return $entry->bitacora->folio_number ?? "Folio #{$entry->bitacora_id}";
            })
            ->map(function ($items, $folioNumber) {
                $datesBreakdown = $items->groupBy(function ($item) {
                    return $item->date ? (is_string($item->date) ? $item->date : $item->date->format('Y-m-d')) : ($item->bitacora->date ?? 'Sin fecha');
                })->map(function ($dateItems, $dateStr) {
                    $isSunday = $dateStr !== 'Sin fecha' ? Carbon::parse($dateStr)->isSunday() : false;
                    $bitacoraId = $dateItems->first()->bitacora_id;
                    $presentItems = $dateItems->where('is_absent', false);

                    return [
                        'date' => $dateStr,
                        'is_sunday' => $isSunday,
                        'bitacora_id' => $bitacoraId,
                        'employees_count' => $dateItems->pluck('employee_id')->unique()->count(),
                        'regular_hours' => round((float) $presentItems->sum('hours_worked'), 2),
                        'overtime_hours' => round((float) $presentItems->sum('overtime_hours'), 2),
                        'regular_pay' => round((float) $presentItems->sum(fn ($e) => $e->hours_worked * $e->base_rate_applied), 2),
                        'overtime_pay' => round((float) $presentItems->sum(fn ($e) => $e->overtime_hours * $e->overtime_rate_applied), 2),
                        'total_pay' => round((float) $presentItems->sum('total_earned'), 2),
                        'absences_count' => $dateItems->where('is_absent', true)->count(),
                    ];
                })->values()->sortBy('date')->values();

                $branchNames = $items->pluck('bitacora.branch.name')->filter()->unique()->values()->join(', ');
                $presentTotalItems = $items->where('is_absent', false);

                $totalRegularHours = $presentTotalItems->sum('hours_worked');
                $totalOvertimeHours = $presentTotalItems->sum('overtime_hours');
                $regularPay = $presentTotalItems->sum(fn ($e) => $e->hours_worked * $e->base_rate_applied);
                $overtimePay = $presentTotalItems->sum(fn ($e) => $e->overtime_hours * $e->overtime_rate_applied);
                $totalPay = $regularPay + $overtimePay;

                return [
                    'folio_number' => $folioNumber,
                    'branch_name' => $branchNames ?: 'N/A',
                    'days_count' => $datesBreakdown->count(),
                    'dates' => $datesBreakdown->toArray(),
                    'employees_count' => $items->pluck('employee_id')->unique()->count(),
                    'total_regular_hours' => round((float) $totalRegularHours, 2),
                    'total_overtime_hours' => round((float) $totalOvertimeHours, 2),
                    'regular_pay' => round((float) $regularPay, 2),
                    'overtime_pay' => round((float) $overtimePay, 2),
                    'total_pay' => round((float) $totalPay, 2),
                ];
            })
            ->sortByDesc('total_pay')
            ->values();

        $branches = $user->hasRole('admin')
            ? Branch::where('is_active', true)->get()
            : $user->branches;

        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('salaries/Index', [
            'payrollSummary' => $payrollSummary,
            'byFolio' => $byFolio,
            'branches' => $branches,
            'users' => $users,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'branch_id' => $branchId,
                'user_id' => $userId,
                'absence_filter' => $absenceFilter,
            ],
            'totals' => [
                'grand_regular_hours' => round($payrollSummary->sum('total_regular_hours'), 2),
                'grand_overtime_hours' => round($payrollSummary->sum('total_overtime_hours'), 2),
                'grand_regular_pay' => round($payrollSummary->sum('regular_pay'), 2),
                'grand_overtime_pay' => round($payrollSummary->sum('overtime_pay'), 2),
                'grand_total_pay' => round($payrollSummary->sum('total_pay'), 2),
                'grand_absences_count' => $payrollSummary->sum('absences_count'),
                'grand_employees_with_absences' => $payrollSummary->where('absences_count', '>', 0)->count(),
                'grand_partial_shifts_count' => $payrollSummary->sum('partial_shifts_count'),
            ],
        ]);
    }
}
