<!DOCTYPE html>
<html>
<head>
    <title>Capstone Approval</title>
</head>
<body>

    <h1>Capstone Approval</h1>

    <p>
        Admin:
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
                    <th>Instructor / Adviser</th>
                    <th>Verified By</th>
                    <th>Verified At</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                @foreach($capstones as $capstone)

                    <tr>

                        <td>
                            <a href="{{ route('admin.capstones.review', $capstone->id) }}">
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
                            {{ $capstone->groupRoom->classroom->instructor->first_name }}
                            {{ $capstone->groupRoom->classroom->instructor->last_name }}
                        </td>

                        <td>
                            {{ $capstone->verifier->first_name }}
                            {{ $capstone->verifier->last_name }}
                        </td>

                        <td>
                            {{ $capstone->verified_at?->format('M d, Y h:i A') }}
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
            There are no instructor-verified capstones waiting for admin approval.
        </p>

    @endif

</body>
</html>