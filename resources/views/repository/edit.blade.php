<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Capstone Metadata &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    @include('components.site-navigation')

    <main class="lt-container lt-animate" style="max-width: 760px;">
        <a href="{{ route('repository.show', ['capstoneId' => $capstone->id, 'type' => $type]) }}" class="lt-back">&larr; Back to Capstone</a>
        <div class="lt-page-head">
            <span class="lt-eyebrow">Admin &middot; Repository</span>
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
                @endif
                <button type="submit" class="lt-btn lt-btn--primary">Save Metadata</button>
            </form>
        </section>
    </main>
</body>
</html>
