<?php

use App\Models\ActivityType;
use App\Models\Bitacora;
use App\Models\BitacoraEmployee;
use App\Models\BitacoraExpense;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Folio;
use App\Models\PaymentMethod;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('allows creating bitacoras with same series and folio if dates are different', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Centro', 'code' => 'SUC-CEN', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente ABC', 'code' => 'ABC-01', 'is_active' => true]);
    $folio = Folio::create(['name' => 'BIT', 'current_consecutive' => 1, 'is_active' => true]);

    // First bitacora: BIT-1 on 2026-09-01
    $response1 = $this->actingAs($admin)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '1',
        'date' => '2026-09-01',
    ]);
    $response1->assertRedirect();
    $this->assertDatabaseHas('bitacoras', [
        'folio_number' => 'BIT-1',
        'date' => '2026-09-01',
    ]);

    // Second bitacora: SAME BIT-1 on DIFFERENT date 2026-09-02 -> MUST SUCCEED
    $response2 = $this->actingAs($admin)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '1',
        'date' => '2026-09-02',
    ]);
    $response2->assertRedirect();
    $this->assertDatabaseHas('bitacoras', [
        'folio_number' => 'BIT-1',
        'date' => '2026-09-02',
    ]);

    expect(Bitacora::where('folio_number', 'BIT-1')->count())->toBe(2);

    // Reusing existing consecutive 1 should not inflate catalog's current_consecutive
    expect($folio->fresh()->current_consecutive)->toBe(1);
});

test('rejects creating bitacora with same series and folio on the same date', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Centro', 'code' => 'SUC-CEN', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente ABC', 'code' => 'ABC-01', 'is_active' => true]);

    // Existing bitacora
    Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '5',
        'folio_number' => 'BIT-5',
        'date' => '2026-09-01',
    ]);

    // Attempt duplicate on SAME date 2026-09-01
    $response = $this->actingAs($admin)->post('/bitacoras', [
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '5',
        'date' => '2026-09-01',
    ]);

    $response->assertSessionHasErrors(['folio_consecutive']);
    expect(Bitacora::where('folio_number', 'BIT-5')->count())->toBe(1);
});

test('rejects updating bitacora to duplicate folio on same date of another bitacora', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Centro', 'code' => 'SUC-CEN', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente ABC', 'code' => 'ABC-01', 'is_active' => true]);
    $actType = ActivityType::create(['name' => 'Limpieza General', 'is_active' => true]);

    $bitacora1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '10',
        'folio_number' => 'BIT-10',
        'date' => '2026-09-01',
    ]);

    $bitacora2 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '11',
        'folio_number' => 'BIT-11',
        'date' => '2026-09-01',
    ]);

    // Try updating bitacora2 to BIT-10 on same date
    $response = $this->actingAs($admin)->put("/bitacoras/{$bitacora2->id}", [
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '10',
        'date' => '2026-09-01',
        'activities' => [
            [
                'date' => '2026-09-01',
                'activity_type_id' => $actType->id,
                'description' => 'Actividad prueba',
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['folio_consecutive']);
});

test('expense report aggregates same folio across different dates in byFolio with date breakdown', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Sur', 'code' => 'SUC-SUR', 'is_active' => true]);
    $pm = PaymentMethod::create(['name' => 'Efectivo', 'slug' => 'efectivo', 'is_active' => true]);

    // Bitacora 1 on 2026-09-02 (Wednesday)
    $bitacoraDay1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'folio_prefix' => 'OBRA',
        'folio_consecutive' => '100',
        'folio_number' => 'OBRA-100',
        'date' => '2026-09-02',
    ]);

    BitacoraExpense::create([
        'bitacora_id' => $bitacoraDay1->id,
        'concept' => 'Materiales Día 1',
        'amount' => 500.00,
        'date' => '2026-09-02',
        'payment_method_id' => $pm->id,
    ]);

    // Bitacora 2 on 2026-09-03 (Thursday) with SAME folio OBRA-100
    $bitacoraDay2 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'folio_prefix' => 'OBRA',
        'folio_consecutive' => '100',
        'folio_number' => 'OBRA-100',
        'date' => '2026-09-03',
    ]);

    BitacoraExpense::create([
        'bitacora_id' => $bitacoraDay2->id,
        'concept' => 'Materiales Día 2',
        'amount' => 750.00,
        'date' => '2026-09-03',
        'payment_method_id' => $pm->id,
    ]);

    $response = $this->actingAs($admin)->get('/expenses?start_date=2026-09-02&end_date=2026-09-04');
    $response->assertStatus(200);

    $response->assertInertia(function ($page) {
        $byFolio = collect($page->toArray()['props']['byFolio']);
        $obraFolio = $byFolio->firstWhere('folio_number', 'OBRA-100');

        expect($obraFolio)->not->toBeNull();
        expect((float) $obraFolio['total_amount'])->toBe(1250.00);
        expect($obraFolio['total_count'])->toBe(2);
        expect($obraFolio['days_count'])->toBe(2);
        expect($obraFolio['dates'])->toHaveCount(2);
        expect($obraFolio['dates'][0]['date'])->toBe('2026-09-02');
        expect((float) $obraFolio['dates'][0]['amount'])->toBe(500.00);
        expect($obraFolio['dates'][1]['date'])->toBe('2026-09-03');
        expect((float) $obraFolio['dates'][1]['amount'])->toBe(750.00);
    });
});

