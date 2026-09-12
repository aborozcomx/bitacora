<?php

use App\Models\ActivityType;
use App\Models\Bitacora;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\Employee;
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

test('admin can create bitacora header with client and client branch', function () {
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

test('encargado or any user can create bitacora directly', function () {
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

    $bitacora = Bitacora::where('folio_number', 'BIT-002')->first();
    expect($bitacora)->not->toBeNull();

    $response->assertRedirect("/bitacoras/{$bitacora->id}/edit");

    $this->assertDatabaseHas('bitacoras', [
        'folio_number' => 'BIT-002',
        'user_id' => $manager->id,
    ]);
});

test('regular user without special roles can access bitacora create and store bitacora', function () {
    $user = User::factory()->create();

    $branch = Branch::create(['name' => 'Sucursal Sur', 'code' => 'SUC-SUR', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Regular', 'code' => 'CLI-REG', 'is_active' => true]);

    $createPageResponse = $this->actingAs($user)->get('/bitacoras/create');
    $createPageResponse->assertStatus(200);

    $storeResponse = $this->actingAs($user)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $user->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '003',
        'date' => '2026-08-26',
    ]);

    $bitacora = Bitacora::where('folio_number', 'BIT-003')->first();
    expect($bitacora)->not->toBeNull();
    $storeResponse->assertRedirect("/bitacoras/{$bitacora->id}/edit");
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

test('individual employee normal hours cannot exceed 8 hrs on weekday or 5 hrs on saturday', function () {
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

    // 2026-08-15 is Saturday (max 5 hrs per employee): 7 hrs for Ana must fail validation
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

test('cannot create bitacora with a future date', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Futura', 'code' => 'SUC-FUT', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Futuro', 'code' => 'CLI-FUT', 'is_active' => true]);

    $futureDate = now()->addDays(2)->toDateString();

    $response = $this->actingAs($admin)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '999',
        'date' => $futureDate,
    ]);

    $response->assertSessionHasErrors(['date']);
    expect(Bitacora::where('folio_number', 'BIT-999')->exists())->toBeFalse();
});

test('cannot create bitacora reusing existing folio with a different client', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Test', 'code' => 'SUC-TST', 'is_active' => true]);
    $client1 = Client::create(['name' => 'Cliente Uno', 'code' => 'CLI-01', 'is_active' => true]);
    $client2 = Client::create(['name' => 'Cliente Dos', 'code' => 'CLI-02', 'is_active' => true]);

    // First bitacora with client 1 on 2026-08-20
    Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client1->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '100',
        'folio_number' => 'BIT-100',
        'date' => '2026-08-20',
    ]);

    // Attempt to reuse BIT-100 with client 2 on a different date (2026-08-21)
    $response = $this->actingAs($admin)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client2->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '100',
        'date' => '2026-08-21',
    ]);

    $response->assertSessionHasErrors(['client_id']);
    expect(Bitacora::where('folio_number', 'BIT-100')->where('date', '2026-08-21')->exists())->toBeFalse();
});

test('can create bitacora reusing existing folio with the same client on a different date', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Test', 'code' => 'SUC-TST', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Uno', 'code' => 'CLI-01', 'is_active' => true]);

    // First bitacora on 2026-08-20
    Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '101',
        'folio_number' => 'BIT-101',
        'date' => '2026-08-20',
    ]);

    // Reuse BIT-101 with same client on 2026-08-21
    $response = $this->actingAs($admin)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '101',
        'date' => '2026-08-21',
    ]);

    $secondBitacora = Bitacora::where('folio_number', 'BIT-101')->where('date', '2026-08-21')->first();
    expect($secondBitacora)->not->toBeNull();
    $response->assertRedirect("/bitacoras/{$secondBitacora->id}/edit");
});

