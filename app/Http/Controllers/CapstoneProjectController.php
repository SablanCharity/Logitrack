<?php

namespace App\Http\Controllers;

use App\Models\CapstoneProject;
use App\Models\GroupRoom;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CapstoneProjectController extends Controller
{
      public function create(int $groupRoomId): View|RedirectResponse
    {
        $groupRoom = GroupRoom::with([
            'classroom.instructor',
            'members.user',
            'capstoneProject',
        ])->findOrFail($groupRoomId);

        // Student must be an approved member of the classroom
        $isClassroomMember = $groupRoom->classroom
            ->members()
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->exists();

        if (! $isClassroomMember) {
            abort(403, 'You are not an approved student in this classroom.');
        }

        // Student must be a member of this group
        $isGroupMember = $groupRoom->members
            ->contains('user_id', auth()->id());

        if (! $isGroupMember) {
            abort(403, 'You are not a member of this group.');
        }

        // Only one capstone submission per group
        if ($groupRoom->capstoneProject) {
            return redirect()
                ->route('student.group-room.show', $groupRoom->id)
                ->with('error', 'This group already has a capstone submission.');
        }

        return view('student.capstone-create', compact('groupRoom'));
    }

    public function store(
        Request $request,
        int $groupRoomId
    ): RedirectResponse {
        $groupRoom = GroupRoom::with([
            'classroom.instructor',
            'members.user',
            'capstoneProject',
        ])->findOrFail($groupRoomId);

        // Student must be an approved classroom member
        $isClassroomMember = $groupRoom->classroom
            ->members()
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->exists();

        if (! $isClassroomMember) {
            abort(403, 'You are not an approved student in this classroom.');
        }

        // Student must belong to this group
        $isGroupMember = $groupRoom->members
            ->contains('user_id', auth()->id());

        if (! $isGroupMember) {
            abort(403, 'You are not a member of this group.');
        }

        // Prevent duplicate submission
        if ($groupRoom->capstoneProject) {
            return back()->with('error', 'This group already has a capstone submission.');
        }

        $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'abstract' => [
                'required',
                'string',
            ],

            'publication_date' => [
                'required',
                'date',
            ],
        ]);

        CapstoneProject::create([
            'group_room_id' => $groupRoom->id,

            // Student who submitted
            'submitted_by' => auth()->id(),

            // Automatically obtained from the classroom/student
            'program' => auth()->user()->program,
            'year_section' => auth()->user()->year_section,

            'title' => $request->title,
            'abstract' => $request->abstract,
            'publication_date' => $request->publication_date,

            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('student.group-room.show', $groupRoom->id)
            ->with('success', 'Capstone submitted successfully. Your adviser will review the metadata.');
    }

    public function instructorIndex(): View
        {
            $instructorId = auth()->id();

            $capstones = CapstoneProject::with([
                'groupRoom.classroom',
                'groupRoom.members.user',
                'submitter',
            ])
                ->where('status', 'submitted')
                ->whereHas('groupRoom.classroom', function ($query) use ($instructorId) {
                    $query->where('instructor_id', $instructorId);
                })
                ->latest('submitted_at')
                ->get();

            $years = $capstones
                ->pluck('publication_date')
                ->filter()
                ->map(fn ($publicationDate) => $publicationDate->year)
                ->unique()
                ->sortDesc()
                ->values();

            return view('instructor.capstones', compact('capstones', 'years'));
        }

        public function instructorReview(int $capstoneId): View
        {
            $capstone = CapstoneProject::with([
                'groupRoom.classroom.instructor',
                'groupRoom.members.user',
                'submitter',
            ])
                ->whereKey($capstoneId)
                ->whereHas('groupRoom.classroom', function ($query) {
                    $query->where('instructor_id', auth()->id());
                })
                ->firstOrFail();

            return view('instructor.capstone-review', compact('capstone'));
        }

        public function instructorVerify(int $capstoneId): RedirectResponse
        {
            $capstone = CapstoneProject::with('groupRoom.classroom')
                ->findOrFail($capstoneId);

            // Only the instructor assigned to this classroom can verify it.
            if (
                $capstone->groupRoom->classroom->instructor_id
                !== auth()->id()
            ) {
                abort(403, 'You are not authorized to verify this capstone.');
            }

            // Only submitted capstones can be verified.
            if ($capstone->status !== 'submitted') {
                return back()->with(
                    'error',
                    'This capstone is not currently waiting for instructor verification.'
                );
            }

            $capstone->update([
                'status' => 'instructor_verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            return redirect()
                ->route('instructor.capstones')
                ->with('success', 'Capstone metadata verified successfully.');
        }

        public function instructorReturn(
            Request $request,
            int $capstoneId
        ): RedirectResponse {
            $capstone = CapstoneProject::with('groupRoom.classroom')
                ->findOrFail($capstoneId);

            // Only the instructor assigned to this classroom can return it.
            if (
                $capstone->groupRoom->classroom->instructor_id
                !== auth()->id()
            ) {
                abort(403, 'You are not authorized to return this capstone.');
            }

            // Only submitted capstones can be returned.
            if ($capstone->status !== 'submitted') {
                return back()->with(
                    'error',
                    'This capstone is not currently waiting for instructor verification.'
                );
            }

            $request->validate([
                'return_reason' => [
                    'required',
                    'string',
                    'max:1000',
                ],
            ]);

            $capstone->update([
                'status' => 'returned_by_instructor',
                'return_reason' => $request->return_reason,
            ]);

            return redirect()
                ->route('instructor.capstones')
                ->with('success', 'Capstone returned to the student for correction.');
        }

        public function adminIndex(): View
        {
            $capstones = CapstoneProject::with([
                'groupRoom.classroom.instructor',
                'groupRoom.members.user',
                'submitter',
                'verifier',
            ])
                ->where('status', 'instructor_verified')
                ->latest('verified_at')
                ->get();

            return view('admin.capstones', compact('capstones'));
        }

        public function adminReview(int $capstoneId): View
        {
            $capstone = CapstoneProject::with([
                'groupRoom.classroom.instructor',
                'groupRoom.members.user',
                'submitter',
                'verifier',
            ])->findOrFail($capstoneId);

            // Only instructor-verified capstones should be reviewed by admin
            if ($capstone->status !== 'instructor_verified') {
                abort(404, 'This capstone is not waiting for admin approval.');
            }

            return view('admin.capstone-review', compact('capstone'));
        }

        public function adminApprove(int $capstoneId): RedirectResponse
        {
            $capstone = CapstoneProject::findOrFail($capstoneId);

            // Only instructor-verified capstones can be approved
            if ($capstone->status !== 'instructor_verified') {
                return back()->with(
                    'error',
                    'This capstone is not ready for admin approval.'
                );
            }

            $capstone->update([
                'status' => 'admin_approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return redirect()
                ->route('admin.capstones')
                ->with(
                    'success',
                    'Capstone approved successfully and added to the repository.'
                );
        }

        public function adminReturn(
            Request $request,
            int $capstoneId
        ): RedirectResponse {
            $capstone = CapstoneProject::with('groupRoom.classroom')
                ->findOrFail($capstoneId);

            // Only instructor-verified capstones can be returned by admin
            if ($capstone->status !== 'instructor_verified') {
                return back()->with(
                    'error',
                    'This capstone is not currently waiting for admin review.'
                );
            }

            $request->validate([
                'return_reason' => [
                    'required',
                    'string',
                    'max:1000',
                ],
            ]);

            $capstone->update([
                'status' => 'returned_by_admin',
                'return_reason' => $request->return_reason,
            ]);

            return redirect()
                ->route('admin.capstones')
                ->with(
                    'success',
                    'Capstone returned for correction.'
                );
        }

        public function edit(int $groupRoomId): View
        {
            $groupRoom = GroupRoom::with([
                'classroom.instructor',
                'members.user',
                'capstoneProject',
            ])->findOrFail($groupRoomId);

            // Student must be an approved member of the classroom
            $isClassroomMember = $groupRoom->classroom
                ->members()
                ->where('user_id', auth()->id())
                ->where('status', 'approved')
                ->exists();

            if (! $isClassroomMember) {
                abort(403, 'You are not an approved student in this classroom.');
            }

            // Student must be a member of this group
            $isGroupMember = $groupRoom->members
                ->contains('user_id', auth()->id());

            if (! $isGroupMember) {
                abort(403, 'You are not a member of this group.');
            }

            // A capstone must exist
            if (! $groupRoom->capstoneProject) {
                abort(404, 'No capstone submission found.');
            }

            // Only returned capstones can be edited
            if (! in_array($groupRoom->capstoneProject->status, [
                'returned_by_instructor',
                'returned_by_admin',
            ])) {
                abort(403, 'This capstone is not available for correction.');
            }

            return view(
                'student.capstone-edit',
                compact('groupRoom')
            );
        }

        public function update(
            Request $request,
            int $groupRoomId
        ): RedirectResponse {
            $groupRoom = GroupRoom::with([
                'classroom',
                'members',
                'capstoneProject',
            ])->findOrFail($groupRoomId);

            // Student must be an approved member of the classroom
            $isClassroomMember = $groupRoom->classroom
                ->members()
                ->where('user_id', auth()->id())
                ->where('status', 'approved')
                ->exists();

            if (! $isClassroomMember) {
                abort(403, 'You are not an approved student in this classroom.');
            }

            // Student must be a member of this group
            $isGroupMember = $groupRoom->members
                ->contains('user_id', auth()->id());

            if (! $isGroupMember) {
                abort(403, 'You are not a member of this group.');
            }

            // Capstone must exist
            $capstone = $groupRoom->capstoneProject;

            if (! $capstone) {
                abort(404, 'No capstone submission found.');
            }

            // Only returned capstones can be resubmitted
            if (! in_array($capstone->status, [
                'returned_by_instructor',
                'returned_by_admin',
            ])) {
                return back()->with(
                    'error',
                    'This capstone is not currently available for resubmission.'
                );
            }

            $request->validate([
                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'abstract' => [
                    'required',
                    'string',
                ],

                'publication_date' => [
                    'required',
                    'date',
                ],
            ]);

            $capstone->update([
                'title' => $request->title,
                'abstract' => $request->abstract,
                'publication_date' => $request->publication_date,

                // Send it back to instructor review
                'status' => 'submitted',

                'submitted_by' => auth()->id(),
                'submitted_at' => now(),

                // Admin/instructor approval is no longer current
                'approved_by' => null,
                'approved_at' => null,
            ]);

            return redirect()
                ->route('student.group-room.show', $groupRoom->id)
                ->with(
                    'success',
                    'Capstone corrected and resubmitted successfully. Your instructor/adviser will review it again.'
                );
        }

        public function setBestStudy(int $capstoneId): RedirectResponse
        {
            DB::transaction(function () use ($capstoneId): void {
                $capstone = CapstoneProject::query()
                    ->where('status', 'admin_approved')
                    ->lockForUpdate()
                    ->findOrFail($capstoneId);

                CapstoneProject::query()
                    ->where('is_best', true)
                    ->update(['is_best' => false]);

                $capstone->update(['is_best' => true]);
            });

            return back()->with('success', 'Best capstone study updated.');
        }

}