test('salary report aggregates same folio across different dates in byFolio and employee by_folio with dates visible', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Norte', 'code' => 'SUC-NOR', 'is_active' => true]);
    $employee = Employee::create([
        'first_name' => 'Carlos',
        'last_name' => 'Gómez',
        'employee_code' => 'EMP-CARLOS',
        'branch_id' => $branch->id,
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    // Bitacora 1: Folio SERV-20 on 2026-09-02 (8 hrs)
    $b1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'folio_prefix' => 'SERV',
        'folio_consecutive' => '20',
        'folio_number' => 'SERV-20',
        'date' => '2026-09-02',
    ]);

    BitacoraEmployee::create([
        'bitacora_id' => $b1->id,
        'employee_id' => $employee->id,
        'date' => '2026-09-02',
        'hours_worked' => 8.0,
        'overtime_hours' => 0.0,
        'base_rate_applied' => 100.00,
        'overtime_rate_applied' => 150.00,
        'total_earned' => 800.00,
        'is_absent' => false,
    ]);

    // Bitacora 2: Folio SERV-20 on 2026-09-03 (6 hrs normal + 2 hrs overtime)
    $b2 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'folio_prefix' => 'SERV',
        'folio_consecutive' => '20',
        'folio_number' => 'SERV-20',
        'date' => '2026-09-03',
    ]);

    BitacoraEmployee::create([
        'bitacora_id' => $b2->id,
        'employee_id' => $employee->id,
        'date' => '2026-09-03',
        'hours_worked' => 6.0,
        'overtime_hours' => 2.0,
        'base_rate_applied' => 100.00,
        'overtime_rate_applied' => 150.00,
        'total_earned' => 900.00,
        'is_absent' => false,
    ]);

    $response = $this->actingAs($admin)->get('/salaries?start_date=2026-09-02&end_date=2026-09-04');
    $response->assertStatus(200);

    $response->assertInertia(function ($page) {
        $props = $page->toArray()['props'];
        $byFolio = collect($props['byFolio']);
        $servFolio = $byFolio->firstWhere('folio_number', 'SERV-20');

        // Check period-wide folio summation
        expect($servFolio)->not->toBeNull();
        expect($servFolio['days_count'])->toBe(2);
        expect((float) $servFolio['total_regular_hours'])->toBe(14.0);
        expect((float) $servFolio['total_overtime_hours'])->toBe(2.0);
        expect((float) $servFolio['total_pay'])->toBe(1700.00);
        expect($servFolio['dates'])->toHaveCount(2);

        // Check employee-level folio breakdown
        $payroll = collect($props['payrollSummary']);
        $carlos = $payroll->firstWhere('employee_code', 'EMP-CARLOS');

        expect($carlos)->not->toBeNull();
        expect($carlos['unique_folios_count'])->toBe(1);
        expect($carlos['by_folio'])->toHaveCount(1);
        expect($carlos['by_folio'][0]['folio_number'])->toBe('SERV-20');
        expect($carlos['by_folio'][0]['days_count'])->toBe(2);
        expect($carlos['by_folio'][0]['dates'])->toBe(['2026-09-02', '2026-09-03']);
        expect((float) $carlos['by_folio'][0]['total_hours_worked'])->toBe(14.0);
        expect((float) $carlos['by_folio'][0]['total_overtime_hours'])->toBe(2.0);
        expect((float) $carlos['by_folio'][0]['total_earned'])->toBe(1700.00);
    });
});
