<!DOCTYPE html>
<html>
<head>
    <title>Capstone Submissions</title>
</head>
<body>

    <h1>Capstone Submissions</h1>

    <p>
        Instructor:
        <strong>
            {{ auth()->user()->first_name }}
            {{ auth()->user()->last_name }}
        </strong>
    </p>

    <hr>

    @if(session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    @if(session('error'))
        <p style="color: red;">
            {{ session('error') }}
        </p>
    @endif

    @if($capstones->count() > 0)

        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Group</th>
                    <th>Classroom</th>
                    <th>Submitted By</th>
                    <th>Submitted At</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($capstones as $capstone)
                    <tr>
                        <td>
                            <a href="{{ route('instructor.capstones.review', $capstone->id) }}">
                                {{ $capstone->title }}
                            </a>
                        </td>

                        <td>
                            {{ $capstone->groupRoom->group_name }}
                        </td>

                        <td>
                            {{ $capstone->groupRoom->classroom->classroom_name }}
                        </td>

                        <td>
                            {{ $capstone->submitter->first_name }}
                            {{ $capstone->submitter->last_name }}
                        </td>

                        <td>
                            {{ $capstone->submitted_at?->format('M d, Y h:i A') }}
                        </td>

                        <td>
                            {{ ucfirst(str_replace('_', ' ', $capstone->status)) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @else

        <p>
            There are no capstone submissions waiting for review.
        </p>

    @endif

</body>
</html>