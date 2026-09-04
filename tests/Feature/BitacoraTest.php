<?php

use App\Models\ActivityType;
use App\Models\Bitacora;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\Employee;
use App\Models\PaymentCard;
use App\Models\PaymentMethod;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin can view bitacoras index', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/bitacoras');

    $response->assertStatus(200);
});

test('only admin can create bitacora header with client and client branch', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $manager = User::factory()->create();
    $manager->assignRole('encargado');

    $branch = Branch::create(['name' => 'Sucursal Centro', 'code' => 'SUC-CEN', 'is_active' => true]);

    $client = Client::create([
        'name' => 'Industrias Monterrey',
        'code' => 'IM-01',
        'is_active' => true,
    ]);

    $clientBranch = ClientBranch::create([
        'client_id' => $client->id,
        'name' => 'Planta Oriente',
        'code' => 'PO-01',
        'is_active' => true,
    ]);

    // Admin creates
    $response = $this->actingAs($admin)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $manager->id,
        'client_id' => $client->id,
        'client_branch_id' => $clientBranch->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '001',
        'date' => '2026-08-26',
        'notes' => 'Bitácora creada por administrador',
    ]);

    $bitacora = Bitacora::where('folio_number', 'BIT-001')->first();
    expect($bitacora)->not->toBeNull();

    $response->assertRedirect("/bitacoras/{$bitacora->id}/edit");

    $this->assertDatabaseHas('bitacoras', [
        'folio_number' => 'BIT-001',
        'client_id' => $client->id,
        'client_branch_id' => $clientBranch->id,
        'user_id' => $manager->id,
    ]);
});

test('encargado cannot create bitacora directly', function () {
    $manager = User::factory()->create();
    $manager->assignRole('encargado');

    $branch = Branch::create(['name' => 'Sucursal Norte', 'code' => 'SUC-NOR', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Test', 'code' => 'CLI-01', 'is_active' => true]);

    $response = $this->actingAs($manager)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $manager->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '002',
        'date' => '2026-08-26',
    ]);

    $response->assertForbidden();
});

test('encargado can update bitacora of their branch to add activities, employees and expenses', function () {
    $branch = Branch::create(['name' => 'Sucursal Centro', 'code' => 'SUC-CEN', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Test', 'code' => 'CLI-01', 'is_active' => true]);

    $manager = User::factory()->create();
    $manager->assignRole('encargado');
    $manager->branches()->attach($branch);

    $employee = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Pedro',
        'last_name' => 'Ramírez',
        'employee_code' => 'EMP-01',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    $paymentMethodCash = PaymentMethod::create([
        'name' => 'Efectivo',
        'slug' => 'efectivo',
        'requires_card_details' => false,
        'is_active' => true,
    ]);

    $activityType = ActivityType::create(['name' => 'Mantenimiento', 'is_active' => true]);

    $bitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $manager->id,
        'client_id' => $client->id,
        'folio_number' => 'BIT-003',
        'date' => '2026-08-26',
    ]);

    // 2026-08-26 is Wednesday
    $response = $this->actingAs($manager)->put("/bitacoras/{$bitacora->id}", [
        'notes' => 'Detalle de actividades actualizado',
        'activities' => [
            [
                'date' => '2026-08-26',
                'activity_type_id' => $activityType->id,
                'description' => 'Mantenimiento de transformadores',
                'employees' => [
                    [
                        'employee_id' => $employee->id,
                        'is_absent' => false,
                        'hours_worked' => 8,
                        'overtime_hours' => 2,
                    ],
                ],
                'expenses' => [
                    [
                        'concept' => 'Compra de fusibles',
                        'amount' => 350.00,
                        'payment_method_id' => $paymentMethodCash->id,
                        'payment_card_id' => null,
                        'reference_number' => 'TICK-101',
                    ],
                ],
            ],
        ],
    ]);

    $response->assertRedirect("/bitacoras/{$bitacora->id}");

    $this->assertDatabaseHas('bitacora_activities', [
        'bitacora_id' => $bitacora->id,
        'date' => '2026-08-26',
        'description' => 'Mantenimiento de transformadores',
    ]);

    $this->assertDatabaseHas('bitacora_employees', [
        'bitacora_id' => $bitacora->id,
        'employee_id' => $employee->id,
        'date' => '2026-08-26',
        'hours_worked' => 8.00,
        'overtime_hours' => 2.00,
        'total_earned' => 1100.00,
    ]);

    $this->assertDatabaseHas('bitacora_expenses', [
        'bitacora_id' => $bitacora->id,
        'concept' => 'Compra de fusibles',
        'amount' => 350.00,
        'date' => '2026-08-26',
    ]);
});

test('individual employee normal hours cannot exceed 8 hrs on weekday or 6 hrs on saturday', function () {
    $branch = Branch::create(['name' => 'Sucursal Test', 'code' => 'SUC-TST', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Test', 'code' => 'CLI-01', 'is_active' => true]);

    $manager = User::factory()->create();
    $manager->assignRole('encargado');
    $manager->branches()->attach($branch);

    $emp1 = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Ana',
        'last_name' => 'Martínez',
        'employee_code' => 'EMP-02',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    $bitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $manager->id,
        'client_id' => $client->id,
        'folio_number' => 'BIT-SAT-ERR',
        'date' => '2026-08-15',
    ]);

    // 2026-08-15 is Saturday (max 6 hrs per employee): 7 hrs for Ana must fail validation
    $response = $this->actingAs($manager)->put("/bitacoras/{$bitacora->id}", [
        'activities' => [
            [
                'date' => '2026-08-15',
                'description' => 'Actividad 1',
                'employees' => [
                    [
                        'employee_id' => $emp1->id,
                        'hours_worked' => 4,
                        'overtime_hours' => 0,
                    ],
                ],
            ],
            [
                'date' => '2026-08-15',
                'description' => 'Actividad 2',
                'employees' => [
                    [
                        'employee_id' => $emp1->id,
                        'hours_worked' => 3,
                        'overtime_hours' => 0,
                    ],
                ],
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['activities']);
});

test('multiple distinct employees with 8 hours each on the same date succeed', function () {
    $branch = Branch::create(['name' => 'Sucursal Test', 'code' => 'SUC-TST', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Test', 'code' => 'CLI-01', 'is_active' => true]);

    $manager = User::factory()->create();
    $manager->assignRole('encargado');
    $manager->branches()->attach($branch);

    $emp1 = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Juan',
        'last_name' => 'Pérez',
        'employee_code' => 'EMP-03',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    $emp2 = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Roberto',
        'last_name' => 'López',
        'employee_code' => 'EMP-04',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    $bitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $manager->id,
        'client_id' => $client->id,
        'folio_number' => 'BIT-MULTI-OK',
        'date' => '2026-08-17',
    ]);

    // Weekday: Emp1 has 8h, Emp2 has 8h -> Validation is PER EMPLOYEE, so this MUST pass!
    $response = $this->actingAs($manager)->put("/bitacoras/{$bitacora->id}", [
        'activities' => [
            [
                'date' => '2026-08-17',
                'description' => 'Mantenimiento General',
                'employees' => [
                    [
                        'employee_id' => $emp1->id,
                        'hours_worked' => 8,
                        'overtime_hours' => 0,
                    ],
                    [
                        'employee_id' => $emp2->id,
                        'hours_worked' => 8,
                        'overtime_hours' => 0,
                    ],
                ],
            ],
        ],
    ]);

    $response->assertRedirect("/bitacoras/{$bitacora->id}");
});

