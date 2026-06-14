<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * Capture the current (env/config-resolved) payment values into editable
 * Settings so admins can change the UPI ID, payee, fee, and method toggles
 * without touching .env. firstOrCreate keeps any real env values intact and
 * never overwrites a value already set. Razorpay key/secret stay in .env only.
 */
return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'payment.admission_fee'     => [(string) config('payments.admission_fee', 0), 'text'],
            'payment.upi_qr.vpa'        => [(string) config('payments.config.upi_qr.vpa', ''), 'text'],
            'payment.upi_qr.payee_name' => [(string) config('payments.config.upi_qr.payee_name', ''), 'text'],
            'payment.upi_qr.enabled'    => [config('payments.config.upi_qr.enabled') ? '1' : '0', 'boolean'],
            'payment.cash.enabled'      => [config('payments.config.cash.enabled') ? '1' : '0', 'boolean'],
            'payment.razorpay.enabled'  => [config('payments.config.razorpay.enabled') ? '1' : '0', 'boolean'],
        ];

        foreach ($defaults as $key => [$value, $type]) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value, 'type' => $type, 'group' => 'payment']);
        }
    }

    public function down(): void
    {
        Setting::where('group', 'payment')->delete();
    }
};
