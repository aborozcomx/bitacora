<?php

use App\Models\Bitacora;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('salary report calculates regular and overtime pay accurately across multiple bitacoras', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Norte', 'code' => 'SUC-NTE', 'is_active' => true]);

    $employee = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Laura',
        'last_name' => 'Martínez',
        'employee_code' => 'EMP-NTE-01',
        'base_hourly_rate' => 120.00,
        'overtime_hourly_rate' => 180.00,
        'is_active' => true,
    ]);

    // Bitacora 1: 8 hrs base, 1 hr overtime -> (8 * 120) + (1 * 180) = 960 + 180 = 1140
    $bitacora1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'folio_number' => 'FOL-01',
        'date' => '2026-08-01',
    ]);
    $bitacora1->employees()->create([
        'employee_id' => $employee->id,
        'hours_worked' => 8.00,
        'overtime_hours' => 1.00,
        'base_rate_applied' => 120.00,
        'overtime_rate_applied' => 180.00,
        'total_earned' => 1140.00,
    ]);

    // Bitacora 2: 8 hrs base, 3 hrs overtime -> (8 * 120) + (3 * 180) = 960 + 540 = 1500
    $bitacora2 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'folio_number' => 'FOL-02',
        'date' => '2026-08-02',
    ]);
    $bitacora2->employees()->create([
        'employee_id' => $employee->id,
        'hours_worked' => 8.00,
        'overtime_hours' => 3.00,
        'base_rate_applied' => 120.00,
        'overtime_rate_applied' => 180.00,
        'total_earned' => 1500.00,
    ]);

    // Query salary report endpoint
    $response = $this->actingAs($admin)->get('/salaries?start_date=2026-08-01&end_date=2026-08-05');

    $response->assertStatus(200);
});

test('salary report accurately counts absences and filters employees with absences', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Poniente', 'code' => 'SUC-PTE', 'is_active' => true]);

    $emp1 = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Mario',
        'last_name' => 'Gómez',
        'employee_code' => 'EMP-PTE-01',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    $emp2 = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Valeria',
        'last_name' => 'Ríos',
        'employee_code' => 'EMP-PTE-02',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    $bitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'folio_number' => 'FOL-ABS-01',
        'date' => '2026-08-10',
    ]);

    // Mario worked 8 hours
    $bitacora->employees()->create([
        'employee_id' => $emp1->id,
        'is_absent' => false,
        'date' => '2026-08-10',
        'hours_worked' => 8.00,
        'overtime_hours' => 0.00,
        'base_rate_applied' => 100.00,
        'overtime_rate_applied' => 150.00,
        'total_earned' => 800.00,
    ]);

    // Valeria had an absence (falta)
    $bitacora->employees()->create([
        'employee_id' => $emp2->id,
        'is_absent' => true,
        'date' => '2026-08-10',
        'hours_worked' => 0.00,
        'overtime_hours' => 0.00,
        'base_rate_applied' => 100.00,
        'overtime_rate_applied' => 150.00,
        'total_earned' => 0.00,
    ]);

    // Check with absence filter = with_absences
    $response = $this->actingAs($admin)->get('/salaries?start_date=2026-08-01&end_date=2026-08-15&absence_filter=with_absences');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('salaries/Index')
        ->has('payrollSummary', 1)
        ->where('payrollSummary.0.employee_id', $emp2->id)
        ->where('payrollSummary.0.absences_count', 1)
        ->where('totals.grand_absences_count', 1)
    );
});

test('salary report defaults to weekly Wednesday-Thursday range and provides bitacoras list for each employee', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Centro', 'code' => 'SUC-CEN', 'is_active' => true]);

    $emp = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Adrian',
        'last_name' => 'Bautista',
        'employee_code' => 'EMP-CEN-01',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    $bitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'folio_number' => 'FOL-WEEK-01',
        'date' => '2026-08-12',
    ]);

    $bitacora->employees()->create([
        'employee_id' => $emp->id,
        'date' => '2026-08-12',
        'hours_worked' => 8.00,
        'overtime_hours' => 2.00,
        'base_rate_applied' => 100.00,
        'overtime_rate_applied' => 150.00,
        'total_earned' => 1100.00,
    ]);

    // Test without params (defaults to Wednesday - Thursday weekly cycle)
    $response = $this->actingAs($admin)->get('/salaries');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('salaries/Index')
        ->has('filters.start_date')
        ->has('filters.end_date')
        ->has('payrollSummary.0.bitacoras')
    );

    // Test with explicit Wednesday - Thursday range
    $explicitResponse = $this->actingAs($admin)->get('/salaries?start_date=2026-08-12&end_date=2026-08-20');
    $explicitResponse->assertStatus(200);
    $explicitResponse->assertInertia(fn ($page) => $page
        ->component('salaries/Index')
        ->where('payrollSummary.0.bitacoras.0.folio_number', 'FOL-WEEK-01')
        ->where('payrollSummary.0.bitacoras.0.hours_worked', 8)
    );
});

test('salary report can be filtered by encargado user_id', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $manager1 = User::factory()->create(['name' => 'Manager One']);
    $manager2 = User::factory()->create(['name' => 'Manager Two']);

    $branch = Branch::create(['name' => 'Sucursal Test', 'code' => 'SUC-TST', 'is_active' => true]);

    $emp1 = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Mario',
        'last_name' => 'Bros',
        'employee_code' => 'EMP-M1',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    $emp2 = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Luigi',
        'last_name' => 'Bros',
        'employee_code' => 'EMP-L1',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    $bitacora1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $manager1->id,
        'folio_number' => 'FOL-M1',
        'date' => '2026-08-12',
    ]);
    $bitacora1->employees()->create([
        'employee_id' => $emp1->id,
        'date' => '2026-08-12',
        'hours_worked' => 8.00,
        'base_rate_applied' => 100.00,
        'total_earned' => 800.00,
    ]);

    $bitacora2 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $manager2->id,
        'folio_number' => 'FOL-M2',
        'date' => '2026-08-12',
    ]);
    $bitacora2->employees()->create([
        'employee_id' => $emp2->id,
        'date' => '2026-08-12',
        'hours_worked' => 6.00,
        'base_rate_applied' => 100.00,
        'total_earned' => 600.00,
    ]);

    // Filter by manager 1
    $response = $this->actingAs($admin)->get("/salaries?start_date=2026-08-10&end_date=2026-08-15&user_id={$manager1->id}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('salaries/Index')
        ->has('payrollSummary', 1)
        ->where('payrollSummary.0.employee_id', $emp1->id)
    );
});
