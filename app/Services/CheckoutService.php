<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class CheckoutService
{
    public function __construct(
        private readonly ShippingService $shippingService,
        private readonly PaymentService $paymentService,
        private readonly OrderService $orderService,
    ) {}

    /**
     * @param  array<string, mixed>  $checkoutInput
     * @param  array<string, mixed>|null  $billingAddress
     * @param  array<string, mixed>|null  $shippingAddress
     * @return array{order: Order, is_redirect: bool, redirect_url: string|null}
     */
    public function placeOrder(
        Collection $cartItems,
        array $checkoutInput,
        ?User $user = null,
        ?array $billingAddress = null,
        ?array $shippingAddress = null,
        ?string $note = null,
    ): array {
        $customer = $user?->customer;

        $shippingMethod = $this->shippingService->resolveSelectedMethod(
            (int) $checkoutInput['shipping_method_id'],
            $cartItems,
            $customer,
        );

        $paymentMethod = $this->paymentService->resolveSelectedMethod(
            (int) $checkoutInput['payment_method_id'],
            $shippingMethod,
            $cartItems,
            $customer,
        );

        $shippingSelection = $this->shippingService->validateSelection($shippingMethod, $checkoutInput);
        $paymentSelection = $this->paymentService->validateSelection($paymentMethod, $checkoutInput);

        $orderItems = $cartItems->map(fn ($item) => [
            'product' => $item->product,
            'quantity' => $item->quantity,
        ])->all();

        $snapshot = [
            'shipping_method_id' => $shippingMethod->id,
            'shipping_code' => $shippingMethod->code,
            'shipping_name' => $shippingMethod->name,
            'shipping_price_net' => (float) $shippingMethod->price_net,
            'shipping_price_gross' => (float) $shippingMethod->price_gross,
            'payment_method_id' => $paymentMethod->id,
            'payment_code' => $paymentMethod->code,
            'payment_name' => $paymentMethod->name,
            'payment_price_net' => (float) $paymentMethod->price_net,
            'payment_price_gross' => (float) $paymentMethod->price_gross,
            'pickup_point_id' => $shippingSelection['pickup_point_id'] ?? null,
            'pickup_point_name' => $shippingSelection['pickup_point_name'] ?? null,
            'pickup_point_address' => $shippingSelection['pickup_point_address'] ?? null,
            'shipping_payload_json' => $shippingSelection['shipping_payload_json'] ?? null,
            'payment_payload_json' => $paymentSelection['payment_payload_json'] ?? null,
        ];

        $order = $this->orderService->create(
            $orderItems,
            $customer,
            $user,
            $billingAddress,
            $shippingAddress,
            $note,
            $snapshot,
        );

        $paymentInit = $this->paymentService->initiate($order, $paymentMethod, [
            'checkout_input' => $checkoutInput,
        ]);

        return [
            'order' => $order,
            'is_redirect' => $paymentInit['is_redirect'],
            'redirect_url' => $paymentInit['redirect_url'],
        ];
    }
}
