@php($pageTitle = 'Edit Announcement')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }} | LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    @include('components.site-navigation')

    <main class="lt-container lt-animate lt-announcement-page">
        <div class="lt-page-head">
            <span class="lt-eyebrow">Administration</span>
            <h1>{{ $pageTitle }}</h1>
            <p class="lt-page-head__sub">Publish an important update for LogiTrack students and instructors.</p>
        </div>

        @if($errors->any())
            <div class="lt-alert lt-alert--danger" role="alert">
                <strong>Please review the following:</strong>
                <ul class="lt-announcement-errors">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="lt-card lt-announcement-form" data-submit-guard>
            @csrf
            @method('PUT')
            <div class="lt-announcement-form__field">
                <label for="title" class="lt-label">Announcement Title</label>
                <input id="title" type="text" name="title" value="{{ old('title', $announcement->title) }}" required maxlength="255" class="lt-input" placeholder="Enter announcement title">
                @error('title')<p class="lt-field-error">{{ $message }}</p>@enderror
            </div>

            <div class="lt-announcement-form__field">
                <label for="message" class="lt-label">Announcement Message</label>
                <textarea id="message" name="message" rows="7" required maxlength="10000" class="lt-input lt-announcement-textarea" placeholder="Write your announcement here...">{{ old('message', $announcement->message) }}</textarea>
                @error('message')<p class="lt-field-error">{{ $message }}</p>@enderror
            </div>

            <div class="lt-announcement-form__grid">
                <div class="lt-announcement-form__field">
                    <label for="audience" class="lt-label">Audience</label>
                    <select id="audience" name="audience" required class="lt-input">
                        <option value="all" @selected(old('audience', $announcement->audience) === 'all')>Everyone</option>
                        <option value="students" @selected(old('audience', $announcement->audience) === 'students')>Students Only</option>
                        <option value="instructors" @selected(old('audience', $announcement->audience) === 'instructors')>Instructors Only</option>
                    </select>
                    @error('audience')<p class="lt-field-error">{{ $message }}</p>@enderror
                </div>
                <div class="lt-announcement-form__field">
                    <label for="expires_at" class="lt-label">Expiration Date <span class="lt-announcement-optional">(Optional)</span></label>
                    <input id="expires_at" type="datetime-local" name="expires_at" min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d\TH:i')) }}" class="lt-input">
                    <p class="lt-form-hint">Leave blank if this announcement should not expire.</p>
                    @error('expires_at')<p class="lt-field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="lt-announcement-form__actions">
                <a href="{{ route('admin.announcements.index') }}" class="lt-btn lt-btn--outline">Cancel</a>
                <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Saving...">Save Changes</button>
            </div>
        </form>
    </main>
</body>
</html>
