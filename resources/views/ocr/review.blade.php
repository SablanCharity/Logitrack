<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Extracted Metadata · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>

<body class="lt-body">

    <header class="lt-nav lt-nav--landing">
        <div class="lt-nav__inner">
            <a href="{{ route('dashboard') }}" class="lt-brand">
                <span class="lt-brand__mark">L</span>
                LogiTrack
            </a>
            <nav class="lt-nav__links lt-nav__links--app lt-admin-nav" aria-label="Admin">
                <a href="{{ route('dashboard') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Dashboard" aria-label="Dashboard"><span class="material-symbols-outlined">dashboard</span><span class="lt-admin-nav-label">Dashboard</span></a>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('repository.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Repository" aria-label="Repository"><span class="material-symbols-outlined">menu_book</span><span class="lt-admin-nav-label">Repository</span></a>
                    <a href="{{ route('admin.capstones') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Capstone Review" aria-label="Capstone Review"><span class="material-symbols-outlined">fact_check</span><span class="lt-admin-nav-label">Capstone Review</span></a>
                    <a href="{{ route('ocr.upload') }}" class="lt-nav__link lt-admin-nav-link is-active" data-tooltip="Upload Archived" aria-label="Upload Archived"><span class="material-symbols-outlined">upload_file</span><span class="lt-admin-nav-label">Upload Archived</span></a>
                    <a href="{{ route('admin.accounts.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Staff Accounts" aria-label="Staff Accounts"><span class="material-symbols-outlined">manage_accounts</span><span class="lt-admin-nav-label">Staff Accounts</span></a>
                    <a href="{{ route('admin.students.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Student Accounts" aria-label="Student Accounts"><span class="material-symbols-outlined">school</span><span class="lt-admin-nav-label">Student Accounts</span></a>
                @endif
            </nav>
            <div class="lt-nav__actions">
                <a href="{{ route('profile.edit') }}" class="lt-user-chip lt-user-chip--link" aria-label="Edit profile"><span class="lt-avatar">{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}</span>{{ auth()->user()->first_name }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="lt-btn lt-btn--outline-light lt-btn--sm">Log Out</button>
                </form>
            </div>
        </div>
    </header>

    <main class="lt-container lt-animate">
        <a href="{{ route('ocr.upload') }}" class="lt-back">← Upload Another Document</a>

        <div class="lt-page-head">
            <div class="lt-page-head__row">
                <div>
                    <span class="lt-eyebrow">OCR Verification</span>
                    <h1>Review Extracted Metadata</h1>
                </div>
                <span class="lt-badge lt-badge--verified">Status: {{ ucfirst($archivedCapstone->status) }}</span>
            </div>
            <p class="lt-page-head__sub">Verify and edit the fields extracted from the scanned document before final approval.</p>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status">
                <span class="lt-alert__icon">✓</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <div class="lt-review">
            <!-- Left Column: Form to Edit Extracted Metadata -->
            <div class="lt-review__stack">
                <section class="lt-card">
                    <h2 class="lt-card__title">Edit Metadata</h2>

                    <form method="POST" action="{{ route('ocr.update', $archivedCapstone->id) }}" class="lt-form">
                        @csrf
                        @method('PUT')

                        <div class="lt-field">
                            <label for="title" class="lt-label">Title</label>
                            <input type="text" id="title" name="title" class="lt-input" value="{{ old('title', $archivedCapstone->title) }}" required>
                        </div>

                        <div class="lt-field">
                            <label for="abstract" class="lt-label">Abstract</label>
                            <textarea id="abstract" name="abstract" class="lt-textarea" rows="5">{{ old('abstract', $archivedCapstone->abstract) }}</textarea>
                        </div>

                        <div class="lt-field">
                            <label for="authors" class="lt-label">Authors (comma separated)</label>
                            <input type="text" id="authors" name="authors" class="lt-input" value="{{ old('authors', is_array($archivedCapstone->authors) ? implode(', ', $archivedCapstone->authors) : $archivedCapstone->authors) }}">
                        </div>

                        <div class="lt-field">
                            <label for="adviser" class="lt-label">Adviser</label>
                            <input type="text" id="adviser" name="adviser" class="lt-input" value="{{ old('adviser', $archivedCapstone->adviser) }}">
                        </div>

                        <div class="lt-field">
                            <label for="program" class="lt-label">Program</label>
                            <input type="text" id="program" name="program" class="lt-input" value="{{ old('program', $archivedCapstone->program) }}">
                        </div>

                        <div class="lt-field">
                            <label for="publication_date" class="lt-label">Publication Date / Year</label>
                            <input type="text" id="publication_date" name="publication_date" class="lt-input" value="{{ old('publication_date', $archivedCapstone->publication_date) }}">
                        </div>

                        <button type="submit" class="lt-btn lt-btn--primary">Save Corrections</button>
                    </form>
                </section>
            </div>

            <!-- Right Column: Raw OCR Text & Final Approval Action -->
            <aside class="lt-panel-sticky">
                <section class="lt-card lt-review-forms">
                    <h2 class="lt-card__title">Raw OCR Extraction</h2>
                    <p class="lt-card__desc">Reference text scanned directly from the document file.</p>

                    <div style="background: #f8fafc; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 12px; max-height: 250px; overflow-y: auto; margin-bottom: 20px; white-space: pre-wrap;">
                        {{ $archivedCapstone->ocr_text }}
                    </div>

                    @if(auth()->user()->role === 'admin')
                        <form method="POST" action="{{ route('ocr.approve', $archivedCapstone->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="lt-btn lt-btn--green" style="width: 100%;">
                                Approve &amp; Push to Repository
                            </button>
                        </form>
                    @endif
                </section>
            </aside>
        </div>
    </main>

</body>
</html>