<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\RendersThemeViews;
use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    use RendersThemeViews;

    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
        private readonly ShippingService $shippingService,
        private readonly PaymentService $paymentService,
    ) {}

    public function index()
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', __('messages.checkout_empty'));
        }

        $totals   = $this->cart->totals();
        $customer = Auth::user()?->customer;
        $shippingMethods = $this->shippingService->availableMethods($items, $customer);

        $selectedShipping = $shippingMethods->firstWhere('id', (int) old('shipping_method_id'))
            ?? $shippingMethods->first();

        $paymentMethods = $selectedShipping
            ? $this->paymentService->availableMethodsForShipping($selectedShipping, $items, $customer)
            : collect();

        return $this->renderTheme('checkout.index', compact('items', 'totals', 'customer', 'shippingMethods', 'paymentMethods', 'selectedShipping'));
    }

    public function paymentMethods(Request $request): JsonResponse
    {
        $items = $this->cart->items();
        $customer = Auth::user()?->customer;

        if ($items->isEmpty()) {
            return response()->json([]);
        }

        $request->validate([
            'shipping_method_id' => ['required', 'integer'],
        ]);

        $shippingMethod = $this->shippingService->resolveSelectedMethod(
            (int) $request->input('shipping_method_id'),
            $items,
            $customer,
        );

        $paymentMethods = $this->paymentService
            ->availableMethodsForShipping($shippingMethod, $items, $customer)
            ->map(fn ($method) => [
                'id' => $method->id,
                'code' => $method->code,
                'name' => $method->name,
                'type' => $method->type,
                'price_net' => (float) $method->price_net,
                'price_gross' => (float) $method->price_gross,
            ])
            ->values();

        return response()->json($paymentMethods);
    }

    public function store(Request $request)
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', __('messages.checkout_empty'));
        }

        $rules = [
            'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'pickup_point_id' => ['nullable', 'string', 'max:100'],
            'pickup_point_name' => ['nullable', 'string', 'max:191'],
            'pickup_point_address' => ['nullable', 'string', 'max:191'],
            'billing_first_name' => ['required', 'string', 'max:100'],
            'billing_last_name'  => ['required', 'string', 'max:100'],
            'billing_street'     => ['required', 'string', 'max:191'],
            'billing_city'       => ['required', 'string', 'max:100'],
            'billing_zip'        => ['required', 'string', 'max:20'],
            'billing_country'    => ['required', 'string', 'max:2'],
            'use_billing_as_shipping' => ['boolean'],
            'shipping_first_name' => ['required_without:use_billing_as_shipping', 'nullable', 'string', 'max:100'],
            'shipping_last_name'  => ['required_without:use_billing_as_shipping', 'nullable', 'string', 'max:100'],
            'shipping_street'     => ['required_without:use_billing_as_shipping', 'nullable', 'string', 'max:191'],
            'shipping_city'       => ['required_without:use_billing_as_shipping', 'nullable', 'string', 'max:100'],
            'shipping_zip'        => ['required_without:use_billing_as_shipping', 'nullable', 'string', 'max:20'],
            'shipping_country'    => ['required_without:use_billing_as_shipping', 'nullable', 'string', 'max:2'],
            'note'               => ['nullable', 'string', 'max:1000'],
        ];

        if (! Auth::check()) {
            $rules['email'] = ['required', 'email', 'max:191'];
        }

        $data = $request->validate($rules);

        $billing = [
            'first_name' => $data['billing_first_name'],
            'last_name'  => $data['billing_last_name'],
            'street'     => $data['billing_street'],
            'city'       => $data['billing_city'],
            'zip'        => $data['billing_zip'],
            'country'    => $data['billing_country'],
            'email'      => $data['email'] ?? Auth::user()?->email,
        ];

        $useSame = $request->boolean('use_billing_as_shipping');
        $shipping = $useSame ? $billing : [
            'first_name' => $data['shipping_first_name'],
            'last_name'  => $data['shipping_last_name'],
            'street'     => $data['shipping_street'],
            'city'       => $data['shipping_city'],
            'zip'        => $data['shipping_zip'],
            'country'    => $data['shipping_country'],
            'email'      => $billing['email'],
        ];

        $user     = Auth::user();

        $result = $this->checkout->placeOrder(
            $items,
            $data,
            $user,
            $billing,
            $shipping,
            $data['note'] ?? null,
        );

        $this->cart->clear();

        if ($result['is_redirect'] && $result['redirect_url']) {
            return redirect()->away($result['redirect_url']);
        }

        return redirect()->route('checkout.success', $result['order'])->with('order_placed', true);
    }

    public function success(\App\Models\Order $order)
    {
        // Prevent direct URL access after session is gone
        abort_unless(session('order_placed') || (Auth::check() && $order->user_id === Auth::id()), 404);

        $order->load('paymentTransactions');

        return $this->renderTheme('checkout.success', compact('order'));
    }
}
