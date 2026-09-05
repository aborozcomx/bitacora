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
                    ];
                })->values()->toArray();

                $hasSunday = collect($bitacoras)->contains('is_sunday', true);

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
                    'has_sunday' => $hasSunday,
                    'absences_count' => $absencesCount,
                    'absences_dates' => $absencesDates,
                    'bitacoras' => $bitacoras,
                ];
            });

        if ($userId) {
            $payrollSummary = $payrollSummary->filter(fn ($item) => $item['bitacora_count'] > 0)->values();
        }

        if ($absenceFilter === 'with_absences') {
            $payrollSummary = $payrollSummary->filter(fn ($item) => $item['absences_count'] > 0)->values();
        } elseif ($absenceFilter === 'without_absences') {
            $payrollSummary = $payrollSummary->filter(fn ($item) => $item['absences_count'] === 0)->values();
        }

        $branches = $user->hasRole('admin')
            ? Branch::where('is_active', true)->get()
            : $user->branches;

        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('salaries/Index', [
            'payrollSummary' => $payrollSummary,
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
            ],
        ]);
    }
}
