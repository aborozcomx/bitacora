<?php

use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin can view clients catalog', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Client::create([
        'name' => 'Cliente Test',
        'code' => 'CLI-TEST',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->get('/catalogs/clients');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('catalogs/Clients/Index')
        ->has('clients.data', 1)
    );
});

test('admin can create and update clients', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Create
    $response = $this->actingAs($admin)->post('/catalogs/clients', [
        'name' => 'Acme Corporation',
        'code' => 'ACME-01',
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('clients', ['code' => 'ACME-01', 'name' => 'Acme Corporation']);

    $client = Client::where('code', 'ACME-01')->first();

    // Update
    $updateResponse = $this->actingAs($admin)->put("/catalogs/clients/{$client->id}", [
        'name' => 'Acme Corp Updated',
        'code' => 'ACME-01',
        'is_active' => true,
    ]);

    $updateResponse->assertRedirect();
    $this->assertDatabaseHas('clients', ['name' => 'Acme Corp Updated', 'code' => 'ACME-01']);
});

test('admin can create and update client branches with contact info', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $client = Client::create([
        'name' => 'Global Logistics',
        'code' => 'GL-01',
        'is_active' => true,
    ]);

    // Create branch with contact info
    $response = $this->actingAs($admin)->post("/catalogs/clients/{$client->id}/branches", [
        'name' => 'Sucursal Norte',
        'code' => 'SN-01',
        'address' => 'Av. Industrial 123',
        'contact_name' => 'John Doe',
        'phone' => '555-7777',
        'email' => 'contact@snorte.com',
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('client_branches', [
        'client_id' => $client->id,
        'name' => 'Sucursal Norte',
        'code' => 'SN-01',
        'contact_name' => 'John Doe',
        'phone' => '555-7777',
        'email' => 'contact@snorte.com',
    ]);

    $branch = ClientBranch::where('code', 'SN-01')->first();

    // Update branch
    $updateResponse = $this->actingAs($admin)->put("/catalogs/clients/branches/{$branch->id}", [
        'name' => 'Sucursal Norte Modificada',
        'code' => 'SN-01',
        'address' => 'Av. Industrial 456',
        'contact_name' => 'Jane Doe',
        'phone' => '555-8888',
        'email' => 'jane@snorte.com',
        'is_active' => true,
    ]);

    $updateResponse->assertRedirect();
    $this->assertDatabaseHas('client_branches', [
        'id' => $branch->id,
        'name' => 'Sucursal Norte Modificada',
        'contact_name' => 'Jane Doe',
        'email' => 'jane@snorte.com',
        'phone' => '555-8888',
    ]);
});

test('admin can search clients by branch contact name or email', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $client = Client::create([
        'name' => 'Alfa Corp',
        'code' => 'ALF-01',
        'is_active' => true,
    ]);

    ClientBranch::create([
        'client_id' => $client->id,
        'name' => 'Sucursal Sur',
        'code' => 'SS-01',
        'contact_name' => 'Carlos Slim',
        'email' => 'carlos@alfacorp.com',
        'phone' => '555-4321',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->get('/catalogs/clients?search=Slim');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('catalogs/Clients/Index')
        ->has('clients.data', 1)
        ->where('clients.data.0.code', 'ALF-01')
    );
});

test('clients catalog respects per_page and pagination page parameters', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Create 12 clients
    for ($i = 1; $i <= 12; $i++) {
        Client::create([
            'name' => "Cliente {$i}",
            'code' => sprintf('CLI-%02d', $i),
            'is_active' => true,
        ]);
    }

    // Default 10 per page
    $responseDefault = $this->actingAs($admin)->get('/catalogs/clients');
    $responseDefault->assertOk();
    $responseDefault->assertInertia(fn ($page) => $page
        ->component('catalogs/Clients/Index')
        ->has('clients.data', 10)
        ->where('clients.total', 12)
        ->where('clients.per_page', 10)
        ->where('clients.current_page', 1)
    );

    // Custom per_page=5, page=2
    $responsePerPage = $this->actingAs($admin)->get('/catalogs/clients?per_page=5&page=2');
    $responsePerPage->assertOk();
    $responsePerPage->assertInertia(fn ($page) => $page
        ->component('catalogs/Clients/Index')
        ->has('clients.data', 5)
        ->where('clients.total', 12)
        ->where('clients.per_page', 5)
        ->where('clients.current_page', 2)
        ->where('filters.per_page', 5)
    );

    // Custom per_page=25 (all 12 in page 1)
    $responseAll = $this->actingAs($admin)->get('/catalogs/clients?per_page=25');
    $responseAll->assertOk();
    $responseAll->assertInertia(fn ($page) => $page
        ->component('catalogs/Clients/Index')
        ->has('clients.data', 12)
        ->where('clients.total', 12)
        ->where('clients.per_page', 25)
    );
});
