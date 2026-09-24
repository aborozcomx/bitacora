<?php

use App\Models\ActivityType;
use App\Models\Bitacora;
use App\Models\BitacoraEmployee;
use App\Models\BitacoraExpense;
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

test('closing a bitacora marks all bitacoras sharing the same folio as closed and prevents modifications', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Closure', 'code' => 'SUC-CLS', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Closure', 'code' => 'CC-01', 'is_active' => true]);

    $b1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-CLOSE-01',
        'folio_prefix' => 'FOL',
        'folio_consecutive' => 'CLOSE-01',
        'date' => '2026-08-10',
    ]);

    $b2 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-CLOSE-01',
        'folio_prefix' => 'FOL',
        'folio_consecutive' => 'CLOSE-01',
        'date' => '2026-08-11',
    ]);

    // Close bitacora 1
    $response = $this->actingAs($admin)->post("/bitacoras/{$b1->id}/close");
    $response->assertSessionHas('success');

    // Both b1 and b2 should be closed
    expect($b1->fresh()->is_closed)->toBeTrue()
        ->and($b1->fresh()->closed_by)->toBe($admin->id)
        ->and($b1->fresh()->closed_at)->not->toBeNull()
        ->and($b2->fresh()->is_closed)->toBeTrue();

    // Updating a closed bitacora is forbidden (403)
    $updateResponse = $this->actingAs($admin)->put("/bitacoras/{$b1->id}", [
        'date' => '2026-08-10',
        'activities' => [
            ['date' => '2026-08-10', 'description' => 'Test edit'],
        ],
    ]);
    $updateResponse->assertStatus(403);

    // Deleting a closed bitacora is forbidden (403)
    $deleteResponse = $this->actingAs($admin)->delete("/bitacoras/{$b1->id}");
    $deleteResponse->assertStatus(403);
    expect(Bitacora::find($b1->id))->not->toBeNull();

    // Reusing the closed folio to create a new bitacora is blocked
    $createResponse = $this->actingAs($admin)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_prefix' => 'FOL',
        'folio_consecutive' => 'CLOSE-01',
        'date' => '2026-08-12',
    ]);
    $createResponse->assertSessionHasErrors(['folio_consecutive']);
});

test('bitacoras index only displays active non-closed bitacoras and user scoping applies for non-admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user1 = User::factory()->create();
    $user1->assignRole('encargado');

    $user2 = User::factory()->create();
    $user2->assignRole('encargado');

    $branch = Branch::create(['name' => 'Sucursal Scoping', 'code' => 'SUC-SCP', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Scoping', 'code' => 'CS-01', 'is_active' => true]);

    // Active bitacora for user1
    $bActiveUser1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $user1->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-ACTIVE-U1',
        'date' => '2026-08-15',
        'is_closed' => false,
    ]);

    // Closed bitacora for user1
    $bClosedUser1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $user1->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-CLOSED-U1',
        'date' => '2026-08-14',
        'is_closed' => true,
    ]);

    // Active bitacora for user2
    $bActiveUser2 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $user2->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-ACTIVE-U2',
        'date' => '2026-08-15',
        'is_closed' => false,
    ]);

    // As user1 (encargado): should only see $bActiveUser1, isAdmin false, and kpis null
    $response = $this->actingAs($user1)->get('/bitacoras');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('bitacoras/Index')
        ->has('bitacoras.data', 1)
        ->where('bitacoras.data.0.folio_number', 'FOL-ACTIVE-U1')
        ->where('isAdmin', false)
        ->where('kpis', null)
    );

    // As admin: should see both active bitacoras ($bActiveUser1 and $bActiveUser2), isAdmin true, and kpis present
    $adminResponse = $this->actingAs($admin)->get('/bitacoras');
    $adminResponse->assertStatus(200);
    $adminResponse->assertInertia(fn ($page) => $page
        ->component('bitacoras/Index')
        ->has('bitacoras.data', 2)
        ->where('isAdmin', true)
        ->has('kpis')
        ->where('kpis.total_folios', 2)
    );

    // Finalized view as user1 (encargado): should see $bClosedUser1, isAdmin false, and kpis null
    $finalizedResponse = $this->actingAs($user1)->get('/bitacoras-finalizadas');
    $finalizedResponse->assertStatus(200);
    $finalizedResponse->assertInertia(fn ($page) => $page
        ->component('bitacoras/Finalized')
        ->has('bitacoras.data', 1)
        ->where('bitacoras.data.0.folio_number', 'FOL-CLOSED-U1')
        ->where('isAdmin', false)
        ->where('kpis', null)
    );

    // Finalized view as admin: should see $bClosedUser1, isAdmin true, and kpis present
    $adminFinalizedResponse = $this->actingAs($admin)->get('/bitacoras-finalizadas');
    $adminFinalizedResponse->assertStatus(200);
    $adminFinalizedResponse->assertInertia(fn ($page) => $page
        ->component('bitacoras/Finalized')
        ->has('bitacoras.data', 1)
        ->where('isAdmin', true)
        ->has('kpis')
        ->where('kpis.total_folios', 1)
    );
});

