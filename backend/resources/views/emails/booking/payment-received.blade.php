<x-mail::message>
# Payment received

Hello {{ $booking->traveler->fullname }},

We have received your payment for **{{ $booking->tour?->title ?? 'your tour' }}**.

**Amount:** {{ number_format((float) $booking->total_price, 2) }} {{ strtoupper(config('stripe.currency')) }}

**Booking date:** {{ $booking->date->format('l, F j, Y') }}

@if($booking->paid_at)
**Paid at:** {{ $booking->paid_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
@endif

Your booking remains subject to the usual confirmation flow. If you have questions, reply to this email or use the app.

<x-mail::button :url="config('app.url')">
Open Kasbah Trek
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
