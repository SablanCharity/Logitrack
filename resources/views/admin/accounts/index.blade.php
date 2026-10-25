<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Accounts · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    <header class="lt-nav lt-nav--landing">
        <div class="lt-nav__inner">
            <a href="{{ route('admin.capstones') }}" class="lt-brand">
                <span class="lt-brand__mark">L</span>
                LogiTrack
            </a>
            <button type="button" class="lt-nav__toggle" data-nav-toggle="#nav-menu" aria-label="Toggle menu" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="lt-nav__menu" id="nav-menu">
                <nav class="lt-nav__links lt-nav__links--app lt-admin-nav" aria-label="Admin">
                    <a href="{{ route('dashboard') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Dashboard" aria-label="Dashboard"><span class="material-symbols-outlined">dashboard</span><span class="lt-admin-nav-label">Dashboard</span></a>
                    <a href="{{ route('repository.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Repository" aria-label="Repository"><span class="material-symbols-outlined">menu_book</span><span class="lt-admin-nav-label">Repository</span></a>
                    <a href="{{ route('admin.capstones') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Capstone Review" aria-label="Capstone Review"><span class="material-symbols-outlined">fact_check</span><span class="lt-admin-nav-label">Capstone Review</span></a>
                    <a href="{{ route('ocr.upload') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Upload Archived" aria-label="Upload Archived"><span class="material-symbols-outlined">upload_file</span><span class="lt-admin-nav-label">Upload Archived</span></a>
                    <a href="{{ route('admin.accounts.index') }}" class="lt-nav__link lt-admin-nav-link is-active" data-tooltip="Staff Accounts" aria-label="Staff Accounts"><span class="material-symbols-outlined">manage_accounts</span><span class="lt-admin-nav-label">Staff Accounts</span></a>
                    <a href="{{ route('admin.students.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Students" aria-label="Students"><span class="material-symbols-outlined">school</span><span class="lt-admin-nav-label">Students</span></a>
                </nav>
                <div class="lt-nav__actions">
                    <a href="{{ route('profile.edit') }}" class="lt-user-chip lt-user-chip--link" aria-label="Edit profile"><span class="lt-avatar">{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}</span>{{ auth()->user()->first_name }}</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="lt-btn lt-btn--outline-light lt-btn--sm">Log Out</button></form>
                </div>
            </div>
        </div>
    </header>
    <main class="lt-container lt-animate">
        <div class="lt-page-head">
            <span class="lt-eyebrow">Admin · Staff Management</span>
            <div class="lt-page-head__row">
                <div>
                    <h1>Staff Accounts</h1>
                    <p class="lt-page-head__sub">Manage administrator, instructor, and OJT accounts.</p>
                </div>
                <a href="{{ route('admin.accounts.create') }}" class="lt-btn lt-btn--primary">Create Account</a>
            </div>
        </div>
        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status"><span class="lt-alert__icon">✓</span><div>{{ session('success') }}</div></div>
        @endif
        <section class="lt-card">
            <div class="lt-card__head">
                <form method="GET" action="{{ route('admin.accounts.index') }}" class="lt-input-wrap" style="max-width: 360px;">
                    <span class="material-symbols-outlined lt-input-icon" aria-hidden="true">search</span>
                    <input type="search" name="q" value="{{ $search }}" class="lt-input" placeholder="Search staff accounts..." aria-label="Search staff accounts">
                </form>
                @if($search !== '' && $accounts->isEmpty())
                    <div class="lt-empty lt-mt"><div class="lt-empty__title">No match found</div><p>No staff account matches your search.</p></div>
                @endif
            </div>
            <div class="lt-table-wrap">
                <table class="lt-table" id="staff-accounts-table">
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @foreach($accounts as $account)
                            <tr>
                                <td>{{ $account->first_name }} {{ $account->last_name }}</td>
                                <td>{{ $account->email }}</td>
                                <td>{{ $account->role === 'ojt' ? 'OJT Metadata Reviewer' : ucfirst($account->role) }}</td>
                                <td><span class="lt-badge {{ $account->is_active ? 'lt-badge--approved' : 'lt-badge--rejected' }}">{{ $account->is_active ? 'Active' : 'Disabled' }}</span></td>
                                <td>
                                    <div class="lt-actions">
                                        <a href="{{ route('admin.accounts.edit', $account) }}" class="lt-btn lt-btn--outline lt-btn--sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.accounts.toggle-active', $account) }}" data-confirm="{{ $account->is_active ? 'Disable this account? The account can be activated again later.' : 'Activate this account?' }}" data-confirm-title="{{ $account->is_active ? 'Disable account' : 'Activate account' }}" data-confirm-button="{{ $account->is_active ? 'Disable' : 'Activate' }}" @if($account->is_active) data-confirm-danger @endif>@csrf @method('PATCH')<button type="submit" class="lt-btn lt-btn--outline lt-btn--sm">{{ $account->is_active ? 'Disable' : 'Activate' }}</button></form>
                                        <form method="POST" action="{{ route('admin.accounts.destroy', $account) }}" data-confirm="Delete this account permanently? It will be deactivated forever and cannot be reactivated." data-confirm-title="Delete account permanently" data-confirm-button="Delete permanently" data-confirm-danger>@csrf @method('DELETE')<button type="submit" class="lt-btn lt-btn--danger-ghost lt-btn--sm">Delete</button></form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
