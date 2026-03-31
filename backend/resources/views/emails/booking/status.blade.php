<x-mail::message>
# Booking Status Update

Hello {{ $booking->traveler->name }},

The status of your booking for the tour **{{ $booking->tour->title }}** has been updated to: **{{ $booking->status }}**.

<x-mail::button :url="''">
View Bookings
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
