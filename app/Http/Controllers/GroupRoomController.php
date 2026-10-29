<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\GroupMember;
use App\Models\GroupRoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupRoomController extends Controller
{
    /**
     * Display groups in a classroom.
     */
    public function index(int $classroomId): View
    {
        $classroom = Classroom::with(['groupRooms' => function ($query): void {
            $query->whereHas('members', function ($memberQuery): void {
                $memberQuery->where('user_id', auth()->id());
            });
        }])
            ->findOrFail($classroomId);

        // Only approved classroom members can view its groups.
        $this->ensureApprovedClassroomMember($classroom);

        // Check whether the current student already belongs
        // to a group in this classroom.
        $alreadyInGroup = GroupMember::whereHas(
            'groupRoom',
            function ($query) use ($classroom) {
                $query->where('classroom_id', $classroom->id);
            }
        )
            ->where('user_id', auth()->id())
            ->exists();

        return view(
            'student.group-rooms',
            compact('classroom', 'alreadyInGroup')
        );
    }

    /**
     * Display a group room.
     */
    public function show(int $groupRoomId): View
    {
        $groupRoom = GroupRoom::with([
            'classroom.members.user',
            'members.user',
        ])->findOrFail($groupRoomId);

        // Authorize access through the GroupRoom policy.
        Gate::authorize('view', $groupRoom);

        return view('student.group-room-show', compact('groupRoom'));
    }

    /**
     * Display the group creation form.
     */
    public function create(
        int $classroomId
    ): View|RedirectResponse {
        $classroom = Classroom::findOrFail($classroomId);

        // Only students can create group rooms.
        Gate::authorize('create', GroupRoom::class);

        // The student must be an approved classroom member.
        $this->ensureApprovedClassroomMember($classroom);

        $alreadyInGroup = GroupMember::whereHas(
            'groupRoom',
            function ($query) use ($classroom) {
                $query->where('classroom_id', $classroom->id);
            }
        )
            ->where('user_id', auth()->id())
            ->exists();

        if ($alreadyInGroup) {
            abort(
                403,
                'You are already a member of a group in this classroom.'
            );
        }

        return view('student.group-room-create', compact('classroom'));
    }

    /**
     * Create a group room.
     */
    public function store(
        Request $request,
        int $classroomId
    ): RedirectResponse {
        $validated = $request->validate([
            'group_name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $classroom = Classroom::findOrFail($classroomId);

        // Only approved classroom members can create groups.
        Gate::authorize('create', GroupRoom::class);
        $this->ensureApprovedClassroomMember($classroom);

        $alreadyInGroup = GroupMember::whereHas(
            'groupRoom',
            function ($query) use ($classroom) {
                $query->where('classroom_id', $classroom->id);
            }
        )
            ->where('user_id', auth()->id())
            ->exists();

        if ($alreadyInGroup) {
            return back()->with(
                'error',
                'You are already a member of a group in this classroom.'
            );
        }

        $groupRoom = GroupRoom::create([
            'classroom_id' => $classroom->id,
            'group_name' => $validated['group_name'],
        ]);

        // The creator becomes the group leader.
        $groupRoom->members()->create([
            'user_id' => auth()->id(),
            'is_leader' => true,
        ]);

        return redirect()
            ->route('student.group-rooms.index', $classroom->id)
            ->with('success', 'Group room created successfully.');
    }

    /**
     * Add an approved student to a group room.
     */
    public function addMember(
        Request $request,
        int $groupRoomId
    ): RedirectResponse {
        $groupRoom = GroupRoom::with('classroom')
            ->findOrFail($groupRoomId);

        // The policy verifies that the current user is the group leader
        // and is allowed to add members.
        Gate::authorize('addMember', $groupRoom);

        // The selected account must exist and have the student role.
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('role', 'student')
                ),
            ],
        ]);

        $selectedUserId = (int) $validated['user_id'];

        // Only approved students in this classroom can be added.
        $studentIsInClassroom = $groupRoom->classroom
            ->members()
            ->where('user_id', $selectedUserId)
            ->where('status', 'approved')
            ->exists();

        if (! $studentIsInClassroom) {
            return back()->with(
                'error',
                'The selected student is not an approved member of this classroom.'
            );
        }

        // Prevent duplicate membership in this group.
        $alreadyMember = $groupRoom->members()
            ->where('user_id', $selectedUserId)
            ->exists();

        if ($alreadyMember) {
            return back()->with(
                'error',
                'This student is already a member of the group.'
            );
        }

        // Prevent a student from joining multiple groups in this classroom.
        $alreadyInAnotherGroup = GroupMember::where(
            'user_id',
            $selectedUserId
        )
            ->whereHas(
                'groupRoom',
                function ($query) use ($groupRoom) {
                    $query->where(
                        'classroom_id',
                        $groupRoom->classroom_id
                    );
                }
            )
            ->exists();

        if ($alreadyInAnotherGroup) {
            return back()->with(
                'error',
                'This student is already a member of another group in this classroom.'
            );
        }

        // Maximum of three members, including the leader.
        if ($groupRoom->members()->count() >= 3) {
            return back()->with(
                'error',
                'This group already has the maximum of 3 members.'
            );
        }

        $groupRoom->members()->create([
            'user_id' => $selectedUserId,
            'is_leader' => false,
        ]);

        return back()->with(
            'success',
            'Member added successfully.'
        );
    }

    /**
     * Verify that the current user is an approved member of a classroom.
     */
    private function ensureApprovedClassroomMember(
        Classroom $classroom
    ): void {
        $membership = $classroom->members()
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->exists();

        if (! $membership) {
            abort(
                403,
                'You are not an approved student in this classroom.'
            );
        }
    }
}
