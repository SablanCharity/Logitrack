<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $announcement->title }}</title>
</head>
<body style="margin: 0; padding: 24px; background: #f3f4f6; color: #1f2937; font-family: Arial, sans-serif;">
    <main style="max-width: 640px; margin: 0 auto; padding: 28px; background: #ffffff; border-radius: 12px;">
        <p style="margin-top: 0; color: #64748b; font-size: 13px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;">LogiTrack Announcement</p>
        <h1 style="margin: 0 0 20px; font-size: 24px; line-height: 1.3;">{{ $announcement->title }}</h1>
        <div style="white-space: pre-line; line-height: 1.65;">{{ $announcement->message }}</div>
        @if($announcement->expires_at)
            <p style="margin: 24px 0 0; color: #64748b; font-size: 13px;">This announcement is available until {{ $announcement->expires_at->format('F j, Y g:i A') }}.</p>
        @endif
        <p style="margin: 28px 0 0;">
            <a href="{{ route('dashboard') }}" style="display: inline-block; padding: 12px 18px; border-radius: 8px; background: #123b68; color: #ffffff; font-weight: 700; text-decoration: none;">Open LogiTrack</a>
        </p>
    </main>
</body>
</html>
