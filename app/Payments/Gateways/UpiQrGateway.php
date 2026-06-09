<?php

namespace App\Payments\Gateways;

use App\Models\Registration;
use App\Payments\DTO\PaymentIntent;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Static UPI QR. The QR encodes a standard `upi://pay` intent the student scans
 * with any UPI app (GPay/PhonePe/Paytm). UPI has no merchant-side push callback
 * for a static VPA, so this gateway is MANUAL: the student pays and submits the
 * UTR, then an admin confirms it (Filament "Record Payment"). Swap to a PSP
 * collect/intent API later if true auto-confirmation is needed — only this
 * driver changes.
 */
class UpiQrGateway extends AbstractGateway
{
    public function key(): string
    {
        return 'upi_qr';
    }

    public function label(): string
    {
        return 'UPI QR';
    }

    public function isManual(): bool
    {
        return true;
    }

    public function createIntent(Registration $registration): PaymentIntent
    {
        $amount = $this->amountFor($registration);

        $upi = $this->buildUpiString(
            vpa: (string) $this->config('vpa'),
            payee: (string) $this->config('payee_name', config('app.name')),
            amount: $amount,
            note: "Admission {$registration->academic_year} #{$registration->id}",
            txnRef: 'REG' . $registration->id,
        );

        return new PaymentIntent(
            gateway: $this->key(),
            type: 'qr',
            amount: $amount,
            reference: 'REG' . $registration->id,
            payload: [
                'upi_string'  => $upi,
                'qr_data_uri' => $this->qrDataUri($upi),
                'vpa'         => $this->config('vpa'),
            ],
            instructions: 'Scan the QR with any UPI app, pay, then submit your UTR / reference number.',
        );
    }

    private function buildUpiString(string $vpa, string $payee, float $amount, string $note, string $txnRef): string
    {
        return 'upi://pay?' . http_build_query([
            'pa' => $vpa,
            'pn' => $payee,
            'am' => number_format($amount, 2, '.', ''),
            'cu' => 'INR',
            'tn' => $note,
            'tr' => $txnRef,
        ]);
    }

    private function qrDataUri(string $data): string
    {
        $qr = QrCode::create($data)->setSize(320)->setMargin(12);

        return (new PngWriter())->write($qr)->getDataUri();
    }
}
