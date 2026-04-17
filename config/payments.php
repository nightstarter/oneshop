<?php

return [
    'bank_transfer' => [
        'account_number' => env('BANK_TRANSFER_ACCOUNT_NUMBER', '123456789'),
        'bank_code' => env('BANK_TRANSFER_BANK_CODE', '0100'),
        'iban' => env('BANK_TRANSFER_IBAN', ''),
        'bic' => env('BANK_TRANSFER_BIC', ''),
        'message' => env('BANK_TRANSFER_MESSAGE', 'Platba za objednavku'),
    ],

    'comgate' => [
        'merchant' => env('COMGATE_MERCHANT', ''),
        'secret' => env('COMGATE_SECRET', ''),
        'test' => env('COMGATE_TEST', true),
        'create_url' => env('COMGATE_CREATE_URL', 'https://payments.comgate.cz/v1.0/create'),
        'status_url' => env('COMGATE_STATUS_URL', 'https://payments.comgate.cz/v1.0/status'),
        'timeout_seconds' => env('COMGATE_TIMEOUT_SECONDS', 15),
         'verify_ssl' => env('COMGATE_VERIFY_SSL', true),
    ],
];
