<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, [5, 10, 15, 25, 50, 100])) {
            $perPage = 10;
        }

        $employees = Employee::with('branch')
            ->when(! $user->hasRole('admin'), function ($query) use ($user) {
                $query->whereIn('branch_id', $user->branches->pluck('id'));
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhereHas('branch', function ($b) use ($search) {
                            $b->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $branches = $user->hasRole('admin')
            ? Branch::where('is_active', true)->get()
            : $user->branches;

        return Inertia::render('catalogs/Employees/Index', [
            'employees' => $employees,
            'branches' => $branches,
            'filters' => [
                'search' => $request->search,
                'branch_id' => $request->branch_id,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'base_hourly_rate' => 'required|numeric|min:0',
            'overtime_hourly_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        Employee::create($validated);

        return redirect()->back()->with('success', 'Empleado registrado exitosamente.');
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'employee_code' => 'required|string|max:50|unique:employees,employee_code,'.$employee->id,
            'base_hourly_rate' => 'required|numeric|min:0',
            'overtime_hourly_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $employee->update($validated);

        return redirect()->back()->with('success', 'Empleado actualizado exitosamente.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->back()->with('success', 'Empleado eliminado exitosamente.');
    }
}
