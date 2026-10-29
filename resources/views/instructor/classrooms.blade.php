<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Classrooms &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
    
</head>

<body class="lt-body">

    @include('components.site-navigation')

    <main class="lt-container lt-animate">

        <div class="lt-page-head">
            <div class="lt-page-head__row">
                <div>
                    <span class="lt-eyebrow">Instructor &middot; {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                    <h1>My Classrooms</h1>
                    <p class="lt-page-head__sub">Share the class code with your students so they can join.</p>
                </div>

                <a href="{{ route('instructor.classrooms.create') }}" class="lt-btn lt-btn--gold">
                    + Create Classroom
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status" data-autohide="6000">
                <span class="lt-alert__icon">&#10003;</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($classrooms->count() > 0)

            <div class="lt-stats">
                <div class="lt-stat">
                    <div class="lt-stat__num">{{ $classrooms->count() }}</div>
                    <div class="lt-stat__label">{{ $classrooms->count() === 1 ? 'Classroom' : 'Classrooms' }}</div>
                </div>
            </div>

            <div class="lt-table-wrap">

                <table class="lt-table">

                    <thead>
                        <tr>
                            <th>Classroom Name</th>
                            <th>Class Code</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($classrooms as $classroom)

                            <tr>
                                <td>
                                    <a href="{{ route('instructor.classroom.show', $classroom->id) }}" class="lt-table__title">
                                        {{ $classroom->classroom_name }}
                                    </a>
                                </td>

                                <td>
                                    <span class="lt-code">
                                        {{ $classroom->class_code }}
                                        <button type="button" class="lt-copy" data-copy="{{ $classroom->class_code }}">Copy</button>
                                    </span>
                                </td>

                                <td class="lt-muted lt-nowrap">
                                    {{ $classroom->created_at->format('M d, Y') }}
                                </td>

                                <td>
                                    <a href="{{ route('instructor.classroom.show', $classroom->id) }}" class="lt-btn lt-btn--ghost lt-btn--sm">
                                        View Classroom
                                    </a>
                                </td>
                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="lt-empty">
                <div class="lt-empty__icon">+</div>
                <div class="lt-empty__title">No classrooms yet</div>
                <p>You don't have any classrooms yet.</p>
                <p class="lt-mt">
                    <a href="{{ route('instructor.classrooms.create') }}" class="lt-btn lt-btn--primary">+ Create Classroom</a>
                </p>
            </div>

        @endif

    </main>

</body>
</html>