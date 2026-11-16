<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submission Monitoring &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    @include('components.site-navigation')

    <main class="lt-container lt-animate">
        <div class="lt-page-head">
            <span class="lt-eyebrow">Admin &middot; Capstone Monitoring</span>
            <h1>Submitted Capstones</h1>
            <p class="lt-page-head__sub">Track individual student capstone submissions and student coverage across CS and IT.</p>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="lt-alert lt-alert--error" role="alert">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="lt-alert lt-alert--error" role="alert">
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="lt-stats">
            <div class="lt-stat">
                <div class="lt-stat__num">{{ $counts['enrolled_cscs'] }}</div>
                <div class="lt-stat__label">Enrolled CS students</div>
            </div>
            <div class="lt-stat">
                <div class="lt-stat__num">{{ $counts['enrolled_it'] }}</div>
                <div class="lt-stat__label">Enrolled IT students</div>
            </div>
            <div class="lt-stat">
                <div class="lt-stat__num">{{ $counts['submitted'] }}</div>
                <div class="lt-stat__label">Students submitted</div>
            </div>
            <div class="lt-stat">
                <div class="lt-stat__num">{{ $counts['not_submitted'] }}</div>
                <div class="lt-stat__label">Students not submitted</div>
            </div>
            <div class="lt-stat">
                <div class="lt-stat__num">{{ $counts['pending'] }}</div>
                <div class="lt-stat__label">Pending review</div>
            </div>
            <div class="lt-stat">
                <div class="lt-stat__num">{{ $counts['approved'] }}</div>
                <div class="lt-stat__label">Approved</div>
            </div>
        </div>

        <section class="lt-card lt-mb-lg">
            <div class="lt-card__head">
                <div>
                    <h2 class="lt-card__title">Submission period</h2>
                    <p class="lt-card__desc">
                        Status:
                        <strong>{{ $submissionWindow->isCurrentlyOpen() ? 'Open' : 'Closed' }}</strong>
                        @if($submissionWindow->deadline_on)
                            &middot; Due {{ $submissionWindow->deadline_on->format('M d, Y') }}
                        @endif
                    </p>
                    <p class="lt-card__desc">Submissions stay closed until you open them. They close automatically after the full due date. You can edit the due date at any time.</p>
                </div>
            </div>
            <div style="display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;">
                <form method="POST" action="{{ route('admin.group-submissions.submission-window') }}" style="display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; margin: 0;">
                    @csrf
                    @method('PATCH')
                    <div class="lt-field">
                        <label for="deadline_on" class="lt-label">Submission due date</label>
                        <input id="deadline_on" name="deadline_on" type="date" class="lt-input" value="{{ old('deadline_on', $submissionWindow->deadline_on?->format('Y-m-d')) }}" required>
                    </div>
                    <button type="submit" class="lt-btn lt-btn--primary" style="flex: 0 0 auto; white-space: nowrap;">Save due date</button>
                </form>
                <form method="POST" action="{{ route('admin.capstones.submission-window') }}" style="margin: 0;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="lt-btn {{ $submissionWindow->isCurrentlyOpen() ? 'lt-btn--danger-ghost' : 'lt-btn--green' }}" style="flex: 0 0 auto; white-space: nowrap;" @if($submissionWindow->deadline_on === null || $submissionWindow->deadline_on->toDateString() < now()->toDateString()) @disabled(!$submissionWindow->isCurrentlyOpen()) @endif>
                        {{ $submissionWindow->isCurrentlyOpen() ? 'Close submissions' : 'Open submissions' }}
                    </button>
                </form>
            </div>
        </section>

        <div class="lt-card lt-mb-lg">
            <div class="lt-card__head">
                <div>
                    <h2 class="lt-card__title">Submission records</h2>
                    <p class="lt-card__desc">Filter the monitoring list by academic program.</p>
                </div>
                <form method="GET" class="lt-inline-form">
                    <label class="lt-sr-only" for="program">Filter by program</label>
                    <select id="program" name="program" class="lt-select">
                        <option value="">All programs</option>
                        <option value="BSCS" @selected($program === 'BSCS')>Computer Science (CS)</option>
                        <option value="BSIT" @selected($program === 'BSIT')>Information Technology (IT)</option>
                    </select>
                    <button class="lt-btn lt-btn--ghost" type="submit">Apply filter</button>
                    @if($program)
                        <a href="{{ route('admin.group-submissions.index') }}" class="lt-btn lt-btn--ghost">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <section class="lt-card lt-mb-lg">
            <div class="lt-card__head">
                <div>
                    <h2 class="lt-card__title">Student submission requests</h2>
                    <p class="lt-card__desc">Review capstone studies submitted by students.</p>
                </div>
            </div>
            <div class="lt-table-wrap">
                <table class="lt-table lt-table--compact">
                    <thead>
                        <tr>
                            <th>Study</th>
                            <th>Submitted By</th>
                            <th>Adviser</th>
                            <th>Authors</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentSubmissions as $studentSubmission)
                            <tr>
                                <td style="min-width: 220px;">
                                    <strong class="lt-table__title">{{ $studentSubmission->title }}</strong>
                                </td>
                                <td>{{ $studentSubmission->submitter->first_name }} {{ $studentSubmission->submitter->last_name }}</td>
                                <td>{{ $studentSubmission->adviserUser ? trim($studentSubmission->adviserUser->first_name.' '.$studentSubmission->adviserUser->last_name) : '—' }}</td>
                                <td style="min-width: 220px;">
                                    @foreach($studentSubmission->authors as $author)
                                        <span class="lt-table__sub">{{ $author->masterlistRecord->name ?: 'Unnamed student' }} ({{ $author->masterlistRecord->student_number }})</span>
                                    @endforeach
                                </td>
                                <td class="lt-nowrap">{{ $studentSubmission->submitted_date->format('M d, Y') }}</td>
                                <td><span class="lt-badge lt-badge--submitted">{{ ucfirst($studentSubmission->status) }}</span></td>
                                <td>
                                    <a class="lt-btn lt-btn--primary lt-btn--sm" href="{{ route('admin.student-submissions.show', $studentSubmission) }}">
                                        View study
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="lt-empty">No student submission requests found.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</body>
</html>
