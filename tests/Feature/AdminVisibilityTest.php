<?php

use App\Models\Bitacora;
use App\Models\Branch;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('shared auth user includes isAdmin and roles properly for admin and encargado', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $encargado = User::factory()->create();
    $encargado->assignRole('encargado');

    $adminResponse = $this->actingAs($admin)->get('/dashboard');
    $adminResponse->assertOk();
    $adminResponse->assertInertia(fn ($page) => $page
        ->where('auth.user.isAdmin', true)
        ->where('auth.user.roles', ['admin'])
    );

    $encargadoResponse = $this->actingAs($encargado)->get('/dashboard');
    $encargadoResponse->assertOk();
    $encargadoResponse->assertInertia(fn ($page) => $page
        ->where('auth.user.isAdmin', false)
        ->where('auth.user.roles', ['encargado'])
    );
});

test('folios index only provides kpi metrics to administrators', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $encargado = User::factory()->create();
    $encargado->assignRole('encargado');

    $branch = Branch::create(['name' => 'Sucursal Visibilidad', 'code' => 'SUC-VIS', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Visibilidad', 'code' => 'CLI-VIS', 'is_active' => true]);

    Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $encargado->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-VIS-01',
        'date' => '2026-09-01',
        'is_closed' => false,
    ]);

    // Admin should see kpis and isAdmin true
    $adminResponse = $this->actingAs($admin)->get('/bitacoras');
    $adminResponse->assertOk();
    $adminResponse->assertInertia(fn ($page) => $page
        ->component('bitacoras/Index')
        ->where('isAdmin', true)
        ->has('kpis')
        ->where('kpis.total_folios', 1)
    );

    // Encargado should receive kpis null and isAdmin false
    $encargadoResponse = $this->actingAs($encargado)->get('/bitacoras');
    $encargadoResponse->assertOk();
    $encargadoResponse->assertInertia(fn ($page) => $page
        ->component('bitacoras/Index')
        ->where('isAdmin', false)
        ->where('kpis', null)
    );
});

test('folios finalized only provides kpi metrics to administrators', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $encargado = User::factory()->create();
    $encargado->assignRole('encargado');

    $branch = Branch::create(['name' => 'Sucursal Fin', 'code' => 'SUC-FIN', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Fin', 'code' => 'CLI-FIN', 'is_active' => true]);

    Bitacora::create([
        'branch_id' => $branch->id,
        'user_id' => $encargado->id,
        'client_id' => $client->id,
        'folio_number' => 'FOL-FIN-01',
        'date' => '2026-09-01',
        'is_closed' => true,
        'closed_at' => now(),
    ]);

    // Admin should see kpis and isAdmin true
    $adminResponse = $this->actingAs($admin)->get('/bitacoras-finalizadas');
    $adminResponse->assertOk();
    $adminResponse->assertInertia(fn ($page) => $page
        ->component('bitacoras/Finalized')
        ->where('isAdmin', true)
        ->has('kpis')
        ->where('kpis.total_folios', 1)
    );

    // Encargado should receive kpis null and isAdmin false
    $encargadoResponse = $this->actingAs($encargado)->get('/bitacoras-finalizadas');
    $encargadoResponse->assertOk();
    $encargadoResponse->assertInertia(fn ($page) => $page
        ->component('bitacoras/Finalized')
        ->where('isAdmin', false)
        ->where('kpis', null)
    );
});
