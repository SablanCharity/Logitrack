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
            <p class="lt-page-head__sub">Track group submissions, student coverage, and review status across CS and IT.</p>
        </div>

        <div class="lt-stats">
            <div class="lt-stat">
                <div class="lt-stat__num">{{ $submissions->count() }}</div>
                <div class="lt-stat__label">Group submissions displayed</div>
            </div>
            <div class="lt-stat">
                <div class="lt-stat__num">{{ $counts['enrolled_cscs'] }}</div>
                <div class="lt-stat__label">Enrolled CS students</div>
            </div>
            <div class="lt-stat">
                <div class="lt-stat__num">{{ $counts['enrolled_it'] }}</div>
                <div class="lt-stat__label">Enrolled IT students</div>
            </div>
            <div class="lt-stat">
                <div class="lt-stat__num">{{ $counts['submitted_cscs'] + $counts['submitted_it'] }}</div>
                <div class="lt-stat__label">Students submitted</div>
            </div>
            <div class="lt-stat">
                <div class="lt-stat__num">{{ $counts['not_submitted_cscs'] + $counts['not_submitted_it'] }}</div>
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
                    <p class="lt-card__desc">Review individual capstone studies submitted by students.</p>
                </div>
            </div>
            <div class="lt-table-wrap">
                <table class="lt-table lt-table--compact">
                    <thead>
                        <tr>
                            <th>Study</th>
                            <th>Submitted By</th>
                            <th>Authors</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Capstone Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentSubmissions as $studentSubmission)
                            <tr>
                                <td style="min-width: 220px;">
                                    <strong class="lt-table__title">{{ $studentSubmission->title }}</strong>
                                </td>
                                <td>{{ $studentSubmission->submitter->first_name }} {{ $studentSubmission->submitter->last_name }}</td>
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
                            <tr><td colspan="6"><div class="lt-empty">No student submission requests found.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="lt-card">
            <div class="lt-card__head">
                <div>
                    <h2 class="lt-card__title">Group submissions</h2>
                    <p class="lt-card__desc">Track group capstones, student coverage, and review status across CS and IT.</p>
                </div>
            </div>
            <div class="lt-table-wrap">
                <table class="lt-table lt-table--compact">
                    <thead>
                        <tr>
                            <th>Capstone</th>
                            <th>Program</th>
                            <th>Authors / Student Numbers</th>
                            <th>Adviser</th>
                            <th>Date Submitted</th>
                            <th>Files</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submissions as $submission)
                            <tr>
                                <td style="min-width: 240px;">
                                    <strong class="lt-table__title">{{ $submission->title }}</strong>
                                    <span class="lt-table__sub">{{ \Illuminate\Support\Str::limit($submission->abstract, 120) }}</span>
                                </td>
                                <td class="lt-nowrap">{{ $submission->program }}</td>
                                <td style="min-width: 220px;">
                                    @foreach($submission->members as $member)
                                        <span class="lt-table__sub">{{ $member->masterlistRecord?->name ?: $member->student?->name ?: 'Unnamed student' }} ({{ $member->masterlistRecord?->student_number ?: $member->student?->student_number ?: 'No student number' }}) @if($member->is_leader)<strong>(Leader)</strong>@endif</span>
                                    @endforeach
                                </td>
                                <td>{{ $submission->adviser }}</td>
                                <td class="lt-nowrap">{{ $submission->submitted_date->format('M d, Y') }}</td>
                                <td style="min-width: 180px;">
                                    <span class="lt-table__sub">Study: {{ basename($submission->study_path) }}</span>
                                    <span class="lt-table__sub">System: {{ basename($submission->system_path) }}</span>
                                    <span class="lt-table__sub">Word: {{ basename($submission->document_path) }}</span>
                                    <span class="lt-table__sub">Mentee: {{ basename($submission->mentee_form_path) }}</span>
                                </td>
                                <td><span class="lt-badge lt-badge--pending">{{ ucfirst($submission->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="lt-empty">No submitted capstones found for this filter.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
