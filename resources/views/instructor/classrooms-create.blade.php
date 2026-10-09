<!DOCTYPE html>
<html>
<head>
    <title>Create Classroom</title>
</head>

<body>

    <h1>Create Classroom</h1>

    <p>
        Instructor:
        {{ auth()->user()->first_name }}
        {{ auth()->user()->last_name }}
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

    <form method="POST" action="{{ route('instructor.classrooms.store') }}">

        @csrf

        <div>
            <label for="classroom_name">
                Classroom Name
            </label>

            <br>

            <input
                type="text"
                id="classroom_name"
                name="classroom_name"
                value="{{ old('classroom_name') }}"
                placeholder="Example: BSIT 3-1"
                required
            >
        </div>

        <br>

        <button type="submit">
            Create Classroom
        </button>

        <a href="{{ route('instructor.classrooms') }}">
            Cancel
        </a>

    </form>

</body>
</html>