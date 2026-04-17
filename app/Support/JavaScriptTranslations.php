<?php

namespace App\Support;

class JavaScriptTranslations
{
    public static function storefront(): array
    {
        return [
            'shop' => [
                'cart' => __('shop.cart'),
                'products' => __('shop.products'),
                'search' => __('shop.search'),
            ],
            'checkout' => [
                'shipping' => __('checkout.shipping'),
                'payment' => __('checkout.payment'),
                'pickup_point' => __('checkout.pickup_point'),
                'pickup_required' => __('checkout.pickup_required'),
                'summary_shipping' => __('checkout.summary_shipping'),
                'summary_payment' => __('checkout.summary_payment'),
                'summary_total_vat' => __('checkout.summary_total_vat'),
                'summary_total' => __('checkout.summary_total'),
            ],
            'buttons' => [
                'submit_order' => __('buttons.submit_order'),
                'search' => __('buttons.search'),
                'cancel_filter' => __('buttons.cancel_filter'),
                'continue_shopping' => __('buttons.continue_shopping'),
                'update' => __('buttons.update'),
                'remove' => __('buttons.remove'),
            ],
            'forms' => [
                'first_name' => __('forms.first_name'),
                'last_name' => __('forms.last_name'),
                'street' => __('forms.street'),
                'city' => __('forms.city'),
                'zip' => __('forms.zip'),
                'country' => __('forms.country'),
                'note' => __('forms.note'),
            ],
            'messages' => [
                'cart_empty' => __('messages.cart_empty'),
                'no_shipping_available' => __('messages.no_shipping_available'),
                'no_products_found' => __('messages.no_products_found'),
                'loading' => __('messages.loading'),
            ],
        ];
    }

    public static function admin(): array
    {
        return [
            'shop' => [
                'dashboard' => __('shop.admin.dashboard'),
                'products' => __('shop.admin.products'),
                'categories' => __('shop.admin.categories'),
                'orders' => __('shop.admin.orders'),
            ],
            'buttons' => [
                'save' => __('buttons.save'),
                'edit' => __('buttons.edit'),
                'delete' => __('buttons.delete'),
                'back' => __('buttons.back'),
            ],
            'messages' => [
                'delete_confirm' => __('messages.delete_confirm'),
            ],
        ];
    }
}