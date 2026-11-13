<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Announcement Management | LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    @include('components.site-navigation')

    <main class="lt-container lt-animate lt-announcement-page">
        <div class="lt-page-head lt-page-head__row">
            <div>
                <span class="lt-eyebrow">Administration</span>
                <h1>Announcement Management</h1>
                <p class="lt-page-head__sub">Create, update, and manage announcements shown on LogiTrack dashboards.</p>
            </div>
            <a href="{{ route('admin.announcements.create') }}" class="lt-btn lt-btn--primary">
                <span aria-hidden="true">＋</span> Add Announcement
            </a>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status" data-autohide="6000">
                <span class="lt-alert__icon">&#10003;</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="lt-alert lt-alert--danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="lt-announcement-management__summary">
            <span class="lt-eyebrow">Published updates</span>
            <strong>{{ method_exists($announcements, 'total') ? number_format($announcements->total()) : number_format($announcements->count()) }}</strong>
            <span class="lt-form-hint">Total announcements</span>
        </div>

        @forelse($announcements as $announcement)
            <article class="lt-card lt-announcement-manage-card">
                <div class="lt-announcement-manage-card__main">
                    <div class="lt-announcement-manage-card__heading">
                        <div>
                            <span class="lt-badge lt-badge--approved">Announcement</span>
                            <h2 class="lt-card__title">{{ $announcement->title }}</h2>
                        </div>
                        <time class="lt-announcement-date" datetime="{{ $announcement->published_at?->toDateString() }}">
                            <span class="material-symbols-outlined" aria-hidden="true">calendar_month</span>
                            {{ $announcement->published_at?->format('M d, Y') ?? 'Not published' }}
                        </time>
                    </div>
                    <p class="lt-announcement-manage-card__message">{{ $announcement->message }}</p>
                    <div class="lt-announcement-manage-card__footer">
                        <span class="lt-announcement-audience">Audience: <strong>{{ match($announcement->audience) { 'students' => 'Students Only', 'instructors' => 'Instructors Only', default => 'Everyone' } }}</strong></span>
                        @if($announcement->expires_at)
                            <span class="lt-announcement-expiry">Expires {{ $announcement->expires_at->format('M d, Y') }}</span>
                        @endif
                    </div>
                </div>
                <div class="lt-announcement-manage-card__actions">
                    <a href="{{ route('admin.announcements.edit', $announcement) }}" class="lt-btn lt-btn--outline lt-btn--sm">Edit</a>
                    <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" data-submit-guard onsubmit="return confirm('Delete this announcement?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="lt-btn lt-btn--danger lt-btn--sm">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="lt-card lt-announcement-empty">
                <span class="material-symbols-outlined" aria-hidden="true">campaign</span>
                <h2 class="lt-card__title">No announcements yet</h2>
                <p class="lt-card__desc">Create your first announcement to share an update with students and instructors.</p>
                <a href="{{ route('admin.announcements.create') }}" class="lt-btn lt-btn--primary">Create Announcement</a>
            </div>
        @endforelse

        @if(method_exists($announcements, 'links'))
            <div class="lt-pager">{{ $announcements->links() }}</div>
        @endif
    </main>
</body>
</html>
