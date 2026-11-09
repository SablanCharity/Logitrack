<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Saved Archived Capstones &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>

<body class="lt-body">
    @include('components.site-navigation')

    <main class="lt-container lt-animate">
        <div class="lt-page-head">
            <span class="lt-eyebrow">Admin &middot; Archived Capstones</span>
            <h1>Saved Corrections</h1>
            <p class="lt-page-head__sub">
                Review corrected OCR metadata and approve archived capstones for publication in the repository.
            </p>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status">
                <span class="lt-alert__icon">&#10003;</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($archivedCapstones->isEmpty())
            <div class="lt-empty">
                <div class="lt-empty__icon">&#10003;</div>
                <div class="lt-empty__title">No saved corrections</div>
                <p>Archived capstones corrected by an OJT or administrator will appear here.</p>
            </div>
        @else
            <div class="lt-table-wrap">
                <table class="lt-table lt-table--compact">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Program</th>
                            <th>Last Updated</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedCapstones as $archivedCapstone)
                            <tr>
                                <td>
                                    <a href="{{ route('ocr.review', $archivedCapstone) }}" class="lt-table__title">
                                        {{ $archivedCapstone->title ?: 'Untitled archived capstone' }}
                                    </a>
                                </td>
                                <td>{{ $archivedCapstone->program ?: '—' }}</td>
                                <td class="lt-muted lt-nowrap">
                                    {{ $archivedCapstone->updated_at?->format('M d, Y h:i A') }}
                                </td>
                                <td>
                                    <span class="lt-badge {{ $archivedCapstone->status === 'reviewed' ? 'lt-badge--verified' : 'lt-badge--submitted' }}">
                                        {{ ucfirst($archivedCapstone->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('ocr.review', $archivedCapstone) }}" class="lt-btn lt-btn--outline lt-btn--sm">
                                        Review &amp; Approve
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </main>
</body>
</html>
