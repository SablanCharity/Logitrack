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

        @if(session()->has('success') && $submission)
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
                <div class="submission-author-list">
                    <div id="submission-author-fields" class="lt-form">
                        <div class="submission-author-row">
                            <div class="lt-field submission-author-field">
                                <label class="lt-label" for="submission-author-search-0">Capstone member</label>
                                <div class="submission-author-controls">
                                    <div class="submission-author-picker">
                                        <input id="submission-author-search-0" class="lt-input submission-author-search" type="search" placeholder="Search by name or student number" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="submission-author-options-0" hidden disabled>
                                        <button type="button" class="submission-author-toggle" aria-label="Show student names" aria-expanded="false" aria-controls="submission-author-options-0">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                        </button>
                                        <div id="submission-author-options-0" class="submission-author-options" role="listbox" hidden></div>
                                    </div>
                                    <select class="lt-select submission-author-select" name="authors[]" aria-label="Capstone member" required>
                                        <option value="">Select an author</option>
                                        @foreach($masterlistRecords as $record)
                                            <option value="{{ $record->id }}" data-search="{{ strtolower($record->name.' '.$record->student_number) }}" @selected(old('authors.0') == $record->id)>{{ $record->name ?: 'Unnamed student' }} ({{ $record->student_number }})</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="lt-btn lt-btn--ghost submission-author-remove" hidden>Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-submission-author" class="lt-btn lt-btn--ghost submission-author-add">+ Add another author</button>
                </div>
                @error('authors')<p class="lt-field-error">{{ $message }}</p>@enderror
            </section>

            <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Uploading...">Submit complete requirements</button>
        </form>
    </main>
    <script>
        (() => {
            const fields = document.getElementById('submission-author-fields');
            const addButton = document.getElementById('add-submission-author');
            const maxAuthors = 2;
            let nextAuthorIndex = fields.children.length;
            const closeOptions = (row) => {
                const search = row.querySelector('.submission-author-search');
                const toggle = row.querySelector('.submission-author-toggle');
                const options = row.querySelector('.submission-author-options');

                options.hidden = true;
                search.setAttribute('aria-expanded', 'false');
                search.removeAttribute('aria-activedescendant');
                toggle.setAttribute('aria-expanded', 'false');
            };
            const renderOptions = (row) => {
                const search = row.querySelector('.submission-author-search');
                const select = row.querySelector('.submission-author-select');
                const options = row.querySelector('.submission-author-options');
                const query = select.value ? '' : search.value.trim().toLowerCase();
                const matchingOptions = [...select.options].filter((option) => option.value && (!query || option.dataset.search.includes(query)));

                options.replaceChildren();
                if (matchingOptions.length === 0) {
                    const emptyMessage = document.createElement('p');
                    emptyMessage.className = 'submission-author-empty';
                    emptyMessage.textContent = 'No students found.';
                    options.appendChild(emptyMessage);
                    return;
                }

                matchingOptions.forEach((option) => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'submission-author-option';
                    item.id = `${options.id}-option-${option.index}`;
                    item.dataset.value = option.value;
                    item.setAttribute('role', 'option');
                    item.setAttribute('aria-selected', select.value === option.value ? 'true' : 'false');
                    item.textContent = option.textContent.trim();
                    options.appendChild(item);
                });
            };
            const showOptions = (row) => {
                const search = row.querySelector('.submission-author-search');
                const toggle = row.querySelector('.submission-author-toggle');

                renderOptions(row);
                row.querySelector('.submission-author-options').hidden = false;
                search.setAttribute('aria-expanded', 'true');
                toggle.setAttribute('aria-expanded', 'true');
            };
            const updateRemoveButtons = () => {
                fields.querySelectorAll('.submission-author-remove').forEach((button) => {
                    button.hidden = fields.children.length === 1;
                });
                addButton.hidden = fields.children.length >= maxAuthors;
            };
            const selectOption = (row, item) => {
                const search = row.querySelector('.submission-author-search');
                const select = row.querySelector('.submission-author-select');

                select.value = item.dataset.value;
                search.value = item.textContent.trim();
                search.setCustomValidity('');
                closeOptions(row);
            };

            fields.querySelectorAll('.submission-author-row').forEach((row) => {
                const search = row.querySelector('.submission-author-search');
                const select = row.querySelector('.submission-author-select');
                const selectedOption = select.options[select.selectedIndex];

                row.querySelector('.lt-label').htmlFor = search.id;
                search.hidden = false;
                search.disabled = false;
                search.required = true;
                select.hidden = true;
                select.required = false;

                if (select.value) {
                    search.value = selectedOption.textContent.trim();
                }
            });
            updateRemoveButtons();

            fields.addEventListener('input', (event) => {
                if (!event.target.matches('.submission-author-search')) {
                    return;
                }

                const search = event.target;
                const row = search.closest('.submission-author-row');
                row.querySelector('.submission-author-select').value = '';
                search.setCustomValidity(search.value.trim() ? 'Select a student from the list.' : '');
                search.removeAttribute('aria-activedescendant');
                showOptions(row);
            });

            fields.addEventListener('click', (event) => {
                const item = event.target.closest('.submission-author-option');
                if (item) {
                    selectOption(item.closest('.submission-author-row'), item);
                    return;
                }

                const removeButton = event.target.closest('.submission-author-remove');
                if (removeButton) {
                    removeButton.closest('.submission-author-row').remove();
                    updateRemoveButtons();
                    return;
                }

                const toggle = event.target.closest('.submission-author-toggle');
                if (toggle) {
                    const row = toggle.closest('.submission-author-row');
                    const search = row.querySelector('.submission-author-search');

                    if (row.querySelector('.submission-author-options').hidden) {
                        showOptions(row);
                    } else {
                        closeOptions(row);
                    }

                    search.focus();
                }
            });

            fields.addEventListener('keydown', (event) => {
                if (!event.target.matches('.submission-author-search')) {
                    return;
                }

                const search = event.target;
                const row = search.closest('.submission-author-row');
                const options = row.querySelector('.submission-author-options');

                if (event.key === 'Escape' && !options.hidden) {
                    closeOptions(row);
                    return;
                }

                if (event.key === 'Tab') {
                    closeOptions(row);
                    return;
                }

                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    if (options.hidden) {
                        showOptions(row);
                    }

                    const items = [...options.querySelectorAll('.submission-author-option')];
                    if (items.length === 0) {
                        return;
                    }

                    const activeIndex = items.findIndex((item) => item.id === search.getAttribute('aria-activedescendant'));
                    const nextIndex = event.key === 'ArrowDown'
                        ? (activeIndex + 1) % items.length
                        : (activeIndex <= 0 ? items.length - 1 : activeIndex - 1);

                    search.setAttribute('aria-activedescendant', items[nextIndex].id);
                    items.forEach((item, index) => item.setAttribute('aria-selected', index === nextIndex ? 'true' : 'false'));
                    return;
                }

                if (event.key === 'Enter' && !options.hidden) {
                    const activeOption = document.getElementById(search.getAttribute('aria-activedescendant'));
                    if (activeOption) {
                        event.preventDefault();
                        selectOption(row, activeOption);
                    }
                }
            });

            document.addEventListener('click', (event) => {
                if (!fields.contains(event.target)) {
                    fields.querySelectorAll('.submission-author-row').forEach(closeOptions);
                }
            });

            addButton.addEventListener('click', () => {
                if (fields.children.length >= maxAuthors) {
                    return;
                }

                const row = fields.firstElementChild.cloneNode(true);
                const search = row.querySelector('.submission-author-search');
                const toggle = row.querySelector('.submission-author-toggle');
                const options = row.querySelector('.submission-author-options');
                const id = `submission-author-search-${nextAuthorIndex++}`;
                const optionsId = `${id}-options`;

                row.querySelector('.lt-label').htmlFor = id;
                search.id = id;
                search.value = '';
                search.setCustomValidity('');
                search.setAttribute('aria-controls', optionsId);
                toggle.setAttribute('aria-controls', optionsId);
                row.querySelector('.submission-author-select').value = '';
                options.id = optionsId;
                options.replaceChildren();
                closeOptions(row);
                fields.appendChild(row);
                updateRemoveButtons();
            });
        })();
    </script>
</body>
</html>
