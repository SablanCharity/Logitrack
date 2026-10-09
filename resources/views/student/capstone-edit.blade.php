<!DOCTYPE html>
<html>
<head>
    <title>Edit Capstone</title>
</head>
<body>

    <h1>Edit Capstone</h1>

    <p>
        Classroom:
        <strong>{{ $groupRoom->classroom->classroom_name }}</strong>
    </p>

    <p>
        Group:
        <strong>{{ $groupRoom->group_name }}</strong>
    </p>

    <hr>

    @if($errors->any())
        <div>
            <strong>Please fix the following:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <p style="color: red;">
            {{ session('error') }}
        </p>
    @endif

    <h2>Correction Required</h2>

    @if($groupRoom->capstoneProject->return_reason)
        <p>
            <strong>Reason for Return:</strong>
        </p>

        <p>
            {{ $groupRoom->capstoneProject->return_reason }}
        </p>
    @endif

    <hr>

    <h2>Capstone Information</h2>

    <form method="POST"
          action="{{ route('student.capstone.update', $groupRoom->id) }}">

        @csrf
        @method('PUT')

        <div>
            <label for="title">Capstone Title</label><br>

            <input
                type="text"
                id="title"
                name="title"
                value="{{ old('title', $groupRoom->capstoneProject->title) }}"
                maxlength="255"
                required
            >
        </div>

        <br>

        <div>
            <label for="abstract">Abstract</label><br>

            <textarea
                id="abstract"
                name="abstract"
                rows="10"
                required
            >{{ old('abstract', $groupRoom->capstoneProject->abstract) }}</textarea>
        </div>

        <br>

        <div>
            <label for="publication_date">
                Publication Date
            </label>
            <br>

            <input
                type="date"
                id="publication_date"
                name="publication_date"
                value="{{ old(
                    'publication_date',
                    optional($groupRoom->capstoneProject->publication_date)->format('Y-m-d')
                ) }}"
                required
            >
        </div>

        <br>

        <h3>Automatically Retrieved Information</h3>

        <p><strong>Authors:</strong></p>

        <ul>
            @foreach($groupRoom->members as $member)
                <li>
                    {{ $member->user->first_name }}
                    {{ $member->user->last_name }}

                    @if($member->is_leader)
                        <strong>(Leader)</strong>
                    @endif
                </li>
            @endforeach
        </ul>

        <p>
            <strong>Program:</strong>
            {{ auth()->user()->program }}
        </p>

        <p>
            <strong>Year & Section:</strong>
            {{ auth()->user()->year_section }}
        </p>

        <p>
            <strong>Instructor / Adviser:</strong>
            {{ $groupRoom->classroom->instructor->first_name }}
            {{ $groupRoom->classroom->instructor->last_name }}
        </p>

        <br>

        <button type="submit">
            Resubmit Capstone
        </button>

        <a href="{{ route('student.group-room.show', $groupRoom->id) }}">
            Cancel
        </a>

    </form>

</body>
</html>