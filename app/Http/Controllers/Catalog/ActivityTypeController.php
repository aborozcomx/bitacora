<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\ActivityType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, [5, 10, 15, 25, 50, 100])) {
            $perPage = 10;
        }

        $activities = ActivityType::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('catalogs/Activities/Index', [
            'activities' => $activities,
            'filters' => [
                'search' => $request->search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        ActivityType::create($validated);

        return redirect()->back()->with('success', 'Tipo de actividad registrado exitosamente.');
    }

    public function update(Request $request, ActivityType $activityType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $activityType->update($validated);

        return redirect()->back()->with('success', 'Tipo de actividad actualizado exitosamente.');
    }

    public function destroy(ActivityType $activityType): RedirectResponse
    {
        $activityType->delete();

        return redirect()->back()->with('success', 'Tipo de actividad eliminado exitosamente.');
    }
}
