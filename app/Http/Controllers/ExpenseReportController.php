<?php

namespace App\Http\Controllers;

use App\Models\BitacoraExpense;
use App\Models\Branch;
use App\Models\PaymentCard;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseReportController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Default to current weekly cycle: Wednesday to following Thursday
        if (! $request->filled('start_date') || ! $request->filled('end_date')) {
            $now = now();
            if ($now->dayOfWeek < \Carbon\Carbon::WEDNESDAY) {
                $start = $now->copy()->previous(\Carbon\Carbon::WEDNESDAY);
            } else {
                $start = $now->copy()->startOfDay()->subDays($now->dayOfWeek - \Carbon\Carbon::WEDNESDAY);
            }
            $end = $start->copy()->addDays(8);

            $startDate = $request->input('start_date', $start->toDateString());
            $endDate = $request->input('end_date', $end->toDateString());
        } else {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        $branchId = $request->input('branch_id');
        $userId = $request->input('user_id');
        $paymentMethodId = $request->input('payment_method_id');
        $paymentCardId = $request->input('payment_card_id');

        $baseQuery = BitacoraExpense::query()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->orWhere(function ($fallbackQ) use ($startDate, $endDate) {
                        $fallbackQ->whereNull('date')
                            ->whereHas('bitacora', function ($bQ) use ($startDate, $endDate) {
                                $bQ->whereBetween('date', [$startDate, $endDate]);
                            });
                    });
            })
            ->whereHas('bitacora', function ($q) use ($branchId, $userId, $user) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }

                if ($userId) {
                    $q->where('user_id', $userId);
                }

                if (! $user->hasRole('admin')) {
                    $q->whereIn('branch_id', $user->branches->pluck('id'));
                }
            })
            ->when($paymentMethodId, function ($query, $pmId) {
                $query->where('payment_method_id', $pmId);
            })
            ->when($paymentCardId, function ($query, $pcId) {
                $query->where('payment_card_id', $pcId);
            });

        $grandTotal = (clone $baseQuery)->sum('amount');
        $totalTransactions = (clone $baseQuery)->count();

        $expenses = (clone $baseQuery)
            ->with([
                'bitacora.branch',
                'bitacora.user',
                'paymentMethod',
                'cardType',
                'paymentCard',
            ])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $byPaymentMethod = (clone $baseQuery)
            ->selectRaw('payment_method_id, SUM(amount) as total_amount, COUNT(*) as total_count')
            ->groupBy('payment_method_id')
            ->with('paymentMethod')
            ->get();

        $branches = $user->hasRole('admin')
            ? Branch::where('is_active', true)->get()
            : $user->branches;

        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $paymentCards = PaymentCard::where('is_active', true)->get();

        return Inertia::render('expenses/Index', [
            'expenses' => $expenses,
            'byPaymentMethod' => $byPaymentMethod,
            'branches' => $branches,
            'users' => $users,
            'paymentMethods' => $paymentMethods,
            'paymentCards' => $paymentCards,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'branch_id' => $branchId,
                'user_id' => $userId,
                'payment_method_id' => $paymentMethodId,
                'payment_card_id' => $paymentCardId,
            ],
            'grand_total' => round((float) $grandTotal, 2),
            'total_transactions' => $totalTransactions,
        ]);
    }
}
