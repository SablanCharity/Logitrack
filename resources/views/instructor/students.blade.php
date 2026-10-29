<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pending Students &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
    
</head>

<body class="lt-body">

    @include('components.site-navigation')

    <main class="lt-container lt-animate">

        <div class="lt-page-head">
            <span class="lt-eyebrow">Instructor &middot; {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
            <h1>Pending Students</h1>
            <p class="lt-page-head__sub">Approve students so they can access their classroom and create groups.</p>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status" data-autohide="6000">
                <span class="lt-alert__icon">&#10003;</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($students->count() > 0)

            <div class="lt-stats">
                <div class="lt-stat">
                    <div class="lt-stat__num">{{ $students->count() }}</div>
                    <div class="lt-stat__label">Waiting for approval</div>
                </div>
            </div>

            <div class="lt-card__head">
                <input
                    type="search"
                    class="lt-input"
                    style="max-width: 320px;"
                    placeholder="Search pending students..."
                    aria-label="Search pending students"
                    data-table-filter="#pending-table"
                    data-filter-empty="#pending-filter-empty"
                >
            </div>

            <div class="lt-table-wrap">

                <table class="lt-table" id="pending-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>Year &amp; Section</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>
                                    <div class="lt-cell-user">
                                        <span class="lt-avatar">{{ strtoupper(substr($student->first_name, 0, 1)) }}</span>
                                        <span class="lt-cell-user__name">
                                            {{ $student->first_name }}
                                            {{ $student->last_name }}
                                        </span>
                                    </div>
                                </td>

                                <td class="lt-muted">{{ $student->email }}</td>

                                <td>{{ $student->program }}</td>

                                <td>{{ $student->year_section }}</td>

                                <td>
                                    <div class="lt-actions">
                                        <form method="POST" action="{{ route('instructor.students.approve', $student->id) }}" data-submit-guard>
                                            @csrf
                                            <button type="submit" class="lt-btn lt-btn--green lt-btn--sm" data-loading-text="Approving...">Approve</button>
                                        </form>

                                        <button type="button" class="lt-btn lt-btn--danger-ghost lt-btn--sm">Reject</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

            <div id="pending-filter-empty" class="lt-empty lt-hidden lt-mt">
                <div class="lt-empty__title">No matching student</div>
                <p>Try a different name or email.</p>
            </div>

        @else

            <div class="lt-empty">
                <div class="lt-empty__icon">&#10003;</div>
                <div class="lt-empty__title">You're all caught up</div>
                <p>No pending students.</p>
            </div>

        @endif

    </main>

</body>
</html>