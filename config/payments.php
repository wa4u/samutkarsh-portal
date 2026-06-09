<?php

use App\Payments\Gateways\CashGateway;
use App\Payments\Gateways\RazorpayGateway;
use App\Payments\Gateways\UpiQrGateway;

return [

    /*
    |--------------------------------------------------------------------------
    | Admission fee (fallback)
    |--------------------------------------------------------------------------
    | Used when a Registration has no explicit payment_amount. Override per
    | center/year later via Settings if needed.
    */
    'admission_fee' => env('ADMISSION_FEE', 0),

    /*
    |--------------------------------------------------------------------------
    | Gateway registry  —  "drop a module to enable"
    |--------------------------------------------------------------------------
    | Map a machine key to a class implementing App\Payments\Contracts\PaymentGateway.
    | Adding a gateway = create the class + add ONE line here + set its `enabled`
    | flag below. Nothing in the core needs to change.
    */
    'gateways' => [
        'razorpay' => RazorpayGateway::class,
        'upi_qr'   => UpiQrGateway::class,
        'cash'     => CashGateway::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-gateway configuration & feature flags
    |--------------------------------------------------------------------------
    | Each block is read by the driver via $this->config('name'). The `enabled`
    | flag is the on/off switch; a registered-but-disabled gateway is inert.
    */
    'config' => [

        'razorpay' => [
            'enabled'        => env('RAZORPAY_ENABLED', false),
            'key_id'         => env('RAZORPAY_KEY_ID'),
            'key_secret'     => env('RAZORPAY_KEY_SECRET'),
            'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        ],

        'upi_qr' => [
            'enabled'    => env('UPI_QR_ENABLED', true),
            'vpa'        => env('UPI_VPA', 'samutkarsh@upi'),
            'payee_name' => env('UPI_PAYEE_NAME', 'Samutkarsh IAS Academy'),
        ],

        'cash' => [
            'enabled' => env('CASH_ENABLED', true),
        ],

    ],
];
