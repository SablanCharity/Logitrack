<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit Capstone &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
    
</head>
<body class="lt-body">

    @include('components.site-navigation')

    <main class="lt-container lt-container--narrow lt-animate">

        <a href="{{ route('student.group-room.show', $groupRoom->id) }}" class="lt-back">&larr; Back to group room</a>

        <div class="lt-page-head">
            <span class="lt-eyebrow">Capstone Submission</span>
            <h1>Submit Capstone</h1>
            <p class="lt-page-head__sub">Enter your capstone details. Your adviser and the admin will review them before they appear in the repository.</p>
        </div>

        <div class="lt-chips">
            <div class="lt-chip">
                <span class="lt-chip__label">Classroom</span>
                <span class="lt-chip__value">{{ $groupRoom->classroom->classroom_name }}</span>
            </div>
            <div class="lt-chip">
                <span class="lt-chip__label">Group</span>
                <span class="lt-chip__value">{{ $groupRoom->group_name }}</span>
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

        <form method="POST"
              action="{{ route('student.capstone.store', $groupRoom->id) }}"
              class="lt-form"
              data-submit-guard
              data-warn-unsaved>

            @csrf

            <section class="lt-card">
                <h2 class="lt-card__title">Capstone Information</h2>
                <p class="lt-card__desc">Fill in the details exactly as written in your approved hardbound study.</p>

                <div class="lt-form">

                    <div class="lt-field">
                        <label for="title" class="lt-label">Capstone Title</label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="lt-input"
                            value="{{ old('title') }}"
                            maxlength="255"
                            data-counter="#title-count"
                            required
                        >

                        <div class="lt-counter">
                            <span class="lt-hint">Use the full official title.</span>
                            <span id="title-count" class="lt-counter__num">0 / 255</span>
                        </div>
                    </div>

                    <div class="lt-field">
                        <label for="abstract" class="lt-label">Abstract</label>

                        <textarea
                            id="abstract"
                            name="abstract"
                            class="lt-textarea"
                            rows="10"
                            data-counter="#abstract-count"
                            required
                        >{{ old('abstract') }}</textarea>

                        <div class="lt-counter">
                            <span class="lt-hint">Paste or type the abstract from your study.</span>
                            <span id="abstract-count" class="lt-counter__num">0</span>
                        </div>
                    </div>

                    <div class="lt-field">
                        <label for="publication_date" class="lt-label">Publication Date</label>

                        <input
                            type="date"
                            id="publication_date"
                            name="publication_date"
                            class="lt-input"
                            value="{{ old('publication_date') }}"
                            required
                        >
                    </div>

                </div>
            </section>

            <section class="lt-auto">
                <div class="lt-auto__title">Automatically Retrieved Information</div>
                <p class="lt-auto__desc">These details come from your account and classroom, so you don't need to enter them.</p>

                <div class="lt-auto__grid">

                    <div class="lt-auto__item lt-auto__item--full">
                        <div class="lt-auto__key">Authors</div>
                        <ul class="lt-authors">
                            @foreach($groupRoom->members as $member)
                                <li>
                                    <span class="lt-avatar">{{ strtoupper(substr($member->user->first_name, 0, 1)) }}</span>
                                    {{ $member->user->first_name }}
                                    {{ $member->user->last_name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="lt-auto__item">
                        <div class="lt-auto__key">Program</div>
                        <div class="lt-auto__val">{{ auth()->user()->program }}</div>
                    </div>

                    <div class="lt-auto__item">
                        <div class="lt-auto__key">Year &amp; Section</div>
                        <div class="lt-auto__val">{{ auth()->user()->year_section }}</div>
                    </div>

                    <div class="lt-auto__item">
                        <div class="lt-auto__key">Instructor / Adviser</div>
                        <div class="lt-auto__val">
                            {{ $groupRoom->classroom->instructor->first_name }}
                            {{ $groupRoom->classroom->instructor->last_name }}
                        </div>
                    </div>

                </div>
            </section>

            <div class="lt-form__actions">
                <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Submitting...">
                    Submit Capstone
                </button>

                <a href="{{ route('student.group-room.show', $groupRoom->id) }}" class="lt-btn lt-btn--ghost">
                    Cancel
                </a>
            </div>

        </form>

    </main>

</body>
</html>