test('bitacoras index groups entries by folio and computes accurate financial summations', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Agrupada', 'code' => 'SUC-AGR', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Agrupado', 'code' => 'CLI-AGR', 'is_active' => true]);
    $paymentMethod = PaymentMethod::firstOrCreate(
        ['slug' => 'efectivo'],
        ['name' => 'Efectivo', 'requires_card_details' => false, 'is_active' => true]
    );

    $emp = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Roberto',
        'last_name' => 'Gómez',
        'employee_code' => 'EMP-AGR-01',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    // Create 2 bitácoras for Day 1 and Day 2 under the SAME folio
    $bDay1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-SUM-TEST-100',
        'date' => '2026-08-10',
        'is_closed' => false,
    ]);

    $bDay2 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-SUM-TEST-100',
        'date' => '2026-08-11',
        'is_closed' => false,
    ]);

    // Add employee earning to Day 1 ($800) and Day 2 ($600) -> total payroll $1,400
    BitacoraEmployee::create([
        'bitacora_id' => $bDay1->id,
        'employee_id' => $emp->id,
        'date' => '2026-08-10',
        'hours_worked' => 8,
        'overtime_hours' => 0,
        'hourly_rate' => 100,
        'overtime_rate' => 150,
        'total_earned' => 800.00,
    ]);

    BitacoraEmployee::create([
        'bitacora_id' => $bDay2->id,
        'employee_id' => $emp->id,
        'date' => '2026-08-11',
        'hours_worked' => 6,
        'overtime_hours' => 0,
        'hourly_rate' => 100,
        'overtime_rate' => 150,
        'total_earned' => 600.00,
    ]);

    // Add expenses to Day 1 ($250) and Day 2 ($350) -> total expenses $600
    BitacoraExpense::create([
        'bitacora_id' => $bDay1->id,
        'payment_method_id' => $paymentMethod->id,
        'concept' => 'Materiales A',
        'amount' => 250.00,
        'date' => '2026-08-10',
    ]);

    BitacoraExpense::create([
        'bitacora_id' => $bDay2->id,
        'payment_method_id' => $paymentMethod->id,
        'concept' => 'Materiales B',
        'amount' => 350.00,
        'date' => '2026-08-11',
    ]);

    $response = $this->actingAs($admin)->get('/bitacoras');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('bitacoras/Index')
        ->has('bitacoras.data', 1)
        ->where('bitacoras.data.0.folio_number', 'FOL-SUM-TEST-100')
        ->where('bitacoras.data.0.dates_count', 2)
        ->where('bitacoras.data.0.total_payroll', 1400)
        ->where('bitacoras.data.0.total_expenses', 600)
        ->where('bitacoras.data.0.total_cost', 2000)
        ->has('bitacoras.data.0.bitacoras', 2)
    );
});

test('create page with from_folio returns inherited folio data with locked fields context', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Herencia', 'code' => 'SUC-HER', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Herencia', 'code' => 'CLI-HER', 'is_active' => true]);
    $clientBranch = ClientBranch::create(['client_id' => $client->id, 'name' => 'Planta Norte', 'code' => 'PN-01', 'is_active' => true]);

    $bitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'client_branch_id' => $clientBranch->id,
        'folio_prefix' => 'ICC',
        'folio_consecutive' => '55',
        'folio_number' => 'ICC-55',
        'date' => '2026-08-10',
        'notes' => 'Observaciones iniciales del servicio',
        'is_closed' => false,
    ]);

    $response = $this->actingAs($admin)->get('/bitacoras/create?from_folio=ICC-55');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('bitacoras/Create')
        ->where('inheritedFolio.folio_number', 'ICC-55')
        ->where('inheritedFolio.folio_prefix', 'ICC')
        ->where('inheritedFolio.folio_consecutive', '55')
        ->where('inheritedFolio.client_id', $client->id)
        ->where('inheritedFolio.client_branch_id', $clientBranch->id)
        ->where('inheritedFolio.branch_id', $branch->id)
        ->where('inheritedFolio.user_id', $admin->id)
        ->where('inheritedFolio.existing_dates', ['2026-08-10'])
        ->where('inheritedFolio.notes', 'Observaciones iniciales del servicio')
    );
});

test('create and edit views expose isAdmin flag according to user role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $encargado = User::factory()->create();
    $encargado->assignRole('encargado');

    $branch = Branch::create(['name' => 'Sucursal Sur', 'code' => 'SUR', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Gamma', 'code' => 'CLI-G', 'is_active' => true]);
    $encargado->branches()->attach($branch);

    $bitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $encargado->id,
        'client_id' => $client->id,
        'folio_number' => 'BIT-999',
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '999',
        'date' => '2026-08-20',
        'is_closed' => false,
    ]);

    // Admin in create
    $this->actingAs($admin)->get('/bitacoras/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('bitacoras/Create')
            ->where('isAdmin', true)
        );

    // Encargado in create
    $this->actingAs($encargado)->get('/bitacoras/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('bitacoras/Create')
            ->where('isAdmin', false)
            ->where('currentUserId', $encargado->id)
            ->where('defaultBranchId', $branch->id)
        );

    // Admin in edit
    $this->actingAs($admin)->get("/bitacoras/{$bitacora->id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('bitacoras/Edit')
            ->where('isAdmin', true)
        );

    // Encargado in edit
    $this->actingAs($encargado)->get("/bitacoras/{$bitacora->id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('bitacoras/Edit')
            ->where('isAdmin', false)
        );
});

