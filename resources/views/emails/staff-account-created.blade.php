<!DOCTYPE html>
<html lang="en">
<body>
    <h1>Welcome to LogiTrack, {{ $firstName }}.</h1>
    <p>An administrator created your {{ $role === 'ojt' ? 'OJT metadata reviewer' : $role }} account.</p>
    <p>Use these credentials to sign in:</p>
    <ul>
        <li>Email: {{ $email }}</li>
        <li>Temporary password: {{ $temporaryPassword }}</li>
    </ul>
    <p><strong>Important:</strong> Change this temporary password immediately after signing in. Do not share it with anyone.</p>
</body>
</html>
