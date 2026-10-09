<!DOCTYPE html>
<html>
<head>
    <title>Review Capstone</title>
</head>
<body>

    <h1>Admin Review</h1>

    <p>
        <strong>Final Repository Approval</strong>
    </p>

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

    <h2>Review Information</h2>

    <p>
        <strong>Submitted By:</strong><br>
        {{ $capstone->submitter->first_name }}
        {{ $capstone->submitter->last_name }}
    </p>

    <p>
        <strong>Verified By Instructor:</strong><br>
        {{ $capstone->verifier->first_name }}
        {{ $capstone->verifier->last_name }}
    </p>

    <p>
        <strong>Verified At:</strong><br>
        {{ $capstone->verified_at?->format('F d, Y h:i A') }}
    </p>

    <p>
        <strong>Status:</strong>
        {{ ucfirst(str_replace('_', ' ', $capstone->status)) }}
    </p>

    <hr>

    <h2>Admin Action</h2>

        <p>
            Review the metadata carefully before approving this capstone
            for inclusion in the repository.
        </p>

        <h3>Approve</h3>

        <form method="POST"
            action="{{ route('admin.capstones.approve', $capstone->id) }}">

            @csrf

            <button type="submit">
                Approve for Repository
            </button>

        </form>

        <hr>

        <h3>Return for Correction</h3>

        <form method="POST"
            action="{{ route('admin.capstones.return', $capstone->id) }}">

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
                maxlength="1000"
                placeholder="Explain what the student needs to correct..."
            ></textarea>

            <br><br>

            <button type="submit">
                Return for Correction
            </button>

        </form>

    <br>

    <a href="{{ route('admin.capstones') }}">
        ← Back to Capstone Approval
    </a>

</body>
</html>