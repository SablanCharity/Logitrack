<!DOCTYPE html>
<html>
<head>
    <title>Create Group Room</title>
</head>

<body>

    <h1>Create Group Room</h1>

    <p>
        Classroom:
        <strong>{{ $classroom->classroom_name }}</strong>
    </p>

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

    <form
        method="POST"
        action="{{ route('student.group-rooms.store', $classroom->id) }}"
    >

        @csrf

        <div>

            <label for="group_name">
                Group Name
            </label>

            <br>

            <input
                type="text"
                id="group_name"
                name="group_name"
                value="{{ old('group_name') }}"
                placeholder="Example: Group 1"
                required
            >

        </div>

        <br>

        <button type="submit">
            Create Group Room
        </button>

        <a href="{{ route('student.group-rooms', $classroom->id) }}">
            Cancel
        </a>

    </form>

</body>
</html>