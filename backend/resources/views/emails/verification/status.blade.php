<x-mail::message>
# Verification Update

Hello {{ $verification->guide->name }},

The status of your Guide Verification request has been reviewed by an Admin.
Your current status is now: **{{ $verification->status->value ?? $verification->status }}**.

@if($verification->status === 'APPROVED' || ($verification->status->value ?? '') === 'APPROVED')
Congratulations! You can now start accepting bookings on Kasbah Trek!
@else
Unfortunately, your verification was rejected. Please ensure your documents are clear and try submitting again.
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
