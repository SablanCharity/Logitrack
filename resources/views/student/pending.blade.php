<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pending Approval · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
    
</head>

<body class="lt-body">

    <div class="lt-center-wrap">

        <main class="lt-pending lt-animate">

            <div class="lt-pending__top">
                <span class="lt-brand">
                    <span class="lt-brand__mark">L</span>
                    LogiTrack
                </span>
            </div>

            <div class="lt-pending__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 7v5l3 2"></path>
                </svg>
            </div>

            <div class="lt-pending__body">

                <h1>Account Pending Approval</h1>

                <p class="lt-pending__hello">
                    Hello, {{ auth()->user()->first_name }}!
                </p>

                <p>
                    Your account is currently waiting for your instructor/adviser
                    to approve your classroom membership.
                </p>

                <div class="lt-steps" aria-label="Approval progress">
                    <div class="lt-step is-done">
                        <span class="lt-step__dot">✓</span>
                        Account registered
                    </div>
                    <div class="lt-step is-current">
                        <span class="lt-step__dot">2</span>
                        Waiting for adviser approval
                    </div>
                    <div class="lt-step">
                        <span class="lt-step__dot">3</span>
                        Access your classroom
                    </div>
                </div>

                <p>
                    Please wait until your instructor approves your account.
                </p>

                <form method="POST" action="{{ route('logout') }}" class="lt-mt-lg">
                    @csrf
                    <button type="submit" class="lt-btn lt-btn--ghost">Log Out</button>
                </form>

            </div>

        </main>

    </div>

</body>
</html>