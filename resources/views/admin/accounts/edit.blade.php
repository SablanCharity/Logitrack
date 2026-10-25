<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Staff Account · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    <header class="lt-nav lt-nav--landing">
        <div class="lt-nav__inner">
            <a href="{{ route('admin.accounts.index') }}" class="lt-brand"><span class="lt-brand__mark">L</span>LogiTrack</a>
            <nav class="lt-nav__links lt-nav__links--app lt-admin-nav" aria-label="Admin">
                <a href="{{ route('dashboard') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Dashboard" aria-label="Dashboard"><span class="material-symbols-outlined">dashboard</span><span class="lt-admin-nav-label">Dashboard</span></a>
                <a href="{{ route('repository.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Repository" aria-label="Repository"><span class="material-symbols-outlined">menu_book</span><span class="lt-admin-nav-label">Repository</span></a>
                <a href="{{ route('admin.capstones') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Capstone Review" aria-label="Capstone Review"><span class="material-symbols-outlined">fact_check</span><span class="lt-admin-nav-label">Capstone Review</span></a>
                <a href="{{ route('ocr.upload') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Upload Archived" aria-label="Upload Archived"><span class="material-symbols-outlined">upload_file</span><span class="lt-admin-nav-label">Upload Archived</span></a>
                <a href="{{ route('admin.accounts.index') }}" class="lt-nav__link lt-admin-nav-link is-active" data-tooltip="Staff Accounts" aria-label="Staff Accounts"><span class="material-symbols-outlined">manage_accounts</span><span class="lt-admin-nav-label">Staff Accounts</span></a>
                <a href="{{ route('admin.students.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Student Accounts" aria-label="Student Accounts"><span class="material-symbols-outlined">school</span><span class="lt-admin-nav-label">Student Accounts</span></a>
                <a href="{{ route('admin.students.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Students" aria-label="Students"><span class="material-symbols-outlined">school</span><span class="lt-admin-nav-label">Students</span></a>
            </nav>
            <div class="lt-nav__actions">
                <a href="{{ route('profile.edit') }}" class="lt-user-chip lt-user-chip--link" aria-label="Edit profile"><span class="lt-avatar">{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}</span>{{ auth()->user()->first_name }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="lt-btn lt-btn--outline-light lt-btn--sm">Log Out</button></form>
            </div>
        </div>
    </header>
    <main class="lt-container lt-animate" style="max-width: 760px;">
        <a href="{{ route('admin.accounts.index') }}" class="lt-back">⟵ Back to Staff Accounts</a>
        <div class="lt-page-head">
            <span class="lt-eyebrow">Admin · Staff Management</span>
            <h1>Edit Staff Account</h1>
            <p class="lt-page-head__sub">Update the staff member's name, email, or program.</p>
        </div>
        @if($errors->any())
            <div class="lt-alert lt-alert--error" role="alert"><span class="lt-alert__icon">!</span><div>{{ $errors->first() }}</div></div>
        @endif
        <section class="lt-card">
            <form method="POST" action="{{ route('admin.accounts.update', $user) }}" class="lt-form">
                @csrf
                @method('PUT')
                <div class="lt-form__grid">
                    <div class="lt-field"><label for="first_name" class="lt-label">First name</label><input id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" class="lt-input" required></div>
                    <div class="lt-field"><label for="last_name" class="lt-label">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="lt-input" required></div>
                </div>
                <div class="lt-field"><label for="email" class="lt-label">Email address</label><input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="lt-input" required><span class="lt-hint">Changing the email sends a new temporary password to the new address.</span></div>
                <div class="lt-field"><label for="program" class="lt-label">Program (optional)</label><input id="program" name="program" value="{{ old('program', $user->program) }}" class="lt-input"></div>
                <button type="submit" class="lt-btn lt-btn--primary">Save Account</button>
            </form>
        </section>
    </main>
</body>
</html>
