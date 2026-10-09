<!DOCTYPE html>
<html>
<head>
    <title>{{ $capstone->title }} - LogiTrack Repository</title>
</head>
<body>

    <h1>LogiTrack Repository</h1>

    <p>
        <a href="{{ route('repository.index') }}">
            ← Back to Repository
        </a>
    </p>

    <hr>

    <h2>{{ $capstone->title }}</h2>

    <h3>Capstone Information</h3>

    <p>
        <strong>Program:</strong>
        {{ $capstone->program }}
    </p>

    <p>
        <strong>Year & Section:</strong>
        {{ $capstone->year_section ?? 'N/A' }}
    </p>

    <p>
        <strong>Publication Date:</strong>
        {{ optional($capstone->publication_date)->format('F d, Y') ?? 'N/A' }}
    </p>

    <p>
        <strong>Instructor / Adviser:</strong>
        {{ $capstone->groupRoom->classroom->instructor->first_name }}
        {{ $capstone->groupRoom->classroom->instructor->last_name }}
    </p>

    <p>
        <strong>Group:</strong>
        {{ $capstone->groupRoom->group_name }}
    </p>

    <hr>

    <h3>Authors</h3>

    <ul>
        @foreach($capstone->groupRoom->members as $member)
            <li>
                {{ $member->user->first_name }}
                {{ $member->user->last_name }}

                @if($member->is_leader)
                    <strong>(Leader)</strong>
                @endif
            </li>
        @endforeach
    </ul>

    <hr>

    <h3>Abstract</h3>

    <p>
        {{ $capstone->abstract }}
    </p>

    <hr>

    <p>
        <strong>Repository Status:</strong>
        Approved
    </p>

    <p>
        This capstone has been reviewed and approved for inclusion
        in the LogiTrack repository.
    </p>

</body>
</html>