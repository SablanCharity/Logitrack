<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    <main class="lt-container lt-container--narrow lt-animate">
        <div class="lt-page-head">
            <span class="lt-eyebrow">Email Verification</span>
            <h1>Enter your verification code</h1>
            <p class="lt-page-head__sub">We sent a six-digit code to your school email address.</p>
        </div>

        @if($errors->any())
            <div class="lt-alert lt-alert--error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('registration.otp') }}" class="lt-form">
            @csrf
            <div class="lt-field">
                <label for="otp" class="lt-label">One-Time Password</label>
                <input id="otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" class="lt-input" required autofocus>
            </div>
            <button type="submit" class="lt-btn lt-btn--primary">Verify and activate account</button>
        </form>
    </main>
</body>
</html>