test('encargado cannot spoof user_id when creating bitacora', function () {
    $encargado = User::factory()->create();
    $encargado->assignRole('encargado');

    $otherUser = User::factory()->create();

    $branch = Branch::create(['name' => 'Sucursal Este', 'code' => 'EST', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Delta', 'code' => 'CLI-D', 'is_active' => true]);
    $encargado->branches()->attach($branch);

    $response = $this->actingAs($encargado)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $otherUser->id, // Attempting to assign to another user
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '888',
        'date' => '2026-08-21',
    ]);

    $bitacora = Bitacora::where('folio_number', 'BIT-888')->first();
    expect($bitacora)->not->toBeNull();
    // Must be forced to the encargado's id
    expect($bitacora->user_id)->toBe($encargado->id);
});

test('encargado cannot alter user_id or branch_id when updating bitacora', function () {
    $branchA = Branch::create(['name' => 'Sucursal A', 'code' => 'SUC-A', 'is_active' => true]);
    $branchB = Branch::create(['name' => 'Sucursal B', 'code' => 'SUC-B', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Epsilon', 'code' => 'CLI-E', 'is_active' => true]);

    $encargado = User::factory()->create();
    $encargado->assignRole('encargado');
    $encargado->branches()->attach($branchA);

    $otherUser = User::factory()->create();

    $activityType = ActivityType::create(['name' => 'Revisión', 'is_active' => true]);

    $bitacora = Bitacora::create([
        'branch_id' => $branchA->id,
        'user_id' => $encargado->id,
        'client_id' => $client->id,
        'folio_number' => 'BIT-777',
        'date' => '2026-08-22',
    ]);

    $response = $this->actingAs($encargado)->put("/bitacoras/{$bitacora->id}", [
        'user_id' => $otherUser->id,
        'branch_id' => $branchB->id,
        'notes' => 'Actualizando actividades',
        'activities' => [
            [
                'date' => '2026-08-22',
                'activity_type_id' => $activityType->id,
                'description' => 'Revisión de instalaciones',
                'employees' => [],
                'expenses' => [],
            ],
        ],
    ]);

    $bitacora->refresh();
    // Must preserve original user_id and branch_id
    expect($bitacora->user_id)->toBe($encargado->id)
        ->and($bitacora->branch_id)->toBe($branchA->id);
});

test('existing folio notes are exposed to create view and inherited on store', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Notas', 'code' => 'SUC-N', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Notas', 'code' => 'CLI-N', 'is_active' => true]);

    $originalBitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '100',
        'folio_number' => 'BIT-100',
        'date' => '2026-08-01',
        'notes' => 'Comentario original fijo e inmutable del folio',
        'is_closed' => false,
    ]);

    // 1. In create view, existingBitacoraFolios must include the notes of BIT-100
    $response = $this->actingAs($admin)->get('/bitacoras/create');
    $response->assertOk();
    $response->assertInertia(function ($page) {
        $existing = collect($page->toArray()['props']['existingBitacoraFolios']);
        $folio100 = $existing->firstWhere('folio_number', 'BIT-100');
        expect($folio100)->not->toBeNull()
            ->and($folio100['notes'])->toBe('Comentario original fijo e inmutable del folio');
    });

    // 2. When creating a new date for BIT-100, notes must be inherited even if empty or different notes are sent
    $storeResponse = $this->actingAs($admin)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '100',
        'date' => '2026-08-02',
        'notes' => 'Intento de modificar comentario',
    ]);

    $newBitacora = Bitacora::where('folio_number', 'BIT-100')->where('date', '2026-08-02')->first();
    expect($newBitacora)->not->toBeNull()
        ->and($newBitacora->notes)->toBe('Comentario original fijo e inmutable del folio');
});

test('updating bitacora does not allow modifying notes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Fix', 'code' => 'SUC-F', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Fix', 'code' => 'CLI-F', 'is_active' => true]);
    $activityType = ActivityType::create(['name' => 'Prueba', 'is_active' => true]);

    $bitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_number' => 'BIT-200',
        'date' => '2026-08-05',
        'notes' => 'Comentario sagrado de la bitacora',
        'is_closed' => false,
    ]);

    $this->actingAs($admin)->put("/bitacoras/{$bitacora->id}", [
        'notes' => 'Intento de cambiar notas al editar',
        'activities' => [
            [
                'date' => '2026-08-05',
                'activity_type_id' => $activityType->id,
                'description' => 'Actividad de prueba',
                'employees' => [],
                'expenses' => [],
            ],
        ],
    ]);

    $bitacora->refresh();
    expect($bitacora->notes)->toBe('Comentario sagrado de la bitacora');
});
