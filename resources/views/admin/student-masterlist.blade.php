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
        @if($errors->any()) <div class="lt-alert lt-alert--error" role="alert">{{ $errors->first() }}</div> @endif

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

        <div class="lt-table-wrap lt-mt-lg">
            <table class="lt-table">
                <thead><tr><th>Student Number</th><th>Name</th><th>Program</th><th>Capstone Status</th></tr></thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ $record->student_number }}</td>
                            <td>{{ $record->name ?: trim($record->first_name.' '.$record->last_name) ?: '—' }}</td>
                            <td>{{ $record->program ?: '—' }}</td>
                            <td>
                                @if($submittedStudentNumbers->has($record->student_number))
                                    <span class="lt-badge lt-badge--approved">Submitted Capstone</span>
                                @else
                                    <span class="lt-muted">--</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No masterlist records uploaded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
