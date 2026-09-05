<?php

use App\Models\ActivityType;
use App\Models\Bitacora;
use App\Models\BitacoraEmployee;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('can save employee with partial shift and reason', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Norte', 'code' => 'SUC-NOR', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente ABC', 'code' => 'ABC-01', 'is_active' => true]);
    $actType = ActivityType::create(['name' => 'Mantenimiento', 'is_active' => true]);

    $emp = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Luis',
        'last_name' => 'Hernández',
        'employee_code' => 'EMP-LUIS',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    $bitacora = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-PARTIAL-1',
        'date' => '2026-09-02',
    ]);

    $response = $this->actingAs($admin)->put("/bitacoras/{$bitacora->id}", [
        'activities' => [
            [
                'date' => '2026-09-02',
                'activity_type_id' => $actType->id,
                'description' => 'Apoyo parcial de 4 horas',
                'employees' => [
                    [
                        'employee_id' => $emp->id,
                        'is_absent' => false,
                        'is_partial_shift' => true,
                        'partial_shift_reason' => 'Medio turno por cita médica',
                        'hours_worked' => 4.0,
                        'overtime_hours' => 0.0,
                    ],
                ],
            ],
        ],
    ]);

    $response->assertRedirect("/bitacoras/{$bitacora->id}");

    $this->assertDatabaseHas('bitacora_employees', [
        'bitacora_id' => $bitacora->id,
        'employee_id' => $emp->id,
        'is_absent' => false,
        'is_partial_shift' => true,
        'partial_shift_reason' => 'Medio turno por cita médica',
        'hours_worked' => 4.00,
        'total_earned' => 400.00,
    ]);
});

test('salary report correctly identifies and filters partial shifts', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::create(['name' => 'Sucursal Norte', 'code' => 'SUC-NOR', 'is_active' => true]);

    $emp1 = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Luis',
        'last_name' => 'Hernández',
        'employee_code' => 'EMP-LUIS',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    $emp2 = Employee::create([
        'branch_id' => $branch->id,
        'first_name' => 'Carlos',
        'last_name' => 'Gómez',
        'employee_code' => 'EMP-CARLOS',
        'base_hourly_rate' => 100.00,
        'overtime_hourly_rate' => 150.00,
        'is_active' => true,
    ]);

    $b1 = Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $admin->id,
        'folio_number' => 'FOL-REP-1',
        'date' => '2026-09-02',
    ]);

    // Luis has a partial shift of 4 hrs
    BitacoraEmployee::create([
        'bitacora_id' => $b1->id,
        'employee_id' => $emp1->id,
        'date' => '2026-09-02',
        'hours_worked' => 4.0,
        'overtime_hours' => 0.0,
        'base_rate_applied' => 100.00,
        'overtime_rate_applied' => 150.00,
        'total_earned' => 400.00,
        'is_absent' => false,
        'is_partial_shift' => true,
        'partial_shift_reason' => 'Medio turno acordado',
    ]);

    // Carlos has a full shift of 8 hrs
    BitacoraEmployee::create([
        'bitacora_id' => $b1->id,
        'employee_id' => $emp2->id,
        'date' => '2026-09-02',
        'hours_worked' => 8.0,
        'overtime_hours' => 0.0,
        'base_rate_applied' => 100.00,
        'overtime_rate_applied' => 150.00,
        'total_earned' => 800.00,
        'is_absent' => false,
        'is_partial_shift' => false,
        'partial_shift_reason' => null,
    ]);

    // Request salary report
    $response = $this->actingAs($admin)->get('/salaries?start_date=2026-09-01&end_date=2026-09-03');
    $response->assertStatus(200);

    $response->assertInertia(function ($page) {
        $props = $page->toArray()['props'];
        $payroll = collect($props['payrollSummary']);
        $totals = $props['totals'];

        $luis = $payroll->firstWhere('employee_code', 'EMP-LUIS');
        expect($luis)->not->toBeNull();
        expect($luis['partial_shifts_count'])->toBe(1);
        expect($luis['bitacoras'][0]['is_partial_shift'])->toBeTrue();
        expect($luis['bitacoras'][0]['partial_shift_reason'])->toBe('Medio turno acordado');

        $carlos = $payroll->firstWhere('employee_code', 'EMP-CARLOS');
        expect($carlos)->not->toBeNull();
        expect($carlos['partial_shifts_count'])->toBe(0);
        expect($carlos['bitacoras'][0]['is_partial_shift'])->toBeFalse();

        expect($totals['grand_partial_shifts_count'])->toBe(1);
    });

    // Test with_partial_shifts filter
    $filteredResponse = $this->actingAs($admin)->get('/salaries?start_date=2026-09-01&end_date=2026-09-03&absence_filter=with_partial_shifts');
    $filteredResponse->assertStatus(200);

    $filteredResponse->assertInertia(function ($page) {
        $props = $page->toArray()['props'];
        $payroll = collect($props['payrollSummary']);

        expect($payroll)->toHaveCount(1);
        expect($payroll->first()['employee_code'])->toBe('EMP-LUIS');
    });
});