test('cannot update bitacora with a future date or future activity date', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Test', 'code' => 'SUC-TST', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Test', 'code' => 'CLI-01', 'is_active' => true]);

    $bitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_number' => 'BIT-FUT-UPD',
        'date' => '2026-08-20',
    ]);

    $futureDate = now()->addDays(5)->toDateString();

    // 1. Future bitacora date
    $response = $this->actingAs($admin)->put("/bitacoras/{$bitacora->id}", [
        'date' => $futureDate,
        'activities' => [
            [
                'date' => '2026-08-20',
                'description' => 'Actividad normal',
            ],
        ],
    ]);
    $response->assertSessionHasErrors(['date']);

    // 2. Future activity date
    $response = $this->actingAs($admin)->put("/bitacoras/{$bitacora->id}", [
        'date' => '2026-08-20',
        'activities' => [
            [
                'date' => $futureDate,
                'description' => 'Actividad en el futuro',
            ],
        ],
    ]);
    $response->assertSessionHasErrors(['activities.0.date']);
});

test('cannot change client in bitacora update if folio is shared across sibling bitacoras', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Test', 'code' => 'SUC-TST', 'is_active' => true]);
    $client1 = Client::create(['name' => 'Cliente Original', 'code' => 'CLI-ORIG', 'is_active' => true]);
    $client2 = Client::create(['name' => 'Cliente Nuevo', 'code' => 'CLI-NEW', 'is_active' => true]);

    // Two sibling bitacoras with same folio BIT-SHARED
    $bitacora1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client1->id,
        'folio_number' => 'BIT-SHARED',
        'date' => '2026-08-20',
    ]);

    $bitacora2 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client1->id,
        'folio_number' => 'BIT-SHARED',
        'date' => '2026-08-21',
    ]);

    // Try to change client on bitacora1
    $response = $this->actingAs($admin)->put("/bitacoras/{$bitacora1->id}", [
        'client_id' => $client2->id,
        'date' => '2026-08-20',
        'activities' => [
            [
                'date' => '2026-08-20',
                'description' => 'Actividad de prueba',
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['client_id']);
    expect($bitacora1->fresh()->client_id)->toBe($client1->id);
});

test('cannot create bitacora reusing existing folio with a different client branch', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Test', 'code' => 'SUC-TST', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Con Sucursales', 'code' => 'CLI-SUC', 'is_active' => true]);
    $cb1 = ClientBranch::create(['client_id' => $client->id, 'name' => 'Planta Norte', 'code' => 'PN', 'is_active' => true]);
    $cb2 = ClientBranch::create(['client_id' => $client->id, 'name' => 'Planta Sur', 'code' => 'PS', 'is_active' => true]);

    Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'client_branch_id' => $cb1->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '200',
        'folio_number' => 'BIT-200',
        'date' => '2026-08-20',
    ]);

    // Attempt to reuse BIT-200 on another date (2026-08-21) with a different client branch ($cb2)
    $response = $this->actingAs($admin)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'client_branch_id' => $cb2->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '200',
        'date' => '2026-08-21',
    ]);

    $response->assertSessionHasErrors(['client_branch_id']);
    expect(Bitacora::where('folio_number', 'BIT-200')->where('date', '2026-08-21')->exists())->toBeFalse();
});

test('cannot change client branch in bitacora update if folio is shared across sibling bitacoras', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Test', 'code' => 'SUC-TST', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Con Sucursales', 'code' => 'CLI-SUC', 'is_active' => true]);
    $cb1 = ClientBranch::create(['client_id' => $client->id, 'name' => 'Planta Norte', 'code' => 'PN', 'is_active' => true]);
    $cb2 = ClientBranch::create(['client_id' => $client->id, 'name' => 'Planta Sur', 'code' => 'PS', 'is_active' => true]);

    $bitacora1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'client_branch_id' => $cb1->id,
        'folio_number' => 'BIT-BRANCH-SHARED',
        'date' => '2026-08-20',
    ]);

    $bitacora2 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'client_branch_id' => $cb1->id,
        'folio_number' => 'BIT-BRANCH-SHARED',
        'date' => '2026-08-21',
    ]);

    // Attempt to change client branch on bitacora1
    $response = $this->actingAs($admin)->put("/bitacoras/{$bitacora1->id}", [
        'client_id' => $client->id,
        'client_branch_id' => $cb2->id,
        'date' => '2026-08-20',
        'activities' => [
            [
                'date' => '2026-08-20',
                'description' => 'Actividad de prueba',
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['client_branch_id']);
    expect($bitacora1->fresh()->client_branch_id)->toBe($cb1->id);
});
