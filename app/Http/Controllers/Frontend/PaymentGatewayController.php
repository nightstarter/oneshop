<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function callback(Request $request, string $provider)
    {
        $transaction = $this->payments->handleCallback($provider, $request->all());

        if (! $transaction) {
            return response('transaction-not-found', 404);
        }

        return response('ok', 200);
    }

    public function return(Request $request, string $provider)
    {
        $transaction = $this->payments->handleReturn($provider, $request->all());

        if (! $transaction) {
            return redirect()->route('home')->with('warning', __('messages.payment_not_found'));
        }

        return redirect()->route('checkout.success', $transaction->order)->with('order_placed', true);
    }
}
