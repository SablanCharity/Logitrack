<!DOCTYPE html>
<html lang="en">
<body>
    <p>Hello {{ $submission->submitter->first_name }},</p>
    <p>The current status of your capstone submission, <strong>{{ $submission->title }}</strong>, is <strong>{{ ucfirst($submission->status) }}</strong>.</p>
    @if($submission->status === 'returned' && $submission->return_reason)
        <p>Return reason: {{ $submission->return_reason }}</p>
        <p>Please update your submission and resubmit it for review.</p>
    @elseif($submission->status === 'approved')
        <p>Your capstone is now available in the LogiTrack repository.</p>
    @else
        <p>Your submission has been received and is awaiting review.</p>
    @endif
    <p>LogiTrack</p>
</body>
</html>
