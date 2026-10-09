<!DOCTYPE html>
<html>
<head>
    <title>{{ $groupRoom->group_name }}</title>
</head>

<body>

    <h1>{{ $groupRoom->group_name }}</h1>

    <p>
        Classroom:
        <strong>{{ $groupRoom->classroom->classroom_name }}</strong>
    </p>

    @if($groupRoom->capstoneProject)

    <p>
        <strong>Capstone Status:</strong>
        {{ ucfirst(str_replace('_', ' ', $groupRoom->capstoneProject->status)) }}
    </p>

    @if(in_array($groupRoom->capstoneProject->status, [
            'returned_by_instructor',
            'returned_by_admin'
        ]))

            @if($groupRoom->capstoneProject->return_reason)
                <p>
                    <strong>Reason for Return:</strong>
                    {{ $groupRoom->capstoneProject->return_reason }}
                </p>
            @endif

            <p>
                <a href="{{ route('student.capstone.edit', $groupRoom->id) }}">
                    Edit & Resubmit Capstone
                </a>
            </p>

        @endif

    @else

        <p>
            <a href="{{ route('student.capstone.create', $groupRoom->id) }}">
                Submit Capstone
            </a>
        </p>

    @endif

    <hr>

    <h2>Group Members</h2>

@if(session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

@if(session('error'))
    <p style="color: red;">{{ session('error') }}</p>
@endif

@if($groupRoom->members->count() < 3)
    @php
        $isLeader = $groupRoom->members
            ->where('user_id', auth()->id())
            ->where('is_leader', true)
            ->isNotEmpty();
    @endphp

    @if($isLeader)
        <h3>Add Member</h3>

        <form method="POST"
              action="{{ route('student.group-room.add-member', $groupRoom->id) }}">
            @csrf

            <label for="user_id">Select Student</label><br>

            <select id="user_id" name="user_id" required>
                <option value="">-- Select Student --</option>

                @foreach($groupRoom->classroom->members as $classroomMember)
                    @if(
                        $classroomMember->status === 'approved' &&
                        $classroomMember->user_id !== auth()->id() &&
                        !$groupRoom->members->contains('user_id', $classroomMember->user_id)
                    )
                        <option value="{{ $classroomMember->user_id }}">
                            {{ $classroomMember->user->first_name }}
                            {{ $classroomMember->user->last_name }}
                            — {{ $classroomMember->user->email }}
                        </option>
                    @endif
                @endforeach
            </select>

            <button type="submit">
                Add Member
            </button>
        </form>
    @endif
@else
    <p>This group has reached the maximum of 3 members.</p>
@endif

    @if($groupRoom->members->count() > 0)

        <table border="1" cellpadding="10">

            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Program</th>
                    <th>Year & Section</th>
                    <th>Role</th>
                </tr>
            </thead>

            <tbody>

                @foreach($groupRoom->members as $member)

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
                            @if($member->is_leader)
                                Leader
                            @else
                                Member
                            @endif
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <p>
            No members in this group.
        </p>

    @endif

    <br>

    <a href="{{ route('student.group-rooms', $groupRoom->classroom_id) }}">
        ← Back to Group Rooms
    </a>

</body>
</html>