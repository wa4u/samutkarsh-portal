<x-mail::message>
# New registration received

A new applicant has registered for **{{ $year }}**.

**Name:** {{ $student->name }}
**Class:** {{ $student->classLabel() ?: '—' }}
**School / College:** {{ $student->school_name ?: '—' }}
**Centre:** {{ $center->name }}
**Phone:** {{ $student->phone }}
**Email:** {{ $student->email ?: '—' }}
@if ($student->guardian_name)
**Guardian:** {{ $student->guardian_name }}
@endif
@if ($student->dob)
**Date of birth:** {{ $student->dob->format('d M Y') }}
@endif

<x-mail::button :url="url('/admin/students')">
Open in admin
</x-mail::button>

Samutkarsh IAS Academy
</x-mail::message>
