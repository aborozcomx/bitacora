<?php

use App\Models\Branch;
use App\Models\Client;
use App\Models\Folio;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
});

test('unauthenticated users cannot view folios', function () {
    $response = $this->get(route('catalogs.folios.index'));

    $response->assertRedirect(route('login'));
});

test('admin can view folios catalog', function () {
    Folio::factory()->create(['name' => 'TST', 'current_consecutive' => 5]);

    $response = $this->actingAs($this->user)->get(route('catalogs.folios.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('catalogs/Folios/Index')
        ->has('folios.data')
    );
});

test('admin can create a new folio with consecutive starting at 0', function () {
    $response = $this->actingAs($this->user)->post(route('catalogs.folios.store'), [
        'name' => 'obras',
        'description' => 'Bitácoras de obras y proyectos',
        'current_consecutive' => 9999, // Intentar inyectar consecutivo no debe tener efecto
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('folios', [
        'name' => 'OBRAS',
        'current_consecutive' => 0, // Consecutivo estrictamente 0 al crear
        'description' => 'Bitácoras de obras y proyectos',
        'is_active' => true,
    ]);
});

test('folio name must be unique', function () {
    Folio::factory()->create(['name' => 'EXISTING']);

    $response = $this->actingAs($this->user)->post(route('catalogs.folios.store'), [
        'name' => 'EXISTING',
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('admin can update a folio without changing its consecutive', function () {
    $folio = Folio::factory()->create([
        'name' => 'OLD',
        'current_consecutive' => 42,
        'description' => 'Antigua descripción',
    ]);

    $response = $this->actingAs($this->user)->put(route('catalogs.folios.update', $folio), [
        'name' => 'NEW',
        'description' => 'Nueva descripción',
        'current_consecutive' => 100, // Intentar modificar en el form no debe alterar el valor real
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $folio->refresh();
    expect($folio->name)->toBe('NEW')
        ->and($folio->description)->toBe('Nueva descripción')
        ->and($folio->current_consecutive)->toBe(42); // Se mantiene intacto
});

test('admin can delete a folio', function () {
    $folio = Folio::factory()->create(['name' => 'DEL']);

    $response = $this->actingAs($this->user)->delete(route('catalogs.folios.destroy', $folio));

    $response->assertRedirect();
    $this->assertSoftDeleted('folios', ['id' => $folio->id]);
});

test('creating a bitacora automatically increments folio consecutive', function () {
    $branch = Branch::create(['name' => 'Sucursal Norte', 'code' => 'SUC-NTE', 'is_active' => true]);
    $client = Client::create(['name' => 'Cliente Alpha', 'code' => 'CLI-ALP', 'is_active' => true]);
    $folio = Folio::factory()->create([
        'name' => 'BIT',
        'current_consecutive' => 3,
    ]);

    $response = $this->actingAs($this->user)->post(route('bitacoras.store'), [
        'branch_id' => $branch->id,
        'user_id' => $this->user->id,
        'client_id' => $client->id,
        'client_branch_id' => null,
        'folio_prefix' => 'BIT',
        'folio_consecutive' => '4',
        'date' => now()->toDateString(),
        'notes' => 'Prueba de sincronización',
    ]);

    $response->assertRedirect();

    $folio->refresh();
    expect($folio->current_consecutive)->toBe(4);
});

test('folios catalog respects per_page and search query parameters', function () {
    for ($i = 1; $i <= 18; $i++) {
        Folio::factory()->create([
            'name' => sprintf('FOL%02d', $i),
            'description' => "Descripcion {$i}",
        ]);
    }

    // Default 15 per page
    $responseDefault = $this->actingAs($this->user)->get(route('catalogs.folios.index'));
    $responseDefault->assertOk();
    $responseDefault->assertInertia(fn ($page) => $page
        ->component('catalogs/Folios/Index')
        ->has('folios.data', 15)
        ->where('folios.per_page', 15)
    );

    // Custom per_page=10, page=2
    $responsePerPage = $this->actingAs($this->user)->get(route('catalogs.folios.index', ['per_page' => 10, 'page' => 2]));
    $responsePerPage->assertOk();
    $responsePerPage->assertInertia(fn ($page) => $page
        ->component('catalogs/Folios/Index')
        ->has('folios.data', 8)
        ->where('folios.current_page', 2)
        ->where('filters.per_page', 10)
    );
});
