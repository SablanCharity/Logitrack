<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Masterlist &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    @include('components.site-navigation')
    <main class="lt-container lt-animate">
        <div class="lt-page-head">
            <span class="lt-eyebrow">Admin Verification</span>
            <h1>Student Masterlist</h1>
            <p class="lt-page-head__sub">Upload a CSV with student_number and name columns. Every extracted student is enrolled immediately.</p>
        </div>

        @if(session('success')) <div class="lt-alert lt-alert--success" role="status">{{ session('success') }}</div> @endif
        @if(session('warning')) <div class="lt-alert lt-alert--warning" role="status">{{ session('warning') }}</div> @endif
        @if($errors->any()) <div class="lt-alert lt-alert--error" role="alert">{{ $errors->first() }}</div> @endif

        <div class="lt-card lt-mt-lg">
            <div class="lt-card__head">
                <div>
                    <h2 class="lt-card__title">Upload student masterlist</h2>
                    <p class="lt-card__desc">Choose the program and CSV file together. Extracted students are enrolled immediately.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.student-masterlist.upload') }}" enctype="multipart/form-data" class="lt-form">
                @csrf
                <div class="lt-field">
                    <label for="program" class="lt-label">Program</label>
                    <select id="program" name="program" class="lt-select" required>
                        <option value="">Select program</option>
                        <option value="BSCS">Computer Science (CS)</option>
                        <option value="BSIT">Information Technology (IT)</option>
                    </select>
                </div>
                <div class="lt-field">
                    <label for="masterlist" class="lt-label">CSV file</label>
                    <input id="masterlist" name="masterlist" type="file" accept=".csv,.txt" class="lt-input" required>
                </div>
                <button type="submit" class="lt-btn lt-btn--primary">Upload masterlist</button>
            </form>
        </div>

        <div class="lt-card lt-mt-lg">
            <div class="lt-card__head">
                <div>
                    <h2 class="lt-card__title">Enrolled students</h2>
                    <p class="lt-card__desc">Search by student number or name and filter by program.</p>
                </div>
            </div>
            <form method="GET" action="{{ route('admin.student-masterlist.index') }}" class="lt-masterlist-filter-form">
                <div class="lt-field">
                    <label for="program-filter" class="lt-sr-only">Filter by program</label>
                    <select id="program-filter" name="program_filter" class="lt-select">
                        <option value="">All programs</option>
                        <option value="BSCS" @selected($programFilter === 'BSCS')>Computer Science (CS)</option>
                        <option value="BSIT" @selected($programFilter === 'BSIT')>Information Technology (IT)</option>
                    </select>
                </div>
                <div class="lt-field">
                    <label for="search" class="lt-sr-only">Search student number or name</label>
                    <input id="search" name="search" type="search" class="lt-input" value="{{ $search }}" placeholder="Search student number or name">
                </div>
                <div class="lt-masterlist-filter-form__actions">
                    <button type="submit" class="lt-btn lt-btn--ghost">Search</button>
                    @if($search !== '' || $programFilter)
                        <a href="{{ route('admin.student-masterlist.index') }}" class="lt-btn lt-btn--ghost">Clear</a>
                    @endif
                </div>
            </form>
            <form id="bulk-delete-form" method="POST" action="{{ route('admin.student-masterlist.destroy-selected') }}" data-confirm="Delete the selected masterlist record(s)? Existing student accounts will remain active, but related author links may be removed." data-confirm-title="Delete student records" data-confirm-button="Delete selected" data-confirm-danger>
                @csrf
                @method('DELETE')
            </form>
            <div class="lt-table-wrap lt-masterlist-table-wrap">
                <table class="lt-table">
                    <thead><tr><th><span class="lt-sr-only">Select</span></th><th>Student Number</th><th>Name</th><th>Program</th><th>Date Added</th><th>Capstone Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td><input type="checkbox" form="bulk-delete-form" class="masterlist-record-checkbox" name="records[]" value="{{ $record->id }}" aria-label="Select {{ $record->student_number }}"></td>
                                <td>{{ $record->student_number }}</td>
                                <td>{{ $record->name ?: trim($record->first_name.' '.$record->last_name) ?: '—' }}</td>
                                <td>{{ $record->program ?: '—' }}</td>
                                <td>{{ $record->created_at?->format('M d, Y') ?? '—' }}</td>
                                <td>
                                    @if($capstoneStatuses->has($record->student_number))
                                        @php($status = $capstoneStatuses->get($record->student_number))
                                        <span class="lt-badge {{ $status === 'returned' ? 'lt-badge--rejected' : ($status === 'approved' ? 'lt-badge--approved' : 'lt-badge--submitted') }}">
                                            {{ ucfirst($status) }} Capstone
                                        </span>
                                    @else
                                        <span class="lt-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.student-masterlist.destroy', $record) }}" data-confirm="Delete this student masterlist record? The student account, if any, will remain active." data-confirm-title="Delete student record" data-confirm-button="Delete student" data-confirm-danger>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="lt-masterlist-delete-icon" aria-label="Delete {{ $record->student_number }}" title="Delete student">
                                            <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7">No masterlist records uploaded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="lt-masterlist-bulk-actions">
                <label class="lt-masterlist-select-all">
                    <input type="checkbox" id="select-all-records">
                    Select all on this page
                </label>
                <button id="delete-selected-records" form="bulk-delete-form" type="submit" class="lt-masterlist-delete-icon lt-masterlist-delete-selected" aria-label="Delete selected students" title="Delete selected" disabled>
                    <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                </button>
            </div>
            <div class="lt-pager">
                {{ $records->withQueryString()->links() }}
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('select-all-records');
            const recordChecks = Array.from(document.querySelectorAll('.masterlist-record-checkbox'));
            const deleteSelected = document.getElementById('delete-selected-records');

            if (!selectAll) return;

            function updateSelectAll() {
                const selectedCount = recordChecks.filter((checkbox) => checkbox.checked).length;
                selectAll.checked = recordChecks.length > 0 && selectedCount === recordChecks.length;
                selectAll.indeterminate = selectedCount > 0 && selectedCount < recordChecks.length;
                if (deleteSelected) deleteSelected.disabled = selectedCount === 0;
            }

            selectAll.addEventListener('change', function () {
                recordChecks.forEach((checkbox) => {
                    checkbox.checked = selectAll.checked;
                });
                updateSelectAll();
            });

            recordChecks.forEach((checkbox) => checkbox.addEventListener('change', updateSelectAll));
            updateSelectAll();
        });
    </script>
</body>
</html>
