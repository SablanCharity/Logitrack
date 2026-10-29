<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $classroom->classroom_name }} &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
    
</head>

<body class="lt-body">

    @include('components.site-navigation')

    <main class="lt-container lt-animate">

        <a href="{{ route('instructor.classrooms') }}" class="lt-back">
            &larr; Back to Classrooms
        </a>

        <div class="lt-page-head">
            <span class="lt-eyebrow">Classroom</span>
            <h1>{{ $classroom->classroom_name }}</h1>
        </div>

        <div class="lt-chips">
            <div class="lt-chip">
                <span class="lt-chip__label">Class Code</span>
                <span class="lt-chip__value">
                    <span class="lt-code">
                        {{ $classroom->class_code }}
                        <button type="button" class="lt-copy" data-copy="{{ $classroom->class_code }}">Copy</button>
                    </span>
                </span>
            </div>

            <div class="lt-chip">
                <span class="lt-chip__label">Instructor</span>
                <span class="lt-chip__value">
                    {{ auth()->user()->first_name }}
                    {{ auth()->user()->last_name }}
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status" data-autohide="6000">
                <span class="lt-alert__icon">&#10003;</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="lt-alert lt-alert--error" role="alert">
                <span class="lt-alert__icon">!</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <div class="lt-grid-2">
            <section class="lt-card lt-classroom-option">
                <div class="lt-card__head">
                    <h2 class="lt-card__title">Students</h2>
                </div>
                <p>View and manage the students enrolled in this classroom.</p>
                <a href="{{ route('instructor.classroom.students', $classroom->id) }}" class="lt-btn lt-btn--ghost lt-btn--sm">View Students</a>
            </section>

            <section class="lt-card lt-classroom-option">
                <div class="lt-card__head">
                    <h2 class="lt-card__title">Group Rooms</h2>
                </div>
                <p>View the groups and capstone submission status for this classroom.</p>
                <a href="{{ route('instructor.classroom.group-rooms', $classroom->id) }}" class="lt-btn lt-btn--ghost lt-btn--sm">View Group Rooms</a>
            </section>
        </div>

    </main>

</body>
</html>
