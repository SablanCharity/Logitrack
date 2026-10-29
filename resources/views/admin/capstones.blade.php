<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Capstone Approval &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])

</head>

<body class="lt-body">

    @include('components.site-navigation')

    <main class="lt-container lt-animate">

        <div class="lt-page-head">
            <span class="lt-eyebrow">
                Admin &middot; {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
            </span>
            <h1>Capstone Approval</h1>
            <p class="lt-page-head__sub">Capstones verified by instructors, waiting for final approval into the repository.</p>
            <form method="POST" action="{{ route('admin.capstones.submission-window') }}" class="lt-mt" data-confirm="{{ $submissionWindow->is_open ? 'Close capstone submissions for students?' : 'Open capstone submissions for students?' }}" data-confirm-title="{{ $submissionWindow->is_open ? 'Close submissions' : 'Open submissions' }}" data-confirm-button="{{ $submissionWindow->is_open ? 'Close submissions' : 'Open submissions' }}" @if($submissionWindow->is_open) data-confirm-danger @endif>
                @csrf
                @method('PATCH')
                <button type="submit" class="lt-btn {{ $submissionWindow->is_open ? 'lt-btn--danger-ghost' : 'lt-btn--green' }}">
                    {{ $submissionWindow->is_open ? 'Close Capstone Submissions' : 'Open Capstone Submissions' }}
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status" data-autohide="6000">
                <span class="lt-alert__icon">&#10003;</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="lt-alert lt-alert--error" role="alert">
                <span class="lt-alert__icon">!</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if($capstones->count() > 0)

            <div class="lt-stats">
                <div class="lt-stat">
                    <div class="lt-stat__num">{{ $capstones->count() }}</div>
                    <div class="lt-stat__label">Awaiting approval</div>
                </div>
            </div>

            <div class="lt-card__head">
                <input
                    type="search"
                    class="lt-input"
                    style="max-width: 320px;"
                    placeholder="Search title, group, adviser..."
                    aria-label="Search capstones for approval"
                    data-table-filter="#approval-table"
                    data-filter-empty="#approval-filter-empty"
                >
            </div>

            <div class="lt-table-wrap">

                <table class="lt-table lt-table--compact" id="approval-table">

                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Group</th>
                            <th>Classroom</th>
                            <th>Instructor / Adviser</th>
                            <th>Verified By</th>
                            <th>Verified At</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($capstones as $capstone)

                            @php
                                $badge = match(true) {
                                    in_array($capstone->status, ['returned_by_instructor', 'returned_by_admin']) => 'lt-badge--returned',
                                    $capstone->status === 'admin_approved' => 'lt-badge--approved',
                                    $capstone->status === 'instructor_verified' => 'lt-badge--verified',
                                    $capstone->status === 'submitted' => 'lt-badge--submitted',
                                    default => 'lt-badge--draft',
                                };
                            @endphp

                            <tr>

                                <td style="min-width: 190px;">
                                    {{-- Fixed route name here from admin.capstones.review to admin.capstone.review --}}
                                    <a href="{{ route('admin.capstone.review', $capstone->id) }}" class="lt-table__title">
                                        {{ $capstone->title }}
                                    </a>
                                </td>

                                <td>
                                    {{ optional($capstone->groupRoom)->group_name }}
                                </td>

                                <td>
                                    {{ optional(optional($capstone->groupRoom)->classroom)->name ?? optional(optional($capstone->groupRoom)->classroom)->classroom_name }}
                                </td>

                                <td>
                                    {{ optional(optional(optional($capstone->groupRoom)->classroom)->instructor)->first_name }}
                                    {{ optional(optional(optional($capstone->groupRoom)->classroom)->instructor)->last_name }}
                                </td>

                                <td>
                                    {{ optional($capstone->verifier)->first_name }}
                                    {{ optional($capstone->verifier)->last_name }}
                                </td>

                                <td class="lt-muted lt-nowrap">
                                    {{ $capstone->verified_at?->format('M d, Y h:i A') }}
                                </td>

                                <td>
                                    <span class="lt-badge {{ $badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $capstone->status)) }}
                                    </span>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div id="approval-filter-empty" class="lt-empty lt-hidden lt-mt">
                <div class="lt-empty__title">No matching capstone</div>
                <p>Try a different search.</p>
            </div>

        @else

            <div class="lt-empty">
                <div class="lt-empty__icon">&#10003;</div>
                <div class="lt-empty__title">Nothing to approve</div>
                <p>There are no instructor-verified capstones waiting for admin approval.</p>
            </div>

        @endif

    </main>

</body>
</html>