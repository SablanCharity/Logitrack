@php
    $user = auth()->user();
    $role = $user->role;
    $isAdmin = $role === 'admin';
    $isOjt = $role === 'ojt';
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
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
        </button>

        <div class="lt-nav__menu" id="nav-menu">
            <nav class="lt-nav__links lt-nav__links--app {{ $isAdmin ? 'lt-admin-nav' : '' }}" aria-label="{{ ucfirst($role) }}">
                @if($isAdmin || $isOjt)
                    <a href="{{ route('dashboard') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" data-tooltip="Dashboard" aria-label="Dashboard">
                        <span class="material-symbols-outlined" aria-hidden="true">dashboard</span>
                        <span class="lt-admin-nav-label">Dashboard</span>
                    </a>

                    <a href="{{ route('repository.index') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('repository.*') ? 'is-active' : '' }}" data-tooltip="Repository" aria-label="Repository">
                        <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
                        <span class="lt-admin-nav-label">Repository</span>
                    </a>

                    <a href="{{ route('admin.student-masterlist.index') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('admin.student-masterlist.*') ? 'is-active' : '' }}" data-tooltip="Student Masterlist" aria-label="Student Masterlist">
                        <span class="material-symbols-outlined" aria-hidden="true">upload_file</span>
                        <span class="lt-admin-nav-label">Student Masterlist</span>
                    </a>

                    @if($isAdmin)
                        <a href="{{ route('admin.group-submissions.index') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('admin.group-submissions.*') ? 'is-active' : '' }}" data-tooltip="Submission Monitoring" aria-label="Submission Monitoring">
                            <svg class="lt-nav-icon" style="display:block;flex:0 0 22px;width:22px;height:22px;min-width:22px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 3v18h18"/>
                                <path d="m7 14 4-4 3 3 6-7"/>
                                <path d="M20 6v4h-4"/>
                            </svg>
                            <span class="lt-admin-nav-label">Submission Monitoring</span>
                        </a>

                        <a href="{{ route('admin.announcements.index') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('admin.announcements.*') ? 'is-active' : '' }}" data-tooltip="Announcements" aria-label="Announcements">
                            <svg class="lt-nav-icon" style="display:block;flex:0 0 22px;width:22px;height:22px;min-width:22px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 11v2a2 2 0 0 0 2 2h2l10 5V4L7 9H5a2 2 0 0 0-2 2Z"/>
                                <path d="M7 15l2 6h3l-2-5"/>
                                <path d="M17 9a3 3 0 0 1 0 6"/>
                            </svg>
                            <span class="lt-admin-nav-label">Announcements</span>
                        </a>
                    @endif

                    @if($isAdmin || $isOjt)
                        <a href="{{ route('ocr.upload') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('ocr.upload') ? 'is-active' : '' }}" data-tooltip="Upload Archived" aria-label="Upload Archived">
                            <span class="material-symbols-outlined" aria-hidden="true">upload_file</span>
                            <span class="lt-admin-nav-label">Upload Archived</span>
                        </a>
                    @endif

                    @if($isAdmin)
                        <a href="{{ route('admin.accounts.index') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('admin.accounts.*') ? 'is-active' : '' }}" data-tooltip="Instructor Accounts" aria-label="Instructor Accounts">
                            <span class="material-symbols-outlined" aria-hidden="true">manage_accounts</span>
                            <span class="lt-admin-nav-label">Instructor Accounts</span>
                        </a>

                        <a href="{{ route('admin.students.index') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('admin.students.*') ? 'is-active' : '' }}" data-tooltip="Student Accounts" aria-label="Student Accounts">
                            <span class="material-symbols-outlined" aria-hidden="true">school</span>
                            <span class="lt-admin-nav-label">Student Accounts</span>
                        </a>
                    @endif

                @elseif($isInstructor)
                    <a href="{{ route('dashboard') }}" class="lt-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
                    
                    </a>
                    <a href="{{ route('instructor.repository.index') }}" class="lt-nav__link {{ request()->routeIs('instructor.repository.*') ? 'is-active' : '' }}">Repository</a>
                    <a href="{{ route('similarity.index') }}" class="lt-nav__link {{ request()->routeIs('similarity.*') ? 'is-active' : '' }}">Similarity Checker</a>

                @elseif($isStudent)
                    <a href="{{ route('dashboard') }}" class="lt-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
                    <a href="{{ route('repository.index') }}" class="lt-nav__link {{ request()->routeIs('repository.*') ? 'is-active' : '' }}">Repository</a>
                    <a href="{{ route('student.submissions.create') }}" class="lt-nav__link {{ request()->routeIs('student.submissions.*', 'student.group-submissions.*') ? 'is-active' : '' }}">Capstone Submission</a>
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
