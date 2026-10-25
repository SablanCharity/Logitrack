<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Capstone Metadata · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    <header class="lt-nav lt-nav--landing">
        <div class="lt-nav__inner">
            <a href="{{ route('repository.index') }}" class="lt-brand">
                <span class="lt-brand__mark">L</span>
                LogiTrack
            </a>
            <nav class="lt-nav__links lt-nav__links--app lt-admin-nav" aria-label="Admin">
                <a href="{{ route('dashboard') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Dashboard" aria-label="Dashboard"><span class="material-symbols-outlined">dashboard</span><span class="lt-admin-nav-label">Dashboard</span></a>
                <a href="{{ route('repository.index') }}" class="lt-nav__link lt-admin-nav-link is-active" data-tooltip="Repository" aria-label="Repository"><span class="material-symbols-outlined">menu_book</span><span class="lt-admin-nav-label">Repository</span></a>
                <a href="{{ route('admin.capstones') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Capstone Review" aria-label="Capstone Review"><span class="material-symbols-outlined">fact_check</span><span class="lt-admin-nav-label">Capstone Review</span></a>
                <a href="{{ route('ocr.upload') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Upload Archived" aria-label="Upload Archived"><span class="material-symbols-outlined">upload_file</span><span class="lt-admin-nav-label">Upload Archived</span></a>
                <a href="{{ route('admin.accounts.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Staff Accounts" aria-label="Staff Accounts"><span class="material-symbols-outlined">manage_accounts</span><span class="lt-admin-nav-label">Staff Accounts</span></a>
                <a href="{{ route('admin.students.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Student Accounts" aria-label="Student Accounts"><span class="material-symbols-outlined">school</span><span class="lt-admin-nav-label">Student Accounts</span></a>
            </nav>
            <div class="lt-nav__actions">
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="lt-btn lt-btn--outline-light lt-btn--sm">Log Out</button></form>
            </div>
        </div>
    </header>

    <main class="lt-container lt-animate" style="max-width: 760px;">
        <a href="{{ route('repository.show', ['capstoneId' => $capstone->id, 'type' => $type]) }}" class="lt-back">⟵ Back to Capstone</a>
        <div class="lt-page-head">
            <span class="lt-eyebrow">Admin · Repository</span>
            <h1>Edit Capstone Metadata</h1>
            <p class="lt-page-head__sub">Changes apply directly to this approved repository record.</p>
        </div>

        @if($errors->any())
            <div class="lt-alert lt-alert--error" role="alert"><span class="lt-alert__icon">!</span><div>{{ $errors->first() }}</div></div>
        @endif

        <section class="lt-card">
            <form method="POST" action="{{ route('admin.repository.update', $capstone->id) }}" class="lt-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="lt-field">
                    <label for="title" class="lt-label">Title</label>
                    <input id="title" name="title" class="lt-input" value="{{ old('title', $capstone->title) }}" required>
                </div>
                <div class="lt-field">
                    <label for="abstract" class="lt-label">Abstract</label>
                    <textarea id="abstract" name="abstract" class="lt-textarea" rows="7">{{ old('abstract', $capstone->abstract) }}</textarea>
                </div>
                <div class="lt-field">
                    <label for="authors" class="lt-label">Authors (comma separated)</label>
                    <input id="authors" name="authors" class="lt-input" value="{{ old('authors', $type === 'old' && is_array($capstone->authors ?? null) ? implode(', ', $capstone->authors) : '') }}">
                </div>
                <div class="lt-form__grid">
                    <div class="lt-field">
                        <label for="program" class="lt-label">Program</label>
                        <input id="program" name="program" class="lt-input" value="{{ old('program', $capstone->program) }}">
                    </div>
                    <div class="lt-field">
                        <label for="publication_date" class="lt-label">Publication date / year</label>
                        <input id="publication_date" name="publication_date" class="lt-input" value="{{ old('publication_date', $type === 'old' ? $capstone->publication_date : optional($capstone->publication_date)->format('Y-m-d')) }}">
                    </div>
                </div>
                @if($type === 'old')
                    <div class="lt-form__grid">
                        <div class="lt-field">
                            <label for="adviser" class="lt-label">Adviser</label>
                            <input id="adviser" name="adviser" class="lt-input" value="{{ old('adviser', $capstone->adviser) }}">
                        </div>
                        <div class="lt-field">
                            <label for="institution" class="lt-label">Institution</label>
                            <input id="institution" name="institution" class="lt-input" value="{{ old('institution', $capstone->institution) }}">
                        </div>
                    </div>
                    <div class="lt-field">
                        <label for="department" class="lt-label">Department</label>
                        <input id="department" name="department" class="lt-input" value="{{ old('department', $capstone->department) }}">
                    </div>
                @endif
                <button type="submit" class="lt-btn lt-btn--primary">Save Metadata</button>
            </form>
        </section>
    </main>
</body>
</html>
