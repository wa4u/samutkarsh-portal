<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Payments\PaymentManager;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(protected PaymentManager $payments) {}

    /**
     * Reached via a signed URL from the Result Gateway ('signed' middleware on
     * the route). On success we drop a session flag authorising the follow-up
     * POST actions (Razorpay order / UPI claim) without re-signing each form.
     */
    public function show(Request $request, Registration $registration)
    {
        $registration->load('student', 'center');

        if ($registration->isAdmitted()) {
            return view('public.checkout-done', ['registration' => $registration]);
        }

        abort_unless($registration->status === 'selected', 403, 'This seat is not open for payment.');

        session(["checkout_ok_{$registration->id}" => true]);

        $gateways = $this->payments->enabled();

        // Pre-build the UPI QR intent if that gateway is on.
        $upiIntent = isset($gateways['upi_qr'])
            ? $gateways['upi_qr']->createIntent($registration)
            : null;

        return view('public.checkout', [
            'registration' => $registration,
            'gateways'     => $gateways,
            'upiIntent'    => $upiIntent,
            'amount'       => $registration->payment_amount ?? config('payments.admission_fee'),
        ]);
    }

    /** Create a Razorpay order for the in-page checkout.js widget. */
    public function razorpay(Request $request, Registration $registration)
    {
        $this->authorizeSession($registration);
        abort_unless(isset($this->payments->enabled()['razorpay']), 404);

        $intent = $this->payments->gateway('razorpay')->createIntent($registration);

        return response()->json($intent->payload);
    }

    /**
     * Student declares the UPI reference (UTR) after paying. We record the claim
     * on the registration; an admin verifies it and confirms via Filament. The
     * seat stays 'selected' until that verified confirmation (webhook-equivalent).
     */
    public function upiClaim(Request $request, Registration $registration)
    {
        $this->authorizeSession($registration);

        $data = $request->validate([
            'utr' => ['required', 'string', 'min:6', 'max:30'],
        ]);

        $registration->forceFill([
            'payment_reference' => $data['utr'],
            'payment_status'    => 'upi_claimed',
            'remarks'           => trim(($registration->remarks ? $registration->remarks . "\n" : '')
                . "UPI claim (UTR {$data['utr']}) submitted via portal, pending verification."),
        ])->save();

        // Don't redirect back to the signed checkout URL (its signature is spent);
        // render the acknowledgement directly.
        return view('public.checkout-claim', [
            'registration' => $registration->load('center'),
            'utr'          => $data['utr'],
        ]);
    }

    protected function authorizeSession(Registration $registration): void
    {
        abort_unless(session("checkout_ok_{$registration->id}") === true, 403);
    }
}
