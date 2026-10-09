<!DOCTYPE html>
<html>
<head>
    <title>Review Capstone</title>
</head>
<body>

    <h1>Review Capstone Metadata</h1>

    <hr>

    <h2>Capstone Information</h2>

    <p>
        <strong>Title:</strong><br>
        {{ $capstone->title }}
    </p>

    <p>
        <strong>Abstract:</strong><br>
        {{ $capstone->abstract }}
    </p>

    <p>
        <strong>Publication Date:</strong><br>
        {{ $capstone->publication_date?->format('F d, Y') }}
    </p>

    <hr>

    <h2>Authors</h2>

    <ul>
        @foreach($capstone->groupRoom->members as $member)
            <li>
                {{ $member->user->first_name }}
                {{ $member->user->last_name }}
            </li>
        @endforeach
    </ul>

    <p>
        <strong>Program:</strong><br>
        {{ $capstone->program }}
    </p>

    <p>
        <strong>Year & Section:</strong><br>
        {{ $capstone->year_section }}
    </p>

    <p>
        <strong>Instructor / Adviser:</strong><br>
        {{ $capstone->groupRoom->classroom->instructor->first_name }}
        {{ $capstone->groupRoom->classroom->instructor->last_name }}
    </p>

    <hr>

    <p>
        <strong>Submitted By:</strong><br>
        {{ $capstone->submitter->first_name }}
        {{ $capstone->submitter->last_name }}
    </p>

    <p>
        <strong>Status:</strong>
        {{ ucfirst(str_replace('_', ' ', $capstone->status)) }}
    </p>

    <br>

    @if($capstone->status === 'submitted')

        <hr>

        <h2>Instructor Review</h2>

        <form method="POST"
            action="{{ route('instructor.capstones.verify', $capstone->id) }}"
            style="display: inline;">
            @csrf

            <button type="submit">
                Verify Metadata
            </button>
        </form>

        <h3>Return for Correction</h3>

        <form method="POST"
            action="{{ route('instructor.capstones.return', $capstone->id) }}">

            @csrf

            <label for="return_reason">
                Reason for returning:
            </label>

            <br>

            <textarea
                id="return_reason"
                name="return_reason"
                rows="5"
                required
                placeholder="Explain what the student needs to correct..."
            ></textarea>

            <br><br>

            <button type="submit">
                Return for Correction
            </button>

        </form>

    @endif

    <a href="{{ route('instructor.capstones') }}">
        ← Back to Capstone Submissions
    </a>

</body>
</html>