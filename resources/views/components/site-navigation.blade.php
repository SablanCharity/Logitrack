@php
    $user = auth()->user();
    $role = $user->role;
    $isAdmin = $role === 'admin';
    $isInstructor = $role === 'instructor';
    $isStudent = $role === 'student';
@endphp

<header class="lt-nav lt-nav--landing">
    <div class="lt-nav__inner">
        <a href="{{ route('dashboard') }}" class="lt-brand">
            <span class="lt-brand__mark">L</span>
            LogiTrack
        </a>

        <button
            type="button"
            class="lt-nav__toggle"
            data-nav-toggle="#nav-menu"
            aria-label="Toggle menu"
            aria-expanded="false"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
        </button>

        <div class="lt-nav__menu" id="nav-menu">
            <nav class="lt-nav__links lt-nav__links--app {{ $isAdmin ? 'lt-admin-nav' : '' }}" aria-label="{{ ucfirst($role) }}">
                @if($isAdmin)
                    <a href="{{ route('dashboard') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" data-tooltip="Dashboard" aria-label="Dashboard">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span class="lt-admin-nav-label">Dashboard</span>
                    </a>
                    <a href="{{ route('repository.index') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('repository.*') ? 'is-active' : '' }}" data-tooltip="Repository" aria-label="Repository">
                        <span class="material-symbols-outlined">menu_book</span>
                        <span class="lt-admin-nav-label">Repository</span>
                    </a>
                    <a href="{{ route('admin.capstones') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('admin.capstones*', 'admin.capstone.*') ? 'is-active' : '' }}" data-tooltip="Capstone Review" aria-label="Capstone Review">
                        <span class="material-symbols-outlined">fact_check</span>
                        <span class="lt-admin-nav-label">Capstone Review</span>
                    </a>
                    <a href="{{ route('ocr.upload') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('ocr.*') ? 'is-active' : '' }}" data-tooltip="Upload Archived" aria-label="Upload Archived">
                        <span class="material-symbols-outlined">upload_file</span>
                        <span class="lt-admin-nav-label">Upload Archived</span>
                    </a>
                    <a href="{{ route('admin.accounts.index') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('admin.accounts.*') ? 'is-active' : '' }}" data-tooltip="Staff Accounts" aria-label="Staff Accounts">
                        <span class="material-symbols-outlined">manage_accounts</span>
                        <span class="lt-admin-nav-label">Staff Accounts</span>
                    </a>
                    <a href="{{ route('admin.students.index') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('admin.students.*') ? 'is-active' : '' }}" data-tooltip="Student Accounts" aria-label="Student Accounts">
                        <span class="material-symbols-outlined">school</span>
                        <span class="lt-admin-nav-label">Student Accounts</span>
                    </a>
                @elseif($isInstructor)
                    <a href="{{ route('dashboard') }}" class="lt-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
                    <a href="{{ route('instructor.repository.index') }}" class="lt-nav__link {{ request()->routeIs('instructor.repository.*') ? 'is-active' : '' }}">Repository</a>
                    <a href="{{ route('instructor.classrooms') }}" class="lt-nav__link {{ request()->routeIs('instructor.classrooms*', 'instructor.classroom.*') ? 'is-active' : '' }}">Classroom</a>
                    <a href="{{ route('instructor.capstones') }}" class="lt-nav__link {{ request()->routeIs('instructor.capstones*', 'instructor.capstone.*') ? 'is-active' : '' }}">Capstone Submissions</a>
                    <a href="{{ route('instructor.students') }}" class="lt-nav__link {{ request()->routeIs('instructor.students*') ? 'is-active' : '' }}">Pending Students</a>
                @elseif($isStudent)
                    <a href="{{ route('dashboard') }}" class="lt-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
                    <a href="{{ route('repository.index') }}" class="lt-nav__link {{ request()->routeIs('repository.*') ? 'is-active' : '' }}">Repository</a>
                    <a href="{{ route('student.classroom.redirect') }}" class="lt-nav__link {{ request()->routeIs('student.group-rooms.*', 'student.group-room.*', 'student.capstone.*', 'student.pending') ? 'is-active' : '' }}">Classroom</a>
                    <a href="{{ route('similarity.index') }}" class="lt-nav__link {{ request()->routeIs('similarity.*') ? 'is-active' : '' }}">Similarity Checker</a>
                @endif
            </nav>

            <div class="lt-nav__actions">
                <a href="{{ route('profile.edit') }}" class="lt-user-chip lt-user-chip--link" aria-label="Edit profile">
                    <span class="lt-avatar">{{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}</span>
                    {{ $user->first_name ?? 'User' }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="lt-btn lt-btn--outline-light lt-btn--sm">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</header>
