<?php

return [
    'vat_rate' => env('SHOP_VAT_RATE', 21.0),
    'currency' => env('SHOP_CURRENCY', 'CZK'),
    'active_theme' => env('SHOP_ACTIVE_THEME', 'default'),
    'image_watermark_text' => env('SHOP_IMAGE_WATERMARK_TEXT', env('APP_NAME', 'OneShop')),
];