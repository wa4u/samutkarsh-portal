<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\InquiryReceived;
use App\Models\Center;
use App\Models\Inquiry;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('public.contact', [
            'centers' => Center::where('is_active', true)
                ->orderByDesc('is_head_office')
                ->orderBy('sort')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $thanks = 'Thank you — we have received your message and will contact you soon.';

        // Honeypot: the hidden "website" field is invisible to humans; if it's
        // filled, it's a bot. Silently accept (no save, no email) so the bot
        // gets a success response and doesn't retry.
        if (filled($request->input('website'))) {
            return back()->with('status', $thanks);
        }

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'phone'     => ['required', 'regex:/^[6-9]\d{9}$/'],
            'email'     => ['nullable', 'email', 'max:255'],
            'center_id' => ['nullable', 'exists:centers,id'],
            'subject'   => ['nullable', 'string', 'max:255'],
            'message'   => ['required', 'string', 'max:5000'],
        ], [
            'phone.regex' => 'Enter a valid 10-digit mobile number.',
        ]);

        $inquiry = Inquiry::create($data + ['status' => 'new']);

        $this->notify($inquiry);

        return back()->with('status', $thanks);
    }

    /**
     * Email the inquiry to Head Office (and the selected centre, if any). Mail
     * failures must never break the submission, so the block is guarded/logged.
     */
    protected function notify(Inquiry $inquiry): void
    {
        try {
            $ho = Setting::get('notify.registration_email') ?: Setting::get('contact.email');
            $recipients = array_filter([$ho]);

            if ($inquiry->center_id) {
                $center = Center::find($inquiry->center_id);
                if ($center && $center->contact_email && $center->contact_email !== $ho) {
                    $recipients[] = $center->contact_email;
                }
            }

            foreach (array_unique($recipients) as $recipient) {
                Mail::to($recipient)->send(new InquiryReceived($inquiry));
            }
        } catch (\Throwable $e) {
            Log::warning('Inquiry email failed: ' . $e->getMessage());
        }
    }
}
