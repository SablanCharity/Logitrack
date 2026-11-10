<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submitted Capstones &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    @include('components.site-navigation')

    <main class="lt-container lt-animate">
        <div class="lt-page-head">
            <span class="lt-eyebrow">{{ $audience }} Review</span>
            <h1>Submitted Capstones</h1>
            <p class="lt-page-head__sub">View submitted study details and access all required files.</p>
        </div>

        @if($submissions->isEmpty())
            <div class="lt-empty">
                <div class="lt-empty__title">No student submissions</div>
                <p>There are no uploaded capstone submissions to review.</p>
            </div>
        @else
            <div class="lt-table-wrap">
                <table class="lt-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Submitted By</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($submissions as $submission)
                            <tr>
                                <td style="min-width: 260px;">{{ $submission->title }}</td>
                                <td>{{ $submission->submitter->first_name }} {{ $submission->submitter->last_name }}</td>
                                <td>{{ $submission->submitted_date->format('M d, Y') }}</td>
                                <td>{{ ucfirst($submission->status) }}</td>
                                <td>
                                    <a class="lt-btn lt-btn--primary lt-btn--sm" href="{{ route($audience === 'Admin' ? 'admin.student-submissions.show' : 'instructor.student-submissions.show', $submission) }}">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </main>
</body>
</html>
