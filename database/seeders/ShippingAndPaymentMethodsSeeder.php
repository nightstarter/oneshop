<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingAndPaymentMethodsSeeder extends Seeder
{
    public function run(): void
    {
        $bankTransfer = PaymentMethod::updateOrCreate(
            ['code' => 'bank_transfer'],
            [
                'name' => 'Platba převodem na účet',
                'provider_code' => 'bank_transfer',
                'type' => 'offline',
                'is_active' => true,
                'price_net' => 0,
                'price_gross' => 0,
                'sort_order' => 10,
            ]
        );

        $cashOnDelivery = PaymentMethod::updateOrCreate(
            ['code' => 'cash_on_delivery'],
            [
                'name' => 'Dobírka',
                'provider_code' => 'cash_on_delivery',
                'type' => 'offline',
                'is_active' => true,
                'price_net' => 24.79,
                'price_gross' => 30.00,
                'sort_order' => 20,
            ]
        );

        $comgate = PaymentMethod::updateOrCreate(
            ['code' => 'comgate_redirect'],
            [
                'name' => 'Online platba kartou (Comgate)',
                'provider_code' => 'comgate',
                'type' => 'redirect',
                'is_active' => true,
                'price_net' => 0,
                'price_gross' => 0,
                'sort_order' => 30,
            ]
        );

        $gls = ShippingMethod::updateOrCreate(
            ['code' => 'gls_home'],
            [
                'name' => 'GLS na adresu',
                'provider_code' => 'gls_address',
                'type' => 'address',
                'is_active' => true,
                'price_net' => 81.82,
                'price_gross' => 99.00,
                'sort_order' => 10,
            ]
        );

        $zasilkovnaBox = ShippingMethod::updateOrCreate(
            ['code' => 'zasilkovna_box'],
            [
                'name' => 'Zasilkovna Box',
                'provider_code' => 'zasilkovna_box',
                'type' => 'pickup_point',
                'is_active' => true,
                'price_net' => 57.02,
                'price_gross' => 69.00,
                'sort_order' => 20,
            ]
        );

        $personalPickup = ShippingMethod::updateOrCreate(
            ['code' => 'personal_pickup'],
            [
                'name' => 'Osobní odběr',
                'provider_code' => 'personal_pickup',
                'type' => 'pickup',
                'is_active' => true,
                'price_net' => 0,
                'price_gross' => 0,
                'sort_order' => 30,
            ]
        );

        $gls->paymentMethods()->sync([$bankTransfer->id, $cashOnDelivery->id, $comgate->id]);
        $zasilkovnaBox->paymentMethods()->sync([$bankTransfer->id, $comgate->id]);
        $personalPickup->paymentMethods()->sync([$bankTransfer->id, $cashOnDelivery->id]);
    }
}
