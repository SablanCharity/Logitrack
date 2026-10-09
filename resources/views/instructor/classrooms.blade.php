<!DOCTYPE html>
<html>
<head>
    <title>My Classrooms</title>
</head>

<body>

    <h1>My Classrooms</h1>

    <p>
        Instructor:
        {{ auth()->user()->first_name }}
        {{ auth()->user()->last_name }}
    </p>

    @if(session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    <p>
        <a href="{{ route('instructor.classrooms.create') }}">
            + Create Classroom
        </a>
    </p>

    @if($classrooms->count() > 0)

        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Classroom Name</th>
                    <th>Class Code</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                @foreach($classrooms as $classroom)

                    <tr>
                        <td>
                            {{ $classroom->classroom_name }}
                        </td>

                        <td>
                            <strong>
                                {{ $classroom->class_code }}
                            </strong>
                        </td>

                        <td>
                            {{ $classroom->created_at->format('M d, Y') }}
                        </td>

                        <td>
                            <a href="{{ route('instructor.classrooms.show', $classroom->id) }}">
                                View Classroom
                            </a>
                        </td>
                    </tr>

                @endforeach

            </tbody>
        </table>

    @else

        <p>
            You don't have any classrooms yet.
        </p>

    @endif

</body>
</html>