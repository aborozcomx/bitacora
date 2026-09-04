<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientBranch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $clients = Client::query()
            ->with(['branches'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('catalogs/Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:clients,code',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        Client::create($validated);

        return redirect()->back()->with('success', 'Cliente creado exitosamente.');
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:clients,code,'.$client->id,
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        $client->update($validated);

        return redirect()->back()->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->back()->with('success', 'Cliente eliminado exitosamente.');
    }

    public function storeBranch(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $client->branches()->create($validated);

        return redirect()->back()->with('success', 'Sucursal del cliente agregada exitosamente.');
    }

    public function updateBranch(Request $request, ClientBranch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $branch->update($validated);

        return redirect()->back()->with('success', 'Sucursal del cliente actualizada exitosamente.');
    }

    public function destroyBranch(ClientBranch $branch): RedirectResponse
    {
        $branch->delete();

        return redirect()->back()->with('success', 'Sucursal del cliente eliminada exitosamente.');
    }
}
