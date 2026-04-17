<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class PaymentTransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = PaymentTransaction::query()
            ->with(['order', 'paymentMethod'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('provider_code'), fn ($q) => $q->where('provider_code', $request->input('provider_code')))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.payment_transactions.index', compact('transactions'));
    }

    public function show(PaymentTransaction $payment_transaction)
    {
        $payment_transaction->load(['order.items', 'paymentMethod']);

        return view('admin.payment_transactions.show', [
            'transaction' => $payment_transaction,
        ]);
    }
}
