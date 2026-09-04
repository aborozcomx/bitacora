<?php

namespace App\Http\Controllers;

use App\Models\ActivityType;
use App\Models\Bitacora;
use App\Models\Branch;
use App\Models\CardType;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Folio;
use App\Models\PaymentCard;
use App\Models\PaymentMethod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BitacoraController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $bitacoras = Bitacora::with([
            'branch',
            'user',
            'client',
            'clientBranch',
            'activities.activityType',
            'activities.employees.employee',
            'activities.expenses.paymentMethod',
            'activities.expenses.paymentCard',
        ])
            ->when(! $user->hasRole('admin'), function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->whereIn('branch_id', $user->branches->pluck('id'))
                        ->orWhere('user_id', $user->id);
                });
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('folio_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('clientBranch', fn ($cbq) => $cbq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->client_id, function ($query, $clientId) {
                $query->where('client_id', $clientId);
            })
            ->when($request->start_date, function ($query, $startDate) {
                $query->whereDate('date', '>=', $startDate);
            })
            ->when($request->end_date, function ($query, $endDate) {
                $query->whereDate('date', '<=', $endDate);
            })
            ->latest('date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $branches = $user->hasRole('admin')
            ? Branch::where('is_active', true)->get()
            : $user->branches;

        $clients = Client::where('is_active', true)->get(['id', 'name', 'code']);

        return Inertia::render('bitacoras/Index', [
            'bitacoras' => $bitacoras,
            'branches' => $branches,
            'clients' => $clients,
            'filters' => $request->only(['search', 'branch_id', 'client_id', 'start_date', 'end_date']),
            'canCreate' => $user->hasRole('admin'),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Bitacora::class);

        $branches = Branch::where('is_active', true)->get();
        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $clients = Client::where('is_active', true)
            ->with(['branches' => fn ($q) => $q->where('is_active', true)])
            ->get();

        $folios = Folio::active()->orderBy('name')->get(['id', 'name', 'current_consecutive', 'description']);
        $defaultFolio = $folios->firstWhere('name', 'BIT') ?? $folios->first();
        $suggestedPrefix = $defaultFolio ? $defaultFolio->name : 'BIT';
        $suggestedConsecutive = (string) (($defaultFolio ? $defaultFolio->current_consecutive : 0) + 1);

        return Inertia::render('bitacoras/Create', [
            'branches' => $branches,
            'users' => $users,
            'clients' => $clients,
            'folios' => $folios,
            'suggestedPrefix' => $suggestedPrefix,
            'suggestedConsecutive' => $suggestedConsecutive,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Bitacora::class);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:clients,id',
            'client_branch_id' => 'nullable|exists:client_branches,id',
            'folio_prefix' => 'required|string|max:20',
            'folio_consecutive' => 'required|string|max:50',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $prefix = trim($validated['folio_prefix']);
        $consecutive = trim($validated['folio_consecutive']);
        $folioNumber = "{$prefix}-{$consecutive}";

        $bitacora = Bitacora::create([
            'branch_id' => $validated['branch_id'],
            'user_id' => $validated['user_id'],
            'client_id' => $validated['client_id'],
            'client_branch_id' => $validated['client_branch_id'] ?: null,
            'folio_prefix' => $prefix,
            'folio_consecutive' => $consecutive,
            'folio_number' => $folioNumber,
            'date' => $validated['date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Synchronize numeric consecutive in Folio catalog if prefix exists
        if (is_numeric($consecutive)) {
            $consecutiveInt = (int) $consecutive;
            $folio = Folio::where('name', $prefix)->first();
            if ($folio) {
                $newConsecutive = max($folio->current_consecutive + 1, $consecutiveInt);
                $folio->update(['current_consecutive' => $newConsecutive]);
            }
        }

        return redirect()->route('bitacoras.edit', $bitacora->id)->with('success', 'Bitácora creada exitosamente. Ahora puedes capturar las actividades, empleados y gastos.');
    }

    public function show(Bitacora $bitacora): Response
    {
        Gate::authorize('view', $bitacora);

        $bitacora->load([
            'branch',
            'user',
            'client',
            'clientBranch',
            'activities.activityType',
            'activities.employees.employee',
            'activities.expenses.paymentMethod',
            'activities.expenses.cardType',
            'activities.expenses.paymentCard',
        ]);

        return Inertia::render('bitacoras/Show', [
            'bitacora' => $bitacora,
        ]);
    }

    public function edit(Request $request, Bitacora $bitacora): Response
    {
        Gate::authorize('update', $bitacora);

        $user = $request->user();

        $bitacora->load([
            'branch',
            'user',
            'client',
            'clientBranch',
            'activities.activityType',
            'activities.employees.employee',
            'activities.expenses.paymentMethod',
            'activities.expenses.paymentCard',
        ]);

        $branches = $user->hasRole('admin')
            ? Branch::where('is_active', true)->get()
            : $user->branches()->where('is_active', true)->get();

        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $clients = Client::where('is_active', true)
            ->with(['branches' => fn ($q) => $q->where('is_active', true)])
            ->get();

        $activityTypes = ActivityType::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->with('branch')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $cardTypes = CardType::where('is_active', true)->get();
        $paymentCards = PaymentCard::where('is_active', true)->with(['paymentMethod', 'cardType'])->get();

        return Inertia::render('bitacoras/Edit', [
            'bitacora' => $bitacora,
            'branches' => $branches,
            'users' => $users,
            'clients' => $clients,
            'activityTypes' => $activityTypes,
            'employees' => $employees,
            'paymentMethods' => $paymentMethods,
            'cardTypes' => $cardTypes,
            'paymentCards' => $paymentCards,
            'isAdmin' => $user->hasRole('admin'),
        ]);
    }

    public function update(Request $request, Bitacora $bitacora): RedirectResponse
    {
        Gate::authorize('update', $bitacora);

        $validated = $this->validateBitacoraHierarchyData($request, $bitacora);

        DB::transaction(function () use ($bitacora, $validated) {
            // Update header data if provided
            $bitacora->update([
                'branch_id' => $validated['branch_id'] ?? $bitacora->branch_id,
                'user_id' => $validated['user_id'] ?? $bitacora->user_id,
                'client_id' => $validated['client_id'] ?? $bitacora->client_id,
                'client_branch_id' => array_key_exists('client_branch_id', $validated) ? ($validated['client_branch_id'] ?: null) : $bitacora->client_branch_id,
                'folio_prefix' => $validated['folio_prefix'] ?? $bitacora->folio_prefix,
                'folio_consecutive' => $validated['folio_consecutive'] ?? $bitacora->folio_consecutive,
                'folio_number' => $validated['folio_number'] ?? $bitacora->folio_number,
                'date' => $validated['date'] ?? $bitacora->date,
                'notes' => $validated['notes'] ?? $bitacora->notes,
            ]);

            // Sync activities and their children
            $bitacora->activities()->delete();

            foreach ($validated['activities'] as $actData) {
                $activity = $bitacora->activities()->create([
                    'activity_type_id' => $actData['activity_type_id'] ?? null,
                    'date' => $actData['date'],
                    'description' => $actData['description'],
                ]);

                // Create employees for this activity
                if (! empty($actData['employees'])) {
                    foreach ($actData['employees'] as $empData) {
                        $employee = Employee::findOrFail($empData['employee_id']);
                        $isAbsent = (bool) ($empData['is_absent'] ?? false);
                        $hoursWorked = $isAbsent ? 0.0 : (float) $empData['hours_worked'];
                        $overtimeHours = $isAbsent ? 0.0 : (float) $empData['overtime_hours'];
                        $baseRate = (float) $employee->base_hourly_rate;
                        $overtimeRate = (float) $employee->overtime_hourly_rate;

                        $totalEarned = ($hoursWorked * $baseRate) + ($overtimeHours * $overtimeRate);

                        $bitacora->employees()->create([
                            'bitacora_activity_id' => $activity->id,
                            'employee_id' => $employee->id,
                            'is_absent' => $isAbsent,
                            'date' => $actData['date'],
                            'hours_worked' => $hoursWorked,
                            'overtime_hours' => $overtimeHours,
                            'base_rate_applied' => $baseRate,
                            'overtime_rate_applied' => $overtimeRate,
                            'total_earned' => $totalEarned,
                        ]);
                    }
                }

                // Create expenses for this activity
                if (! empty($actData['expenses'])) {
                    foreach ($actData['expenses'] as $expData) {
                        $bitacora->expenses()->create([
                            'bitacora_activity_id' => $activity->id,
                            'concept' => $expData['concept'],
                            'amount' => $expData['amount'],
                            'date' => $actData['date'],
                            'payment_method_id' => $expData['payment_method_id'],
                            'card_type_id' => $expData['card_type_id'] ?? null,
                            'payment_card_id' => $expData['payment_card_id'] ?? null,
                            'reference_number' => $expData['reference_number'] ?? null,
                            'notes' => $expData['notes'] ?? null,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('bitacoras.show', $bitacora->id)->with('success', 'Bitácora y actividades actualizadas exitosamente.');
    }

    /**
     * Validate nested activity data and hours per employee per date.
     *
     * @return array<string, mixed>
     */
    private function validateBitacoraHierarchyData(Request $request, Bitacora $bitacora): array
    {
        $validated = $request->validate([
            'branch_id' => 'sometimes|required|exists:branches,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'client_id' => 'sometimes|required|exists:clients,id',
            'client_branch_id' => 'nullable|exists:client_branches,id',
            'folio_prefix' => 'nullable|string|max:20',
            'folio_consecutive' => 'nullable|string|max:50',
            'folio_number' => 'nullable|string|max:100',
            'date' => 'nullable|date',
            'notes' => 'nullable|string',

            // Activities
            'activities' => 'required|array|min:1',
            'activities.*.id' => 'nullable|integer',
            'activities.*.date' => 'required|date',
            'activities.*.activity_type_id' => 'nullable|exists:activity_types,id',
            'activities.*.description' => 'required|string',

            // Nested Employees in each Activity
            'activities.*.employees' => 'nullable|array',
            'activities.*.employees.*.employee_id' => 'required|exists:employees,id',
            'activities.*.employees.*.is_absent' => 'nullable|boolean',
            'activities.*.employees.*.hours_worked' => 'required|numeric|min:0',
            'activities.*.employees.*.overtime_hours' => 'required|numeric|min:0',

            // Nested Expenses in each Activity
            'activities.*.expenses' => 'nullable|array',
            'activities.*.expenses.*.concept' => 'required|string|max:255',
            'activities.*.expenses.*.amount' => 'required|numeric|min:0.01',
            'activities.*.expenses.*.payment_method_id' => 'required|exists:payment_methods,id',
            'activities.*.expenses.*.card_type_id' => 'nullable|exists:card_types,id',
            'activities.*.expenses.*.payment_card_id' => 'nullable|exists:payment_cards,id',
            'activities.*.expenses.*.reference_number' => 'nullable|string|max:255',
            'activities.*.expenses.*.notes' => 'nullable|string',
        ]);

        // If prefix and consecutive provided, generate folio_number
        if (! empty($validated['folio_prefix']) && ! empty($validated['folio_consecutive'])) {
            $validated['folio_number'] = trim($validated['folio_prefix']).'-'.trim($validated['folio_consecutive']);
        }

        // VALIDATION 4: Normal hours limit is PER EMPLOYEE PER DATE:
        // <= 8 hrs on weekdays/Sunday, <= 6 hrs on Saturdays
        $hoursByEmpAndDate = [];

        foreach ($validated['activities'] as $actIdx => $act) {
            $actDate = $act['date'];

            if (! empty($act['employees'])) {
                foreach ($act['employees'] as $empIdx => $empData) {
                    if (! empty($empData['is_absent'])) {
                        continue;
                    }
                    $empId = $empData['employee_id'];
                    $key = "{$empId}_{$actDate}";

                    if (! isset($hoursByEmpAndDate[$key])) {
                        $hoursByEmpAndDate[$key] = [
                            'employee_id' => $empId,
                            'date' => $actDate,
                            'hours' => 0.0,
                        ];
                    }
                    $hoursByEmpAndDate[$key]['hours'] += (float) $empData['hours_worked'];
                }
            }

            // Validate expenses payment card requirement
            if (! empty($act['expenses'])) {
                foreach ($act['expenses'] as $expIdx => $expData) {
                    $paymentMethod = PaymentMethod::find($expData['payment_method_id']);
                    if ($paymentMethod && $paymentMethod->requires_card_details && empty($expData['payment_card_id'])) {
                        throw ValidationException::withMessages([
                            "activities.{$actIdx}.expenses.{$expIdx}.payment_card_id" => "La cuenta o tarjeta es obligatoria para el método '{$paymentMethod->name}'.",
                        ]);
                    }
                }
            }
        }

        foreach ($hoursByEmpAndDate as $data) {
            $carbonDate = Carbon::parse($data['date']);
            $maxHours = $carbonDate->isSaturday() ? 6.0 : 8.0;
            $dayLabel = $carbonDate->isSaturday() ? 'sábado' : 'entre semana';

            if ($data['hours'] > $maxHours) {
                $employee = Employee::find($data['employee_id']);
                $empName = $employee ? "{$employee->first_name} {$employee->last_name}" : "ID {$data['employee_id']}";

                throw ValidationException::withMessages([
                    'activities' => "El empleado {$empName} no puede tener más de {$maxHours} horas normales el día {$data['date']} ({$dayLabel}). Actual asignado: {$data['hours']} hrs.",
                ]);
            }
        }

        return $validated;
    }

    public function destroy(Bitacora $bitacora): RedirectResponse
    {
        Gate::authorize('delete', $bitacora);

        $bitacora->delete();

        return redirect()->route('bitacoras.index')->with('success', 'Bitácora eliminada exitosamente.');
    }
}

