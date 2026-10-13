<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Group Room · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
    
</head>

<body class="lt-body">

    <header class="lt-nav">
        <div class="lt-nav__inner">
            <a href="{{ route('student.group-rooms', $classroom->id) }}" class="lt-brand">
                <span class="lt-brand__mark">L</span>
                LogiTrack
            </a>
            <span class="lt-nav__tag">Student</span>
        </div>
    </header>

    <main class="lt-container lt-container--narrow lt-animate">

        <a href="{{ route('student.group-rooms', $classroom->id) }}" class="lt-back">← Back to group rooms</a>

        <div class="lt-page-head">
            <span class="lt-eyebrow">New Group</span>
            <h1>Create Group Room</h1>
            <p class="lt-page-head__sub">You'll be the group leader and can add up to 2 more members afterwards, or stay solo.</p>
        </div>

        <div class="lt-chips">
            <div class="lt-chip">
                <span class="lt-chip__label">Classroom</span>
                <span class="lt-chip__value">{{ $classroom->classroom_name }}</span>
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

        @if(session('error'))
            <div class="lt-alert lt-alert--error" role="alert">
                <span class="lt-alert__icon">!</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('student.group-rooms.store', $classroom->id) }}"
            class="lt-card lt-form"
            data-submit-guard
        >

            @csrf

            <div class="lt-field">

                <label for="group_name" class="lt-label">
                    Group Name
                </label>

                <input
                    type="text"
                    id="group_name"
                    name="group_name"
                    class="lt-input"
                    value="{{ old('group_name') }}"
                    placeholder="Example: Group 1"
                    required
                >

                <span class="lt-hint">Pick a name your adviser and groupmates will recognize.</span>

            </div>

            <div class="lt-form__actions">
                <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Creating...">
                    Create Group Room
                </button>

                <a href="{{ route('student.group-rooms', $classroom->id) }}" class="lt-btn lt-btn--ghost">
                    Cancel
                </a>
            </div>

        </form>

    </main>

</body>
</html>