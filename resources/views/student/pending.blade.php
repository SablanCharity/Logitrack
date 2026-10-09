<!DOCTYPE html>
<html>
<head>
    <title>Pending Approval</title>
</head>

<body>

    <h1>Account Pending Approval</h1>

    <p>
        Hello, {{ auth()->user()->first_name }}!
    </p>

    <p>
        Your account is currently waiting for your instructor/adviser
        to approve your classroom membership.
    </p>

    <p>
        Please wait until your instructor approves your account.
    </p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Log Out</button>
    </form>

</body>
</html>