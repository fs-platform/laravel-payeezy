<?php

return [
    'environment'          => env('PAYEEZY_ENVIRONMENT','sandbox'),

    'channel'              => env('PAYEEZY_CHANNEL',),

    'eu_warehouse'         => env('PAYEEZY_EU_WAREHOUSE',[]),

    'fill_able_currencies' => env('PAYEEZY_FILL_ABLE_CURRENCIES',[]),

    'model' => [
        'log_model'    => env('PAYEEZY_LOG_MODEL',''),
        'token_model'  => env('PAYEEZY_TOKEN_MODEL',''),
        'status_model' => env('PAYEEZY_STATUS_MODEL',''),
    ],

    '3ds_status' => env('3DS_STATUS',true),

    'sandbox' => [
        'apiKey'          => env('PAYEEZY_SANDBOX_APIKEY',''),

        'paymentSecret'   => env('PAYEEZY_SANDBOX_PAYMENT_SECRET',''),

        'apiSecret'       => env('PAYEEZY_SANDBOX_API_SECRET',''),

        'merchantToken'   => env('PAYEEZY_SANDBOX_MERCHANT_TOKEN',''),

        'taToken'         => env('PAYEEZY_SANDBOX_TA_TOKEN',''),

        'tokenUrl'        => env('PAYEEZY_SANDBOX_TOKEN_URL',''),

        'url'             => env('PAYEEZY_SANDBOX_URL',''),

        'integration_url' => env('PAYEEZY_SANDBOX_INTEGRATION_URL',''),

        'jwt_apiKey'      => env('PAYEEZY_SANDBOX_JWT_APIKEY',''),

        'jwt_apiId'       => env('PAYEEZY_SANDBOX_JWT_API_ID',''),

        'jwt_unitId'      => env('PAYEEZY_SANDBOX_JWT_UNIT_ID',''),
    ],

    'production' => [
        'apiKey'          => env('PAYEEZY_PRODUCTION_APIKEY',''),

        'paymentSecret'   => env('PAYEEZY_PRODUCTION_PAYMENT_SECRET',''),

        'apiSecret'       => env('PAYEEZY_PRODUCTION_API_SECRET',''),

        'merchantToken'   => env('PAYEEZY_PRODUCTION_MERCHANT_TOKEN',''),

        'taToken'         => env('PAYEEZY_PRODUCTION_TA_TOKEN',''),

        'tokenUrl'        => env('PAYEEZY_PRODUCTION_TOKEN_URL',''),

        'url'             => env('PAYEEZY_PRODUCTION_URL',''),

        'integration_url' => env('PAYEEZY_PRODUCTION_INTEGRATION_URL',''),

        'jwt_apiKey'      => env('PAYEEZY_PRODUCTION_JWT_APIKEY',''),

        'jwt_apiId'       => env('PAYEEZY_PRODUCTION_JWT_API_ID',''),

        'jwt_unitId'      => env('PAYEEZY_PRODUCTION_JWT_UNIT_ID',''),
    ]
];
