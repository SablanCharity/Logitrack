<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit Capstone &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    @include('components.site-navigation')

    <main class="lt-container lt-container--narrow lt-animate">
        <div class="lt-page-head">
            <span class="lt-eyebrow">Completed Capstone Requirements</span>
            <h1>Submit your capstone</h1>
            <p class="lt-page-head__sub">Upload every required file and complete all metadata fields for review.</p>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="lt-alert lt-alert--error" role="alert">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($submission)
            <div class="lt-alert lt-alert--success" role="status">
                Your latest submission is currently <strong>{{ ucfirst($submission->status) }}</strong>.
            </div>
        @endif

        <form method="POST" action="{{ route('student.submissions.store') }}" enctype="multipart/form-data" class="lt-form" data-submit-guard>
            @csrf
            <section class="lt-card">
                <h2 class="lt-card__title">Submission details</h2>
                <div class="lt-form">
                    <div class="lt-field">
                        <label for="title" class="lt-label">Capstone Title</label>
                        <input id="title" name="title" class="lt-input" maxlength="255" value="{{ old('title') }}" required>
                    </div>
                    <div class="lt-field">
                        <label for="abstract" class="lt-label">Abstract</label>
                        <textarea id="abstract" name="abstract" class="lt-textarea" rows="10" required>{{ old('abstract') }}</textarea>
                    </div>
                    <div class="lt-field">
                        <label for="submitted_date" class="lt-label">Date Submitted</label>
                        <input id="submitted_date" name="submitted_date" type="date" class="lt-input" value="{{ old('submitted_date', now()->format('Y-m-d')) }}" required>
                    </div>
                </div>
            </section>

            <section class="lt-card">
                <h2 class="lt-card__title">Required files</h2>
                <div class="lt-form">
                    <div class="lt-field">
                        <label for="study" class="lt-label">Full Study (PDF)</label>
                        <input id="study" name="study" type="file" accept=".pdf,application/pdf" class="lt-input" required>
                    </div>
                    <div class="lt-field">
                        <label for="manual" class="lt-label">System Manual (PDF)</label>
                        <input id="manual" name="manual" type="file" accept=".pdf,application/pdf" class="lt-input" required>
                    </div>
                    <div class="lt-field">
                        <label for="system" class="lt-label">System Files (ZIP)</label>
                        <input id="system" name="system" type="file" accept=".zip,application/zip" class="lt-input" required>
                    </div>
                    <div class="lt-field">
                        <label for="mentee_form" class="lt-label">Mentee Form</label>
                        <input id="mentee_form" name="mentee_form" type="file" accept=".pdf,.doc,.docx" class="lt-input" required>
                    </div>
                </div>
            </section>

            <section class="lt-card">
                <h2 class="lt-card__title">Authors</h2>
                <p class="lt-card__desc">Search and select every student who is part of this capstone. Selected students will be marked as having submitted a capstone.</p>
                <div id="submission-author-fields" class="lt-form">
                    <div class="lt-inline-form submission-author-row">
                        <div class="lt-field" style="flex: 1 1 360px;">
                            <label class="lt-label" for="submission-author-search-0">Search author</label>
                            <input id="submission-author-search-0" class="lt-input submission-author-search" type="search" placeholder="Search by name or student number" autocomplete="off">
                            <select class="lt-select submission-author-select" name="authors[]" required>
                                <option value="">Select an author</option>
                                @foreach($masterlistRecords as $record)
                                    <option value="{{ $record->id }}" data-search="{{ strtolower($record->name.' '.$record->student_number) }}" @selected(old('authors.0') == $record->id)>{{ $record->name ?: 'Unnamed student' }} ({{ $record->student_number }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="lt-btn lt-btn--ghost submission-author-remove" hidden>Remove</button>
                    </div>
                </div>
                <button type="button" id="add-submission-author" class="lt-btn lt-btn--ghost">+ Add another author</button>
                @error('authors')<p class="lt-field-error">{{ $message }}</p>@enderror
            </section>

            <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Uploading...">Submit complete requirements</button>
        </form>
    </main>
    <script>
        (() => {
            const fields = document.getElementById('submission-author-fields');
            const addButton = document.getElementById('add-submission-author');
            const refresh = (select, search = '') => {
                const query = search.trim().toLowerCase();
                [...select.options].forEach((option, index) => {
                    option.hidden = index !== 0 && query !== '' && !option.dataset.search.includes(query);
                });
            };
            const bind = (row) => {
                const search = row.querySelector('.submission-author-search');
                const select = row.querySelector('.submission-author-select');
                row.querySelector('.submission-author-remove').addEventListener('click', () => {
                    row.remove();
                    document.querySelectorAll('.submission-author-remove').forEach((button) => button.hidden = fields.children.length === 1);
                });
                search.addEventListener('input', () => refresh(select, search.value));
            };
            bind(fields.firstElementChild);
            addButton.addEventListener('click', () => {
                const row = fields.firstElementChild.cloneNode(true);
                row.querySelector('.submission-author-search').value = '';
                row.querySelector('.submission-author-select').value = '';
                row.querySelector('.submission-author-remove').hidden = false;
                fields.appendChild(row);
                bind(row);
            });
        })();
    </script>
</body>
</html>
