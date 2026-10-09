<!DOCTYPE html>
<html>
<head>
    <title>Pending Students</title>
</head>

<body>

    <h1>Pending Students</h1>

    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <p>Instructor: {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>

    @if($students->count() > 0)

        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Program</th>
                    <th>Year & Section</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($students as $student)
                    <tr>
                        <td>
                            {{ $student->first_name }}
                            {{ $student->last_name }}
                        </td>

                        <td>{{ $student->email }}</td>

                        <td>{{ $student->program }}</td>

                        <td>{{ $student->year_section }}</td>

                        <td>
                            <form method="POST" action="{{ route('instructor.students.approve', $student->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit">Approve</button>
                            </form>

                            <button type="button">Reject</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @else

        <p>No pending students.</p>

    @endif

</body>
</html>