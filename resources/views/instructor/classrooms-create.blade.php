<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Classroom &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
    
</head>

<body class="lt-body">

    @include('components.site-navigation')

    <main class="lt-container lt-container--narrow lt-animate">

        <a href="{{ route('instructor.classrooms') }}" class="lt-back">&larr; Back to classrooms</a>

        <div class="lt-page-head">
            <span class="lt-eyebrow">New Classroom</span>
            <h1>Create Classroom</h1>
            <p class="lt-page-head__sub">
                A class code is generated for your students to join.
            </p>
        </div>

        <div class="lt-chips">
            <div class="lt-chip">
                <span class="lt-chip__label">Instructor</span>
                <span class="lt-chip__value">
                    {{ auth()->user()->first_name }}
                    {{ auth()->user()->last_name }}
                </span>
            </div>
        </div>

        @if($errors->any())

            <div class="lt-alert lt-alert--error" role="alert">
                <span class="lt-alert__icon">!</span>
                <div>
                    <strong>Please fix the following:</strong>

                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

        @endif

        <form method="POST"
              action="{{ route('instructor.classrooms.store') }}"
              class="lt-card lt-form"
              data-submit-guard>

            @csrf

            <div class="lt-field">
                <label for="classroom_name" class="lt-label">
                    Classroom Name
                </label>

                <input
                    type="text"
                    id="classroom_name"
                    name="classroom_name"
                    class="lt-input"
                    value="{{ old('classroom_name') }}"
                    placeholder="Example: BSIT 3-1"
                    required
                >

                <span class="lt-hint">Use the program, year and section so students can recognize it.</span>
            </div>

            <div class="lt-form__actions">
                <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Creating...">
                    Create Classroom
                </button>

                <a href="{{ route('instructor.classrooms') }}" class="lt-btn lt-btn--ghost">
                    Cancel
                </a>
            </div>

        </form>

    </main>

</body>
</html>