<!DOCTYPE html>
<html>
<head>
    <title>{{ $classroom->classroom_name }} - Group Rooms</title>
</head>

<body>

    <h1>{{ $classroom->classroom_name }}</h1>

    <h2>Group Rooms</h2>

    @if(session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    <p>
        <a href="{{ route('student.group-rooms.create', $classroom->id) }}">
            + Create Group Room
        </a>
    </p>

    @if($classroom->groupRooms->count() > 0)

        <table border="1" cellpadding="10">

            <thead>
                <tr>
                    <th>Group Name</th>
                    <th>Created</th>
                </tr>
            </thead>

            <tbody>

                @foreach($classroom->groupRooms as $groupRoom)

                    <tr>
                        <td>
                            <a href="{{ route('student.group-room.show', $groupRoom->id) }}">
                                {{ $groupRoom->group_name }}
                            </a>
                        </td>

                        <td>
                            {{ $groupRoom->created_at->format('M d, Y') }}
                        </td>
                    </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <p>
            No group rooms have been created yet.
        </p>

    @endif

</body>
</html>