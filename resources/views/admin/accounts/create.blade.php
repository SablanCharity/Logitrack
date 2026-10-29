<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Staff Account &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    @include('components.site-navigation')

    <main class="lt-container lt-animate" style="max-width: 760px;">
        <div class="lt-page-head">
            <span class="lt-eyebrow">Admin &middot; Staff Management</span>
            <h1>Create Staff Account</h1>
            <p class="lt-page-head__sub">Create an instructor or OJT account. New staff accounts are approved immediately.</p>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status"><span class="lt-alert__icon">&#10003;</span><div>{{ session('success') }}</div></div>
        @endif
        @if($errors->any())
            <div class="lt-alert lt-alert--error" role="alert"><span class="lt-alert__icon">!</span><div>{{ $errors->first() }}</div></div>
        @endif

        <section class="lt-card">
            <form method="POST" action="{{ route('admin.accounts.store') }}" class="lt-form">
                @csrf
                <div class="lt-form__grid">
                    <div class="lt-field">
                        <label for="first_name" class="lt-label">First name</label>
                        <input id="first_name" name="first_name" value="{{ old('first_name') }}" class="lt-input" required>
                    </div>
                    <div class="lt-field">
                        <label for="last_name" class="lt-label">Last name</label>
                        <input id="last_name" name="last_name" value="{{ old('last_name') }}" class="lt-input" required>
                    </div>
                </div>
                <div class="lt-field">
                    <label for="email" class="lt-label">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="lt-input" required>
                </div>
                <div class="lt-field">
                    <label for="role" class="lt-label">Account type</label>
                    <select id="role" name="role" class="lt-input" required>
                        <option value="">Select account type</option>
                        <option value="instructor" @selected(old('role') === 'instructor')>Instructor</option>
                        <option value="ojt" @selected(old('role') === 'ojt')>OJT Metadata Reviewer</option>
                    </select>
                    <span class="lt-hint">OJTs may review and correct OCR metadata, but cannot approve records for the repository.</span>
                </div>
                <div class="lt-field">
                    <label for="program" class="lt-label">Program (optional)</label>
                    <input id="program" name="program" value="{{ old('program') }}" class="lt-input">
                </div>
                <span class="lt-hint">A secure temporary password will be generated and sent to the new staff member by email.</span>
                <button type="submit" class="lt-btn lt-btn--primary">Create Staff Account</button>
            </form>
        </section>
    </main>
</body>
</html>
