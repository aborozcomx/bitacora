<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Folio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FolioController extends Controller
{
    public function index(Request $request): Response
    {
        $folios = Folio::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('catalogs/Folios/Index', [
            'folios' => $folios,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:folios,name',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        Folio::create([
            'name' => strtoupper(trim($validated['name'])),
            'description' => $validated['description'] ?? null,
            'current_consecutive' => 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->back()->with('success', 'Folio creado exitosamente.');
    }

    public function update(Request $request, Folio $folio): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:folios,name,'.$folio->id,
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $folio->update([
            'name' => strtoupper(trim($validated['name'])),
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->back()->with('success', 'Folio actualizado exitosamente.');
    }

    public function destroy(Folio $folio): RedirectResponse
    {
        $folio->delete();

        return redirect()->back()->with('success', 'Folio eliminado exitosamente.');
    }
}
