<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit Group Capstone &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    @include('components.site-navigation')
    <main class="lt-container lt-container--narrow lt-animate">
        <div class="lt-page-head">
            <span class="lt-eyebrow">Group Submission</span>
            <h1>Submit Capstone</h1>
            <p class="lt-page-head__sub">Select every enrolled author from the masterlist. Selected students will be marked as having submitted a capstone.</p>
        </div>
        @if(session('success')) <div class="lt-alert lt-alert--success" role="status">{{ session('success') }}</div> @endif
        @if($errors->any()) <div class="lt-alert lt-alert--error" role="alert">{{ $errors->first() }}</div> @endif
        <form method="POST" action="{{ route('student.group-submissions.store') }}" enctype="multipart/form-data" class="lt-form">
            @csrf
            <div class="lt-card">
                <div class="lt-field">
                    <label class="lt-label" for="program">Program</label>
                    <select class="lt-select" id="program" name="program" required>
                        <option value="">Select program</option>
                        <option value="BSCS" @selected(old('program') === 'BSCS')>Computer Science (CS)</option>
                        <option value="BSIT" @selected(old('program') === 'BSIT')>Information Technology (IT)</option>
                    </select>
                </div>
                <div class="lt-field"><label class="lt-label" for="title">Capstone Title</label><input class="lt-input" id="title" name="title" value="{{ old('title') }}" required></div>
                <div class="lt-field"><label class="lt-label" for="abstract">Abstract</label><textarea class="lt-textarea" id="abstract" name="abstract" rows="8" required>{{ old('abstract') }}</textarea></div>
                <div class="lt-field"><label class="lt-label" for="adviser">Adviser</label><input class="lt-input" id="adviser" name="adviser" value="{{ old('adviser') }}" required></div>
                <div class="lt-field"><label class="lt-label" for="submitted_date">Date Submitted</label><input class="lt-input" id="submitted_date" name="submitted_date" type="date" value="{{ old('submitted_date', now()->format('Y-m-d')) }}" required></div>
            </div>
            <div class="lt-card">
                <h2 class="lt-card__title">Authors</h2>
                <p class="lt-card__desc">Search the selected program and add each author. The first selected author is treated as the submitting group member.</p>
                <div id="author-fields" class="lt-form">
                    <div class="lt-inline-form author-row">
                        <div class="lt-field" style="flex: 1 1 360px;">
                            <label class="lt-label" for="author-search-0">Search author</label>
                            <input id="author-search-0" class="lt-input author-search" type="search" placeholder="Search by name or student number" autocomplete="off">
                            <select class="lt-select author-select" name="authors[]" required>
                                <option value="">Select an author</option>
                                @foreach($masterlistRecords as $record)
                                    <option value="{{ $record->id }}" data-program="{{ $record->program }}" data-search="{{ strtolower($record->name.' '.$record->student_number) }}" @selected(old('authors.0') == $record->id)>{{ $record->name ?: 'Unnamed student' }} ({{ $record->student_number }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="lt-btn lt-btn--ghost author-remove" hidden>Remove</button>
                    </div>
                </div>
                <button type="button" id="add-author" class="lt-btn lt-btn--ghost">+ Add another author</button>
                <p class="lt-hint">Only authors from the selected program can be selected.</p>
            </div>
            <div class="lt-card">
                <h2 class="lt-card__title">Required Files</h2>
                <div class="lt-field"><label class="lt-label" for="study">Study PDF</label><input class="lt-input" id="study" name="study" type="file" accept=".pdf" required></div>
                <div class="lt-field"><label class="lt-label" for="system">System ZIP</label><input class="lt-input" id="system" name="system" type="file" accept=".zip,application/zip,application/x-zip-compressed,application/octet-stream" required></div>
                <div class="lt-field"><label class="lt-label" for="document">Word Document (.docx)</label><input class="lt-input" id="document" name="document" type="file" accept=".docx" required></div>
                <div class="lt-field"><label class="lt-label" for="mentee_form">Mentee Form</label><input class="lt-input" id="mentee_form" name="mentee_form" type="file" accept=".pdf,.doc,.docx" required></div>
            </div>
            <button type="submit" class="lt-btn lt-btn--primary">Submit Group Capstone</button>
        </form>
    </main>
    <script>
        (() => {
            const fields = document.getElementById('author-fields');
            const program = document.getElementById('program');
            const addButton = document.getElementById('add-author');
            const options = @json($masterlistRecords->map(fn ($record) => ['id' => $record->id, 'program' => $record->program, 'name' => $record->name ?: 'Unnamed student', 'number' => $record->student_number]));

            const refreshOptions = (select, search = '') => {
                const current = select.value;
                const query = search.trim().toLowerCase();
                select.innerHTML = '<option value="">Select an author</option>';
                options.filter((option) => (!program.value || option.program === program.value) && (!query || `${option.name} ${option.number}`.toLowerCase().includes(query))).forEach((option) => {
                    const item = new Option(`${option.name} (${option.number})`, option.id);
                    select.add(item);
                });
                if ([...select.options].some((option) => option.value === current)) select.value = current;
            };

            const bindRow = (row) => {
                const search = row.querySelector('.author-search');
                const select = row.querySelector('.author-select');
                const remove = row.querySelector('.author-remove');
                search.addEventListener('input', () => refreshOptions(select, search.value));
                remove.addEventListener('click', () => {
                    row.remove();
                    document.querySelectorAll('.author-remove').forEach((button) => button.hidden = fields.children.length === 1);
                });
                refreshOptions(select);
            };

            bindRow(fields.firstElementChild);
            program.addEventListener('change', () => document.querySelectorAll('.author-select').forEach((select) => refreshOptions(select)));
            addButton.addEventListener('click', () => {
                const row = fields.firstElementChild.cloneNode(true);
                row.querySelector('.author-search').value = '';
                row.querySelector('.author-select').value = '';
                row.querySelector('.author-remove').hidden = false;
                fields.appendChild(row);
                bindRow(row);
            });
        })();
    </script>
</body>
</html>
