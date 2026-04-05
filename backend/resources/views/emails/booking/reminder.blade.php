<x-mail::message>
# Your tour is tomorrow

Hello {{ $booking->traveler->fullname }},

This is a friendly reminder that your booking for **{{ $booking->tour->title }}** is scheduled for **{{ $booking->date->format('l, F j, Y') }}**.

**Location:** {{ $booking->tour->location }}

Please arrive on time and contact your guide if you need anything before the trip.

<x-mail::button :url="config('app.url')">
Open Kasbah Trek
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
