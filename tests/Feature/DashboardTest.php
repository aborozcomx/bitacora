<?php

use App\Models\Bitacora;
use App\Models\BitacoraEmployee;
use App\Models\BitacoraExpense;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Employee;
use App\Models\PaymentMethod;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin can view dashboard with global metrics, active folios, and calendars', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Centro', 'code' => 'SUC-CEN', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente ABC', 'code' => 'CLI-ABC', 'is_active' => true]);

    $bitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-DASH-01',
        'folio_prefix' => 'FOL',
        'folio_consecutive' => 'DASH-01',
        'date' => now()->startOfWeek()->format('Y-m-d'),
        'is_closed' => false,
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('kpis')
        ->where('kpis.active_folios_count', 1)
        ->has('activeFolios', 1)
        ->where('activeFolios.0.folio_number', 'FOL-DASH-01')
        ->has('expensesCalendar')
        ->has('personnelCalendar')
        ->where('isAdmin', true)
    );
});

test('encargado dashboard is scoped strictly to their own bitacoras, expenses, and personnel', function () {
    $user1 = User::factory()->create();
    $user1->assignRole('encargado');

    $user2 = User::factory()->create();
    $user2->assignRole('encargado');

    $branch = Branch::create(['name' => 'Sucursal Norte', 'code' => 'SUC-NOR', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente XYZ', 'code' => 'CLI-XYZ', 'is_active' => true]);
    $paymentMethod = PaymentMethod::create(['name' => 'Efectivo', 'slug' => 'efectivo', 'is_active' => true]);

    $emp1 = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Carlos',
        'last_name' => 'Pérez',
        'employee_code' => 'EMP-P1',
        'base_hourly_rate' => 100,
        'overtime_hourly_rate' => 150,
        'is_active' => true,
    ]);

    $emp2 = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Lucía',
        'last_name' => 'Mora',
        'employee_code' => 'EMP-P2',
        'base_hourly_rate' => 100,
        'overtime_hourly_rate' => 150,
        'is_active' => true,
    ]);

    $monday = now()->startOfWeek()->format('Y-m-d');

    // Bitácora belonging to user1
    $bUser1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $user1->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-USER1-10',
        'date' => $monday,
        'is_closed' => false,
    ]);

    BitacoraExpense::create([
        'bitacora_id' => $bUser1->id,
        'payment_method_id' => $paymentMethod->id,
        'concept' => 'Gasto de User1',
        'amount' => 500,
        'date' => $monday,
    ]);

    BitacoraEmployee::create([
        'bitacora_id' => $bUser1->id,
        'employee_id' => $emp1->id,
        'date' => $monday,
        'hours_worked' => 8,
        'overtime_hours' => 0,
        'base_rate_applied' => 100,
        'overtime_rate_applied' => 150,
        'total_earned' => 800,
        'is_absent' => false,
    ]);

    // Bitácora belonging to user2
    $bUser2 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $user2->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-USER2-20',
        'date' => $monday,
        'is_closed' => false,
    ]);

    BitacoraExpense::create([
        'bitacora_id' => $bUser2->id,
        'payment_method_id' => $paymentMethod->id,
        'concept' => 'Gasto de User2',
        'amount' => 1200,
        'date' => $monday,
    ]);

    BitacoraEmployee::create([
        'bitacora_id' => $bUser2->id,
        'employee_id' => $emp2->id,
        'date' => $monday,
        'hours_worked' => 8,
        'overtime_hours' => 2,
        'base_rate_applied' => 100,
        'overtime_rate_applied' => 150,
        'total_earned' => 1100,
        'is_absent' => false,
    ]);

    // Request dashboard as user1
    $response = $this->actingAs($user1)->get('/dashboard');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('isAdmin', false)
        ->where('kpis.active_folios_count', 1)
        ->where('kpis.total_period_expenses', 500)
        ->where('kpis.total_period_payroll', 800)
        ->where('kpis.total_period_cost', 1300)
        ->where('kpis.total_unique_workers', 1)
        ->has('activeFolios', 1)
        ->where('activeFolios.0.folio_number', 'FOL-USER1-10')
    );
});

test('dashboard custom date range filters data correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Rango', 'code' => 'SUC-RNG', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Rango', 'code' => 'CLI-RNG', 'is_active' => true]);
    $paymentMethod = PaymentMethod::create(['name' => 'Efectivo', 'slug' => 'efectivo', 'is_active' => true]);

    $bIn = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-IN',
        'date' => '2026-08-15',
        'is_closed' => false,
    ]);

    BitacoraExpense::create([
        'bitacora_id' => $bIn->id,
        'payment_method_id' => $paymentMethod->id,
        'concept' => 'Gasto En Rango',
        'amount' => 450,
        'date' => '2026-08-15',
    ]);

    $bOut = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-OUT',
        'date' => '2026-07-10',
        'is_closed' => false,
    ]);

    BitacoraExpense::create([
        'bitacora_id' => $bOut->id,
        'payment_method_id' => $paymentMethod->id,
        'concept' => 'Gasto Fuera De Rango',
        'amount' => 999,
        'date' => '2026-07-10',
    ]);

    $response = $this->actingAs($admin)->get('/dashboard?start_date=2026-08-01&end_date=2026-08-31');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('filters.start_date', '2026-08-01')
        ->where('filters.end_date', '2026-08-31')
        ->where('kpis.total_period_expenses', 450)
    );
});

test('active folios list on dashboard excludes closed folios', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Test', 'code' => 'SUC-TST', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Test', 'code' => 'CLI-TST', 'is_active' => true]);

    // Active
    Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-OPEN',
        'date' => '2026-08-10',
        'is_closed' => false,
    ]);

    // Closed
    Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-CLOSED',
        'date' => '2026-08-10',
        'is_closed' => true,
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('kpis.active_folios_count', 1)
        ->has('activeFolios', 1)
        ->where('activeFolios.0.folio_number', 'FOL-OPEN')
    );
});
