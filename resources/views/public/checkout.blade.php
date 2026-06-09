@extends('layouts.public')

@section('title', 'Confirm your seat')

@section('content')
    <div class="mx-auto max-w-lg px-4 py-12">
        <h1 class="text-2xl font-bold text-slate-900">Confirm your seat</h1>
        <p class="mt-1 text-sm text-slate-600">
            {{ $registration->student->name }} · {{ $registration->center->name }} · {{ $registration->academic_year }}
        </p>

        <div class="mt-4 rounded-xl bg-white p-6 ring-1 ring-slate-200">
            <div class="flex items-baseline justify-between">
                <span class="text-slate-600">Admission fee</span>
                <span class="text-2xl font-bold text-slate-900">₹{{ number_format((float) $amount, 2) }}</span>
            </div>
        </div>

        @if (session('status'))
            <div class="mt-4 rounded-lg bg-green-50 p-4 text-sm text-green-700 ring-1 ring-green-200">{{ session('status') }}</div>
        @endif

        @if (count($gateways) === 0)
            <div class="mt-6 rounded-lg bg-amber-50 p-4 text-sm text-amber-800 ring-1 ring-amber-200">
                Online payment is not configured yet. Please contact your center to pay.
            </div>
        @endif

        <div class="mt-6 space-y-4">
            {{-- Razorpay (online) --}}
            @isset($gateways['razorpay'])
                <div class="rounded-xl bg-white p-6 ring-1 ring-slate-200">
                    <h2 class="font-semibold text-slate-900">Pay online</h2>
                    <p class="mt-1 text-sm text-slate-600">Cards, netbanking, wallets & UPI via Razorpay.</p>
                    <button id="razorpay-btn"
                            class="mt-4 w-full rounded-lg bg-indigo-600 px-4 py-3 font-semibold text-white hover:bg-indigo-700">
                        Pay ₹{{ number_format((float) $amount, 2) }} online
                    </button>
                </div>
            @endisset

            {{-- UPI QR (manual confirm) --}}
            @isset($gateways['upi_qr'])
                <div class="rounded-xl bg-white p-6 ring-1 ring-slate-200">
                    <h2 class="font-semibold text-slate-900">Pay via UPI</h2>
                    <p class="mt-1 text-sm text-slate-600">Scan with any UPI app, pay, then enter your UTR / reference below.</p>
                    <div class="mt-4 flex justify-center">
                        <img src="{{ $upiIntent->payload['qr_data_uri'] }}" alt="UPI QR code" class="h-56 w-56 rounded-lg ring-1 ring-slate-200">
                    </div>
                    <p class="mt-2 text-center text-xs text-slate-500">UPI ID: {{ $upiIntent->payload['vpa'] }}</p>

                    <form method="POST" action="{{ route('public.checkout.upi', $registration) }}" class="mt-4 flex gap-2">
                        @csrf
                        <input type="text" name="utr" required placeholder="12-digit UPI reference / UTR"
                               class="flex-1 rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 font-semibold text-white hover:bg-slate-900">Submit</button>
                    </form>
                </div>
            @endisset

            {{-- Cash (at center) --}}
            @isset($gateways['cash'])
                <div class="rounded-xl bg-white p-6 ring-1 ring-slate-200">
                    <h2 class="font-semibold text-slate-900">Pay cash at the center</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Visit {{ $registration->center->name }} and pay at the office. Staff will confirm your seat instantly.
                    </p>
                </div>
            @endisset
        </div>
    </div>
@endsection

@isset($gateways['razorpay'])
    @push('scripts')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            document.getElementById('razorpay-btn')?.addEventListener('click', async () => {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const res = await fetch("{{ route('public.checkout.razorpay', $registration) }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                });
                const data = await res.json();
                new Razorpay({
                    key: data.key,
                    order_id: data.order_id,
                    amount: data.amount,
                    currency: data.currency,
                    name: data.name,
                    handler: () => { window.location = "{{ route('public.result.form') }}"; },
                }).open();
            });
        </script>
    @endpush
@endisset
