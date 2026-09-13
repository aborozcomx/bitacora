<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\BitacoraEmployee;
use App\Models\BitacoraExpense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $currentUser = $request->user();
        $isAdmin = $currentUser->hasRole('admin');

        // Scoping: If not admin, strictly scope to current user
        $filteredUserId = $isAdmin ? $request->query('user_id') : $currentUser->id;
        if ($filteredUserId) {
            $filteredUserId = (int) $filteredUserId;
        }

        // Custom Date Range (default: current week from Monday to Sunday)
        $today = Carbon::today();
        $defaultStart = $today->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $defaultEnd = $today->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        $startDate = $request->query('start_date', $defaultStart);
        $endDate = $request->query('end_date', $defaultEnd);

        // Sanitize dates
        try {
            $parsedStart = Carbon::parse($startDate)->startOfDay();
            $parsedEnd = Carbon::parse($endDate)->startOfDay();
            if ($parsedStart->gt($parsedEnd)) {
                $temp = $parsedStart;
                $parsedStart = $parsedEnd;
                $parsedEnd = $temp;
                $startDate = $parsedStart->format('Y-m-d');
                $endDate = $parsedEnd->format('Y-m-d');
            }
        } catch (\Exception $e) {
            $startDate = $defaultStart;
            $endDate = $defaultEnd;
            $parsedStart = Carbon::parse($startDate)->startOfDay();
            $parsedEnd = Carbon::parse($endDate)->startOfDay();
        }

        // 1. ACTIVE FOLIOS (Scoped to user, is_closed = false)
        $activeBitacorasQuery = Bitacora::with([
            'client:id,name,code',
            'clientBranch:id,name,code',
            'branch:id,name,code',
            'user:id,name,email',
            'activities',
            'expenses',
            'employees',
        ])
            ->where('is_closed', false);

        if (! $isAdmin || $filteredUserId) {
            $activeBitacorasQuery->where('user_id', $filteredUserId ?? $currentUser->id);
        }

        $activeBitacoras = $activeBitacorasQuery->orderBy('date', 'desc')->get();

        $activeFolios = $activeBitacoras
            ->groupBy('folio_number')
            ->map(function ($folioGroup, $folioNumber) {
                $first = $folioGroup->first();
                $datesList = $folioGroup->map(fn ($b) => is_string($b->date) ? substr($b->date, 0, 10) : $b->date->format('Y-m-d'))->unique()->values()->toArray();

                $payrollSum = (float) $folioGroup->sum(fn ($b) => $b->employees->sum('total_earned'));
                $expensesSum = (float) $folioGroup->sum(fn ($b) => $b->expenses->sum('amount'));

                return [
                    'id' => $first?->id,
                    'folio_number' => $folioNumber,
                    'folio_prefix' => $first?->folio_prefix,
                    'folio_consecutive' => $first?->folio_consecutive,
                    'client' => $first?->client,
                    'client_branch' => $first?->clientBranch,
                    'branch' => $first?->branch,
                    'user' => $first?->user,
                    'dates' => $datesList,
                    'dates_count' => count($datesList),
                    'total_payroll' => $payrollSum,
                    'total_expenses' => $expensesSum,
                    'total_cost' => $payrollSum + $expensesSum,
                    'activities_count' => $folioGroup->sum(fn ($b) => $b->activities->count()),
                ];
            })
            ->values();

        // 2. EXPENSES FOR THE PERIOD (Calendario de Gastos)
        $expensesQuery = BitacoraExpense::with([
            'bitacora.client',
            'bitacora.clientBranch',
            'bitacora.branch',
            'bitacora.user',
            'paymentMethod',
            'paymentCard',
            'activity',
        ])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNull('date')
                            ->whereHas('bitacora', fn ($bQ) => $bQ->whereBetween('date', [$startDate, $endDate]));
                    });
            })
            ->whereHas('bitacora', function ($bQ) use ($isAdmin, $filteredUserId, $currentUser) {
                if (! $isAdmin || $filteredUserId) {
                    $bQ->where('user_id', $filteredUserId ?? $currentUser->id);
                }
            });

        $expenses = $expensesQuery->get();

        // 3. PERSONNEL IN CHARGE FOR THE PERIOD (Calendario de Personal a Cargo)
        $employeesQuery = BitacoraEmployee::with([
            'employee.branch',
            'bitacora.client',
            'bitacora.clientBranch',
            'bitacora.branch',
            'bitacora.user',
            'activity.activityType',
        ])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNull('date')
                            ->whereHas('bitacora', fn ($bQ) => $bQ->whereBetween('date', [$startDate, $endDate]));
                    });
            })
            ->whereHas('bitacora', function ($bQ) use ($isAdmin, $filteredUserId, $currentUser) {
                if (! $isAdmin || $filteredUserId) {
                    $bQ->where('user_id', $filteredUserId ?? $currentUser->id);
                }
            });

        $assignedPersonnel = $employeesQuery->get();

        // Build Day-by-Day Calendar structures
        $dayNamesEs = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];

        $expensesByDate = $expenses->groupBy(function ($exp) {
            return $exp->date
                ? (is_string($exp->date) ? substr($exp->date, 0, 10) : $exp->date->format('Y-m-d'))
                : ($exp->bitacora?->date ? (is_string($exp->bitacora->date) ? substr($exp->bitacora->date, 0, 10) : $exp->bitacora->date->format('Y-m-d')) : null);
        });

        $personnelByDate = $assignedPersonnel->groupBy(function ($empEntry) {
            return $empEntry->date
                ? (is_string($empEntry->date) ? substr($empEntry->date, 0, 10) : $empEntry->date->format('Y-m-d'))
                : ($empEntry->bitacora?->date ? (is_string($empEntry->bitacora->date) ? substr($empEntry->bitacora->date, 0, 10) : $empEntry->bitacora->date->format('Y-m-d')) : null);
        });

        $expensesCalendar = [];
        $personnelCalendar = [];

        $cursor = $parsedStart->copy();
        while ($cursor->lte($parsedEnd)) {
            $dateStr = $cursor->format('Y-m-d');
            $dayOfWeek = $cursor->dayOfWeek;
            $dayName = $dayNamesEs[$dayOfWeek] ?? $cursor->format('l');

            // Daily Expenses
            $dayExpenses = $expensesByDate->get($dateStr, collect());
            $dailyExpenseTotal = (float) $dayExpenses->sum('amount');

            $expensesCalendar[] = [
                'date' => $dateStr,
                'day_name' => $dayName,
                'day_number' => $cursor->format('d'),
                'is_today' => $cursor->isToday(),
                'is_sunday' => $cursor->isSunday(),
                'total_amount' => $dailyExpenseTotal,
                'expenses_count' => $dayExpenses->count(),
                'expenses' => $dayExpenses->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'concept' => $e->concept,
                        'amount' => (float) $e->amount,
                        'folio_number' => $e->bitacora?->folio_number,
                        'bitacora_id' => $e->bitacora_id,
                        'client_name' => $e->bitacora?->client?->name,
                        'payment_method' => $e->paymentMethod?->name ?? 'Efectivo',
                        'payment_card' => $e->paymentCard?->bank_name,
                        'reference_number' => $e->reference_number,
                    ];
                })->values()->toArray(),
            ];

            // Daily Personnel
            $dayPersonnel = $personnelByDate->get($dateStr, collect());
            $presentWorkers = $dayPersonnel->where('is_absent', false);
            $dailyHours = (float) $presentWorkers->sum(fn ($w) => $w->hours_worked + $w->overtime_hours);

            $personnelCalendar[] = [
                'date' => $dateStr,
                'day_name' => $dayName,
                'day_number' => $cursor->format('d'),
                'is_today' => $cursor->isToday(),
                'is_sunday' => $cursor->isSunday(),
                'workers_count' => $dayPersonnel->pluck('employee_id')->unique()->count(),
                'total_hours' => round($dailyHours, 1),
                'workers' => $dayPersonnel->map(function ($w) {
                    return [
                        'id' => $w->id,
                        'employee_id' => $w->employee_id,
                        'employee_name' => $w->employee?->full_name ?? "Empleado #{$w->employee_id}",
                        'employee_code' => $w->employee?->employee_code,
                        'hours_worked' => (float) $w->hours_worked,
                        'overtime_hours' => (float) $w->overtime_hours,
                        'total_hours' => (float) ($w->hours_worked + $w->overtime_hours),
                        'is_absent' => (bool) $w->is_absent,
                        'is_partial_shift' => (bool) $w->is_partial_shift,
                        'partial_shift_reason' => $w->partial_shift_reason,
                        'folio_number' => $w->bitacora?->folio_number,
                        'bitacora_id' => $w->bitacora_id,
                        'activity_description' => $w->activity?->description,
                        'activity_type' => $w->activity?->activityType?->name,
                    ];
                })->values()->toArray(),
            ];

            $cursor->addDay();
        }

        // Summary KPIs for the Selected Period
        $periodExpensesSum = (float) $expenses->sum('amount');
        $periodPayrollSum = (float) $assignedPersonnel->where('is_absent', false)->sum('total_earned');
        $periodTotalHours = (float) $assignedPersonnel->where('is_absent', false)->sum(fn ($w) => $w->hours_worked + $w->overtime_hours);
        $periodUniqueWorkers = $assignedPersonnel->pluck('employee_id')->unique()->count();

        $kpis = [
            'active_folios_count' => $activeFolios->count(),
            'total_period_cost' => $periodExpensesSum + $periodPayrollSum,
            'total_period_expenses' => $periodExpensesSum,
            'total_period_payroll' => $periodPayrollSum,
            'total_period_hours' => round($periodTotalHours, 1),
            'total_unique_workers' => $periodUniqueWorkers,
        ];

        // List of managers for admin filter dropdown
        $availableManagers = $isAdmin
            ? User::role('encargado')->orderBy('name')->get(['id', 'name', 'email'])
            : collect();

        return Inertia::render('Dashboard', [
            'kpis' => $kpis,
            'activeFolios' => $activeFolios,
            'expensesCalendar' => $expensesCalendar,
            'personnelCalendar' => $personnelCalendar,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'user_id' => $filteredUserId ? (string) $filteredUserId : '',
            ],
            'isAdmin' => $isAdmin,
            'availableManagers' => $availableManagers,
            'currentUser' => [
                'id' => $currentUser->id,
                'name' => $currentUser->name,
                'email' => $currentUser->email,
            ],
        ]);
    }
}
