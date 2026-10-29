<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Staff Account &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    @include('components.site-navigation')
    <main class="lt-container lt-animate" style="max-width: 760px;">
        <a href="{{ route('admin.accounts.index') }}" class="lt-back">&larr; Back to Staff Accounts</a>
        <div class="lt-page-head">
            <span class="lt-eyebrow">Admin &middot; Staff Management</span>
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
