<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    public function index()
    {
        $shippingMethods = ShippingMethod::query()
            ->with('paymentMethods')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.shipping_methods.index', compact('shippingMethods'));
    }

    public function create()
    {
        return view('admin.shipping_methods.form', [
            'shippingMethod' => new ShippingMethod(),
            'paymentMethods' => PaymentMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedPaymentMethods' => [],
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateInput($request);
        $paymentMethodIds = $data['payment_method_ids'] ?? [];
        unset($data['payment_method_ids']);

        $shippingMethod = ShippingMethod::create($data);
        $shippingMethod->paymentMethods()->sync($paymentMethodIds);

        return redirect()->route('admin.shipping-methods.index')->with('success', __('messages.shipping_method_created'));
    }

    public function edit(ShippingMethod $shipping_method)
    {
        return view('admin.shipping_methods.form', [
            'shippingMethod' => $shipping_method,
            'paymentMethods' => PaymentMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedPaymentMethods' => $shipping_method->paymentMethods()->pluck('payment_methods.id')->all(),
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, ShippingMethod $shipping_method)
    {
        $data = $this->validateInput($request, $shipping_method->id);
        $paymentMethodIds = $data['payment_method_ids'] ?? [];
        unset($data['payment_method_ids']);

        $shipping_method->update($data);
        $shipping_method->paymentMethods()->sync($paymentMethodIds);

        return redirect()->route('admin.shipping-methods.index')->with('success', __('messages.shipping_method_updated'));
    }

    public function destroy(ShippingMethod $shipping_method)
    {
        $shipping_method->delete();

        return redirect()->route('admin.shipping-methods.index')->with('success', __('messages.shipping_method_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateInput(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:64', 'unique:shipping_methods,code,' . $id],
            'name' => ['required', 'string', 'max:191'],
            'provider_code' => ['required', 'string', 'max:64'],
            'type' => ['required', 'string', 'max:64'],
            'is_active' => ['nullable', 'boolean'],
            'price_net' => ['required', 'numeric', 'min:0'],
            'price_gross' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'payload_json' => ['nullable', 'array'],
            'payment_method_ids' => ['nullable', 'array'],
            'payment_method_ids.*' => ['integer', 'exists:payment_methods,id'],
        ]);
    }
}
