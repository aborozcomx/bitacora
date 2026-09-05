<?php

use App\Models\Bitacora;
use App\Models\BitacoraExpense;
use App\Models\Branch;
use App\Models\PaymentCard;
use App\Models\PaymentMethod;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('expense report defaults to Thursday-to-Wednesday weekly range', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Norte', 'code' => 'SUC-NTE', 'is_active' => true]);

    $response = $this->actingAs($admin)->get('/expenses');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('expenses/Index')
        ->has('filters.start_date')
        ->has('filters.end_date')
        ->has('paymentMethods')
        ->has('paymentCards')
    );

    $start = Carbon::parse($response->inertiaPage()['props']['filters']['start_date']);
    $end = Carbon::parse($response->inertiaPage()['props']['filters']['end_date']);
    expect($start->isThursday())->toBeTrue();
    expect($end->isWednesday())->toBeTrue();
    expect($start->diffInDays($end))->toEqual(6);
});

test('expense report filters by payment method and payment card correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Oriente', 'code' => 'SUC-ORI', 'is_active' => true]);

    $methodCard = PaymentMethod::create([
        'name' => 'Tarjeta BBVA',
        'slug' => 'tarjeta-bbva',
        'requires_card_details' => true,
        'is_active' => true,
    ]);

    $card1 = PaymentCard::create([
        'alias' => 'BBVA Débito Operativa',
        'payment_method_id' => $methodCard->id,
        'bank_name' => 'BBVA',
        'card_number_masked' => '**** **** **** 1234',
        'is_active' => true,
    ]);

    $card2 = PaymentCard::create([
        'alias' => 'BBVA Crédito Gerencia',
        'payment_method_id' => $methodCard->id,
        'bank_name' => 'BBVA',
        'card_number_masked' => '**** **** **** 5678',
        'is_active' => true,
    ]);

    $methodCash = PaymentMethod::create([
        'name' => 'Efectivo',
        'slug' => 'efectivo',
        'requires_card_details' => false,
        'is_active' => true,
    ]);

    $bitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'folio_number' => 'FOL-EXP-01',
        'date' => '2026-08-12',
    ]);

    // Expense 1: with card 1
    BitacoraExpense::create([
        'bitacora_id' => $bitacora->id,
        'concept' => 'Gasolina Unidad 1',
        'amount' => 800.00,
        'date' => '2026-08-12',
        'payment_method_id' => $methodCard->id,
        'payment_card_id' => $card1->id,
        'reference_number' => 'REF-001',
    ]);

    // Expense 2: with card 2
    BitacoraExpense::create([
        'bitacora_id' => $bitacora->id,
        'concept' => 'Materiales de Limpieza',
        'amount' => 450.00,
        'date' => '2026-08-12',
        'payment_method_id' => $methodCard->id,
        'payment_card_id' => $card2->id,
        'reference_number' => 'REF-002',
    ]);

    // Expense 3: with cash
    BitacoraExpense::create([
        'bitacora_id' => $bitacora->id,
        'concept' => 'Comida personal',
        'amount' => 200.00,
        'date' => '2026-08-12',
        'payment_method_id' => $methodCash->id,
        'payment_card_id' => null,
        'reference_number' => 'REF-003',
    ]);

    // Query filtering by card 1
    $response = $this->actingAs($admin)->get("/expenses?start_date=2026-08-12&end_date=2026-08-20&payment_method_id={$methodCard->id}&payment_card_id={$card1->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('expenses/Index')
        ->where('grand_total', 800)
        ->where('total_transactions', 1)
        ->has('expenses.data', 1)
        ->where('expenses.data.0.payment_card.alias', 'BBVA Débito Operativa')
        ->where('expenses.data.0.payment_method.name', 'Tarjeta BBVA')
    );
});

test('expenses report can be filtered by encargado user_id', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $manager1 = User::factory()->create(['name' => 'Supervisor One']);
    $manager2 = User::factory()->create(['name' => 'Supervisor Two']);

    $branch = Branch::create(['name' => 'Sucursal Norte', 'code' => 'SUC-NOR', 'is_active' => true]);

    $method = PaymentMethod::create([
        'name' => 'Efectivo',
        'slug' => 'efectivo',
        'requires_card_details' => false,
        'is_active' => true,
    ]);

    $bitacora1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $manager1->id,
        'folio_number' => 'FOL-EXP-1',
        'date' => '2026-08-12',
    ]);
    $bitacora1->expenses()->create([
        'concept' => 'Combustible',
        'amount' => 450.00,
        'payment_method_id' => $method->id,
        'date' => '2026-08-12',
    ]);

    $bitacora2 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $manager2->id,
        'folio_number' => 'FOL-EXP-2',
        'date' => '2026-08-12',
    ]);
    $bitacora2->expenses()->create([
        'concept' => 'Comida',
        'amount' => 200.00,
        'payment_method_id' => $method->id,
        'date' => '2026-08-12',
    ]);

    // Query filtering by manager 1
    $response = $this->actingAs($admin)->get("/expenses?start_date=2026-08-10&end_date=2026-08-15&user_id={$manager1->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('expenses/Index')
        ->where('grand_total', 450)
        ->where('total_transactions', 1)
        ->has('expenses.data', 1)
        ->where('expenses.data.0.concept', 'Combustible')
    );
});
