<!DOCTYPE html>
<html>
<head>
    <title>{{ $classroom->classroom_name }}</title>
</head>

<body>

    <h1>{{ $classroom->classroom_name }}</h1>

    <p>
        Class Code:
        <strong>{{ $classroom->class_code }}</strong>
    </p>

    <p>
        Instructor:
        {{ auth()->user()->first_name }}
        {{ auth()->user()->last_name }}
    </p>

    <hr>

    <h2>Students</h2>

    @if($classroom->members->count() > 0)

        <table border="1" cellpadding="10">

            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Program</th>
                    <th>Year & Section</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                @foreach($classroom->members as $member)

                    <tr>

                        <td>
                            {{ $member->user->first_name }}
                            {{ $member->user->last_name }}
                        </td>

                        <td>
                            {{ $member->user->email }}
                        </td>

                        <td>
                            {{ $member->user->program }}
                        </td>

                        <td>
                            {{ $member->user->year_section }}
                        </td>

                        <td>

                            {{ ucfirst($member->status) }}

                            @if($member->status === 'pending')

                                <form
                                    method="POST"
                                    action="{{ route('instructor.students.approve', $member->user_id) }}"
                                    style="display: inline;"
                                >

                                    @csrf

                                    <button type="submit">
                                        Approve
                                    </button>

                                </form>

                            @endif

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <p>
            No students have joined this classroom yet.
        </p>

    @endif

    <br>

    <a href="{{ route('instructor.classrooms') }}">
        ← Back to Classrooms
    </a>

</body>
</html>