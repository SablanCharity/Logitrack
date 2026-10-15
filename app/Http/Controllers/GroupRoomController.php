<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\GroupRoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupRoomController extends Controller
{
    public function index(int $classroomId): View
    {
        $classroom = Classroom::with('groupRooms')
            ->findOrFail($classroomId);

        $membership = $classroom->members()
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->exists();

        if (! $membership) {
            abort(403, 'You are not an approved student in this classroom.');
        }

        return view('student.group-rooms', compact('classroom'));
    }

    public function show(int $groupRoomId): View
    {
        $groupRoom = GroupRoom::with([
            'classroom.members.user',
            'members.user',
        ])->findOrFail($groupRoomId);

        $membership = $groupRoom->classroom
            ->members()
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->exists();

        if (! $membership) {
            abort(403, 'You are not an approved student in this classroom.');
        }

        return view('student.group-room-show', compact('groupRoom'));
    }

    public function create(int $classroomId): View
    {
        $classroom = Classroom::findOrFail($classroomId);

        return view('student.group-room-create', compact('classroom'));
    }

    public function store(
        Request $request,
        int $classroomId
    ): RedirectResponse {
        $request->validate([
            'group_name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $classroom = Classroom::findOrFail($classroomId);

        $membership = $classroom->members()
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->first();

        if (! $membership) {
            abort(403, 'You are not an approved student in this classroom.');
        }

        $alreadyInGroup = \App\Models\GroupMember::whereHas(
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
            'group_name' => $request->group_name,
        ]);

        $groupRoom->members()->create([
            'user_id' => auth()->id(),
            'is_leader' => true,
        ]);

        return redirect()
            ->route('student.group-rooms.index', $classroom->id)
            ->with('success', 'Group room created successfully.');
    }

    public function addMember(
        Request $request,
        int $groupRoomId
    ): RedirectResponse {
        $groupRoom = GroupRoom::with('members')
            ->findOrFail($groupRoomId);

        // Only the group leader can add members
        $isLeader = $groupRoom->members()
            ->where('user_id', auth()->id())
            ->where('is_leader', true)
            ->exists();

        if (! $isLeader) {
            abort(403, 'Only the group leader can add members.');
        }

        // Maximum of 3 members
        if ($groupRoom->members()->count() >= 3) {
            return back()->with('error', 'This group already has the maximum of 3 members.');
        }

        $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        // Make sure the selected student belongs to the same classroom
        $studentIsInClassroom = $groupRoom->classroom
            ->members()
            ->where('user_id', $request->user_id)
            ->where('status', 'approved')
            ->exists();

        if (! $studentIsInClassroom) {
            return back()->with('error', 'The selected student is not an approved member of this classroom.');
        }

        // Prevent duplicate group membership
        $alreadyMember = $groupRoom->members()
            ->where('user_id', $request->user_id)
            ->exists();

        if ($alreadyMember) {
            return back()->with('error', 'This student is already a member of the group.');
        }

        $groupRoom->members()->create([
            'user_id' => $request->user_id,
            'is_leader' => false,
        ]);

        return back()->with('success', 'Member added successfully.');
    }
}