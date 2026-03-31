<x-mail::message>
# Trip Report Status Update

Hello {{ $report->traveler->name }},

The status of the Trip Report you submitted has been updated by an Admin.
Your report's current status is: **{{ $report->status->value ?? $report->status }}**.

@if($report->status === 'APPROVED' || ($report->status->value ?? '') === 'APPROVED')
Your report has been approved. The problematic tour/guide is under review. Thank you for keeping Kasbah Trek safe!
@elseif($report->status === 'REJECTED' || ($report->status->value ?? '') === 'REJECTED')
Unfortunately, your report was rejected. The Admin determined there was not enough evidence or the issue did not violate our policies.
@elseif($report->status === 'RESOLVED' || ($report->status->value ?? '') === 'RESOLVED')
Your report has been marked as resolved! The issue you raised has been successfully handled.
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
