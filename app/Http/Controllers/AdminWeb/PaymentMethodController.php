<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::query()
            ->with('shippingMethods')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.payment_methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        return view('admin.payment_methods.form', [
            'paymentMethod' => new PaymentMethod(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        PaymentMethod::create($this->validateInput($request));

        return redirect()->route('admin.payment-methods.index')->with('success', __('messages.payment_method_created'));
    }

    public function edit(PaymentMethod $payment_method)
    {
        return view('admin.payment_methods.form', [
            'paymentMethod' => $payment_method,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, PaymentMethod $payment_method)
    {
        $payment_method->update($this->validateInput($request, $payment_method->id));

        return redirect()->route('admin.payment-methods.index')->with('success', __('messages.payment_method_updated'));
    }

    public function destroy(PaymentMethod $payment_method)
    {
        $payment_method->delete();

        return redirect()->route('admin.payment-methods.index')->with('success', __('messages.payment_method_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateInput(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:64', 'unique:payment_methods,code,' . $id],
            'name' => ['required', 'string', 'max:191'],
            'provider_code' => ['required', 'string', 'max:64'],
            'type' => ['required', 'string', 'max:64'],
            'is_active' => ['nullable', 'boolean'],
            'price_net' => ['required', 'numeric', 'min:0'],
            'price_gross' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'payload_json' => ['nullable', 'array'],
        ]);
    }
}
