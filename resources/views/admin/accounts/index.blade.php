<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Accounts &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    @include('components.site-navigation')
    <main class="lt-container lt-animate">
        <div class="lt-page-head">
            <span class="lt-eyebrow">Admin &middot; Staff Management</span>
            <div class="lt-page-head__row">
                <div>
                    <h1>Staff Accounts</h1>
                    <p class="lt-page-head__sub">Manage administrator, instructor, and OJT accounts.</p>
                </div>
                <a href="{{ route('admin.accounts.create') }}" class="lt-btn lt-btn--primary">Create Account</a>
            </div>
        </div>
        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status"><span class="lt-alert__icon">&#10003;</span><div>{{ session('success') }}</div></div>
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
