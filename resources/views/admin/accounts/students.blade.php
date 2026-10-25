<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Accounts · LogiTrack</title>
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
                <a href="{{ route('admin.accounts.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Staff Accounts" aria-label="Staff Accounts"><span class="material-symbols-outlined">manage_accounts</span><span class="lt-admin-nav-label">Staff Accounts</span></a>
                <a href="{{ route('admin.students.index') }}" class="lt-nav__link lt-admin-nav-link is-active" data-tooltip="Students" aria-label="Students"><span class="material-symbols-outlined">school</span><span class="lt-admin-nav-label">Students</span></a>
            </nav>
            <div class="lt-nav__actions">
                <a href="{{ route('profile.edit') }}" class="lt-user-chip lt-user-chip--link" aria-label="Edit profile"><span class="lt-avatar">{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}</span>{{ auth()->user()->first_name }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="lt-btn lt-btn--outline-light lt-btn--sm">Log Out</button></form>
            </div>
        </div>
    </header>
    <main class="lt-container lt-animate">
        <div class="lt-page-head">
            <span class="lt-eyebrow">Admin · Account Management</span>
            <h1>Student Accounts</h1>
            <p class="lt-page-head__sub">View student details, advisers, and account status.</p>
        </div>
        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status"><span class="lt-alert__icon">✓</span><div>{{ session('success') }}</div></div>
        @endif
        <section class="lt-card">
            <div class="lt-card__head">
                <form method="GET" action="{{ route('admin.students.index') }}" class="lt-input-wrap" style="max-width: 360px;">
                    <span class="material-symbols-outlined lt-input-icon" aria-hidden="true">search</span>
                    <input type="search" name="q" value="{{ $search }}" class="lt-input" placeholder="Search student accounts..." aria-label="Search student accounts">
                </form>
                @if($search !== '' && $students->isEmpty())
                    <div class="lt-empty lt-mt"><div class="lt-empty__title">No match found</div><p>No student account matches your search.</p></div>
                @endif
            </div>
            <div class="lt-table-wrap">
                <table class="lt-table" id="student-accounts-table">
                    <thead><tr><th>Name</th><th>Email</th><th>Program</th><th>Year &amp; Section</th><th>Adviser</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($students as $student)
                            @php($membership = $student->classroomMemberships->first())
                            <tr>
                                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                <td>{{ $student->email }}</td>
                                <td>{{ $student->program ?: '—' }}</td>
                                <td>{{ $student->year_section ?: '—' }}</td>
                                <td>{{ $membership?->classroom?->instructor?->first_name }} {{ $membership?->classroom?->instructor?->last_name ?: 'Not assigned' }}</td>
                                <td><span class="lt-badge {{ $student->is_active ? 'lt-badge--approved' : 'lt-badge--rejected' }}">{{ $student->is_active ? ($student->is_approved ? 'Active' : 'Pending approval') : 'Disabled' }}</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <form method="POST" action="{{ route('admin.accounts.toggle-active', $student) }}" data-confirm="{{ $student->is_active ? 'Disable this account? The account can be activated again later.' : 'Activate this account?' }}" data-confirm-title="{{ $student->is_active ? 'Disable account' : 'Activate account' }}" data-confirm-button="{{ $student->is_active ? 'Disable' : 'Activate' }}" @if($student->is_active) data-confirm-danger @endif>@csrf @method('PATCH')<button type="submit" class="lt-btn lt-btn--outline lt-btn--sm">{{ $student->is_active ? 'Disable' : 'Activate' }}</button></form>
                                        <form method="POST" action="{{ route('admin.accounts.destroy', $student) }}" data-confirm="Delete this student account permanently? It will be deactivated forever and cannot be reactivated." data-confirm-title="Delete student account permanently" data-confirm-button="Delete permanently" data-confirm-danger>@csrf @method('DELETE')<button type="submit" class="lt-btn lt-btn--danger-ghost lt-btn--sm">Delete</button></form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7">No student accounts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
