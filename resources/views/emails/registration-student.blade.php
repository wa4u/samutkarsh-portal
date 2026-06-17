<x-mail::message>
# Thank you for registering!

Dear {{ $student->name }},

We've received your registration for the **{{ $year }}** admission cycle at **{{ $center->name }}**.

Our team will review your application and get in touch. You can check your
admission status anytime using the Result Gateway on our website.

<x-mail::button :url="route('public.result.form')">
Check admission status
</x-mail::button>

Warm regards,
**Samutkarsh IAS Academy**
</x-mail::message>
