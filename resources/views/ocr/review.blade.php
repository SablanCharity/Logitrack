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
            <div class="lt-nav__actions">
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
                <span class="lt-badge lt-badge--verified">Status: {{ ucfirst($oldCapstone->status) }}</span>
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

                    <form method="POST" action="{{ route('ocr.update', $oldCapstone->id) }}" class="lt-form">
                        @csrf
                        @method('PUT')

                        <div class="lt-field">
                            <label for="title" class="lt-label">Title</label>
                            <input type="text" id="title" name="title" class="lt-input" value="{{ old('title', $oldCapstone->title) }}" required>
                        </div>

                        <div class="lt-field">
                            <label for="abstract" class="lt-label">Abstract</label>
                            <textarea id="abstract" name="abstract" class="lt-textarea" rows="5">{{ old('abstract', $oldCapstone->abstract) }}</textarea>
                        </div>

                        <div class="lt-field">
                            <label for="authors" class="lt-label">Authors (comma separated)</label>
                            <input type="text" id="authors" name="authors" class="lt-input" value="{{ old('authors', is_array($oldCapstone->authors) ? implode(', ', $oldCapstone->authors) : $oldCapstone->authors) }}">
                        </div>

                        <div class="lt-field">
                            <label for="adviser" class="lt-label">Adviser</label>
                            <input type="text" id="adviser" name="adviser" class="lt-input" value="{{ old('adviser', $oldCapstone->adviser) }}">
                        </div>

                        <div class="lt-field">
                            <label for="program" class="lt-label">Program</label>
                            <input type="text" id="program" name="program" class="lt-input" value="{{ old('program', $oldCapstone->program) }}">
                        </div>

                        <div class="lt-field">
                            <label for="publication_date" class="lt-label">Publication Date / Year</label>
                            <input type="text" id="publication_date" name="publication_date" class="lt-input" value="{{ old('publication_date', $oldCapstone->publication_date) }}">
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
                        {{ $oldCapstone->ocr_text }}
                    </div>

                    @if(auth()->user()->role === 'admin')
                        <form method="POST" action="{{ route('ocr.approve', $oldCapstone->id) }}">
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