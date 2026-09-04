<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\CardType;
use App\Models\PaymentCard;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PaymentCatalogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('catalogs/PaymentMethods/Index', [
            'paymentMethods' => PaymentMethod::latest()->get(),
            'cardTypes' => CardType::latest()->get(),
            'paymentCards' => PaymentCard::with(['paymentMethod', 'cardType'])->latest()->get(),
        ]);
    }

    // --- Payment Methods ---
    public function storeMethod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'requires_card_details' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        PaymentMethod::create($validated);

        return redirect()->back()->with('success', 'Método de pago registrado exitosamente.');
    }

    public function updateMethod(Request $request, PaymentMethod $method): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'requires_card_details' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $method->update($validated);

        return redirect()->back()->with('success', 'Método de pago actualizado exitosamente.');
    }

    public function destroyMethod(PaymentMethod $method): RedirectResponse
    {
        $method->delete();

        return redirect()->back()->with('success', 'Método de pago eliminado exitosamente.');
    }

    // --- Card Types ---
    public function storeCardType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        CardType::create($validated);

        return redirect()->back()->with('success', 'Tipo de tarjeta registrado exitosamente.');
    }

    public function updateCardType(Request $request, CardType $cardType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $cardType->update($validated);

        return redirect()->back()->with('success', 'Tipo de tarjeta actualizado exitosamente.');
    }

    public function destroyCardType(CardType $cardType): RedirectResponse
    {
        $cardType->delete();

        return redirect()->back()->with('success', 'Tipo de tarjeta eliminado exitosamente.');
    }

    // --- Payment Cards / Accounts ---
    public function storeCard(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'alias' => 'required|string|max:255',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'card_type_id' => 'nullable|exists:card_types,id',
            'bank_name' => 'nullable|string|max:255',
            'card_number_masked' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        PaymentCard::create($validated);

        return redirect()->back()->with('success', 'Tarjeta / Cuenta de pago registrada exitosamente.');
    }

    public function updateCard(Request $request, PaymentCard $card): RedirectResponse
    {
        $validated = $request->validate([
            'alias' => 'required|string|max:255',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'card_type_id' => 'nullable|exists:card_types,id',
            'bank_name' => 'nullable|string|max:255',
            'card_number_masked' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $card->update($validated);

        return redirect()->back()->with('success', 'Tarjeta / Cuenta de pago actualizada exitosamente.');
    }

    public function destroyCard(PaymentCard $card): RedirectResponse
    {
        $card->delete();

        return redirect()->back()->with('success', 'Tarjeta / Cuenta de pago eliminada exitosamente.');
    }
}
