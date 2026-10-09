<!DOCTYPE html>
<html>
<head>
    <title>LogiTrack Repository</title>
</head>
<body>

    <h1>LogiTrack Repository</h1>

    <p>
        Browse approved capstone projects in the repository.
    </p>

    <hr>

    <form method="GET" action="{{ route('repository.index') }}">

        <label for="search">
            Search Capstone Projects
        </label>

        <br>

        <input
            type="text"
            id="search"
            name="search"
            value="{{ $search }}"
            placeholder="Search by title, abstract, program..."
        >

        <button type="submit">
            Search
        </button>

        @if($search)
            <a href="{{ route('repository.index') }}">
                Clear
            </a>
        @endif

    </form>

    <hr>

    @if($capstones->count() > 0)

        @foreach($capstones as $capstone)

            <div style="margin-bottom: 30px;">

                <h2>
                    {{ $capstone->title }}
                </h2>

                <p>
                    <strong>Program:</strong>
                    {{ $capstone->program }}
                </p>

                <p>
                    <strong>Year & Section:</strong>
                    {{ $capstone->year_section ?? 'N/A' }}
                </p>

                <p>
                    <strong>Authors:</strong>
                </p>

                <ul>
                    @foreach($capstone->groupRoom->members as $member)
                        <li>
                            {{ $member->user->first_name }}
                            {{ $member->user->last_name }}
                        </li>
                    @endforeach
                </ul>

                <p>
                    <strong>Instructor / Adviser:</strong>
                    {{ $capstone->groupRoom->classroom->instructor->first_name }}
                    {{ $capstone->groupRoom->classroom->instructor->last_name }}
                </p>

                <p>
                    <strong>Publication Date:</strong>
                    {{ optional($capstone->publication_date)->format('F d, Y') ?? 'N/A' }}
                </p>

                <p>
                    <strong>Abstract:</strong>
                </p>

                <p>
                    {{ \Illuminate\Support\Str::limit($capstone->abstract, 250) }}
                </p>

                <p>
                    <a href="{{ route('repository.show', $capstone->id) }}">
                        View Details
                    </a>
                </p>

                <p>
                    <strong>Repository Status:</strong>
                    Approved
                </p>

                <hr>

            </div>

        @endforeach

        <div>
            {{ $capstones->withQueryString()->links() }}
        </div>

    @else

        <p>
            No approved capstone projects are currently available
            in the repository.
        </p>

    @endif

    <p>
        <a href="{{ route('dashboard') }}">
            Back to Dashboard
        </a>
    </p>

</body>
</html>