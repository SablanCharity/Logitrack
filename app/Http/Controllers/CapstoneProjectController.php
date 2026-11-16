<?php

namespace App\Http\Controllers;

use App\Mail\CapstoneProjectReturned;
use App\Models\ArchivedCapstone;
use App\Models\CapstoneAudit;
use App\Models\CapstoneProject;
use App\Models\CapstoneSubmissionWindow;
use App\Models\GroupRoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class CapstoneProjectController extends Controller
{
    public function create(int $groupRoomId): View|RedirectResponse
    {
        if (! CapstoneSubmissionWindow::current()->isCurrentlyOpen()) {
            return redirect()
                ->route('student.group-room.show', $groupRoomId)
                ->with('error', 'Capstone submissions are currently closed. Please wait for the department admin to open submissions.');
        }

        $groupRoom = GroupRoom::with([
            'classroom.instructor',
            'members.user',
            'capstoneProject',
        ])->findOrFail($groupRoomId);

        $this->ensureStudentCanAccessGroup($groupRoom);

        // Only one capstone submission per group.
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
        if (! CapstoneSubmissionWindow::current()->isCurrentlyOpen()) {
            return back()->with('error', 'Capstone submissions are currently closed. Please wait for the department admin to open submissions.');
        }

        $groupRoom = GroupRoom::with([
            'classroom.instructor',
            'members.user',
            'capstoneProject',
        ])->findOrFail($groupRoomId);

        $this->ensureStudentCanAccessGroup($groupRoom);

        // Prevent duplicate submission.
        if ($groupRoom->capstoneProject) {
            return back()->with(
                'error',
                'This group already has a capstone submission.'
            );
        }

        $validated = $request->validate([
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

        $capstone = CapstoneProject::create([
            'group_room_id' => $groupRoom->id,
            'submitted_by' => auth()->id(),
            'program' => auth()->user()->program,
            'year_section' => auth()->user()->year_section,
            'title' => $validated['title'],
            'abstract' => $validated['abstract'],
            'publication_date' => $validated['publication_date'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->logCapstoneAudit(
            $capstone,
            'submitted',
            'Student submitted a capstone for instructor review.'
        );

        return redirect()
            ->route('student.group-room.show', $groupRoom->id)
            ->with(
                'success',
                'Capstone submitted successfully. Your adviser will review the metadata.'
            );
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
            ->pluck('year_section')
            ->filter()
            ->unique()
            ->sort()
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
            (int) $capstone->groupRoom->classroom->instructor_id
            !== (int) auth()->id()
        ) {
            abort(403, 'You are not authorized to verify this capstone.');
        }

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

        $this->logCapstoneAudit(
            $capstone,
            'instructor_verified',
            'Instructor verified the capstone metadata.'
        );

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
            (int) $capstone->groupRoom->classroom->instructor_id
            !== (int) auth()->id()
        ) {
            abort(403, 'You are not authorized to return this capstone.');
        }

        if ($capstone->status !== 'submitted') {
            return back()->with(
                'error',
                'This capstone is not currently waiting for instructor verification.'
            );
        }

        $validated = $request->validate([
            'return_reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        $capstone->update([
            'status' => 'returned_by_instructor',
            'return_reason' => $validated['return_reason'],
        ]);

        $this->logCapstoneAudit(
            $capstone,
            'returned_by_instructor',
            'Instructor returned the capstone. Reason: '
                .$validated['return_reason']
        );

        return redirect()
            ->route('instructor.capstones')
            ->with('success', 'Capstone returned to the student for correction.');
    }

    public function setBestStudy(Request $request, int $capstoneId): RedirectResponse
    {
        $type = $request->validate([
            'type' => ['nullable', 'in:modern,old'],
        ])['type'] ?? 'modern';

        DB::transaction(function () use ($capstoneId, $type): void {
            $model = $type === 'old' ? ArchivedCapstone::class : CapstoneProject::class;
            $capstone = $model::query()
                ->where('status', $type === 'old' ? 'approved' : 'admin_approved')
                ->lockForUpdate()
                ->findOrFail($capstoneId);

            CapstoneProject::query()
                ->where('is_best', true)
                ->update(['is_best' => false]);
            ArchivedCapstone::query()
                ->where('is_best', true)
                ->update(['is_best' => false]);

            $capstone->update(['is_best' => true]);
        });

        return back()->with('success', 'Best capstone study updated.');
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

        return view('admin.capstones', [
            'capstones' => $capstones,
            'submissionWindow' => CapstoneSubmissionWindow::current(),
        ]);
    }

    public function updateSubmissionWindow(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'deadline_on' => ['required', 'date_format:Y-m-d'],
        ]);

        $window = CapstoneSubmissionWindow::current();
        $window->update([
            'deadline_on' => $validated['deadline_on'],
            // Editing a past/expired window never opens it by itself.
            'is_open' => $window->isCurrentlyOpen(),
        ]);

        return back()->with('success', 'Submission deadline saved as '.$validated['deadline_on'].'. Open submissions when you are ready to accept student work.');
    }

    public function toggleSubmissionWindow(): RedirectResponse
    {
        $window = CapstoneSubmissionWindow::current();

        if ($window->isCurrentlyOpen()) {
            $window->update(['is_open' => false]);

            return back()->with('success', 'Capstone submissions are now closed.');
        }

        if ($window->deadline_on === null || $window->deadline_on->toDateString() < now()->toDateString()) {
            return back()->with('error', 'Set a due date of today or later before opening capstone submissions.');
        }

        $window->update(['is_open' => true]);

        return back()->with('success', 'Capstone submissions are now open until '.$window->deadline_on->format('M d, Y').'.');
    }

    public function adminReview(int $capstoneId): View
    {
        $capstone = CapstoneProject::with([
            'groupRoom.classroom.instructor',
            'groupRoom.members.user',
            'submitter',
            'verifier',
        ])->findOrFail($capstoneId);

        if ($capstone->status !== 'instructor_verified') {
            abort(404, 'This capstone is not waiting for admin approval.');
        }

        return view('admin.admin-capstone-review', compact('capstone'));
    }

    public function adminApprove(int $capstoneId): RedirectResponse
    {
        $capstone = CapstoneProject::findOrFail($capstoneId);

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

        $this->logCapstoneAudit(
            $capstone,
            'admin_approved',
            'Administrator approved the capstone for the repository.'
        );

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
        $capstone = CapstoneProject::with(['groupRoom.classroom', 'submitter'])
            ->findOrFail($capstoneId);

        if ($capstone->status !== 'instructor_verified') {
            return back()->with(
                'error',
                'This capstone is not currently waiting for admin review.'
            );
        }

        $validated = $request->validate([
            'return_reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        $capstone->update([
            'status' => 'returned_by_admin',
            'return_reason' => $validated['return_reason'],
        ]);

        $this->logCapstoneAudit(
            $capstone,
            'returned_by_admin',
            'Administrator returned the capstone. Reason: '
                .$validated['return_reason']
        );

        $redirect = redirect()->route('admin.capstones');

        if (! $capstone->submitter) {
            return $redirect
                ->with('success', 'Capstone returned for correction.')
                ->with('error', 'No submitting student account was linked to this capstone, so no email notification could be sent.');
        }

        try {
            Mail::to($capstone->submitter->email)->send(new CapstoneProjectReturned($capstone));
        } catch (Throwable $exception) {
            Log::warning('Capstone return email could not be sent.', [
                'capstone_id' => $capstone->id,
                'recipient_id' => $capstone->submitter->id,
                'exception' => $exception::class,
            ]);

            return $redirect
                ->with('success', 'Capstone returned for correction.')
                ->with('error', 'The capstone was returned, but its email notification could not be sent.');
        }

        return $redirect->with('success', 'Capstone returned for correction. The student was notified by email.');
    }

    public function edit(int $groupRoomId): View
    {
        $groupRoom = GroupRoom::with([
            'classroom.instructor',
            'members.user',
            'capstoneProject',
        ])->findOrFail($groupRoomId);

        $this->ensureStudentCanAccessGroup($groupRoom);

        if (! $groupRoom->capstoneProject) {
            abort(404, 'No capstone submission found.');
        }

        // Only returned capstones can be edited.
        if (! in_array($groupRoom->capstoneProject->status, [
            'returned_by_instructor',
            'returned_by_admin',
        ], true)) {
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
        if (! CapstoneSubmissionWindow::current()->isCurrentlyOpen()) {
            return back()->with('error', 'Capstone submissions are currently closed. Please wait for the department admin to reopen submissions.');
        }

        $groupRoom = GroupRoom::with([
            'classroom',
            'members',
            'capstoneProject',
        ])->findOrFail($groupRoomId);

        $this->ensureStudentCanAccessGroup($groupRoom);

        $capstone = $groupRoom->capstoneProject;

        if (! $capstone) {
            abort(404, 'No capstone submission found.');
        }

        // Only returned capstones can be resubmitted.
        if (! in_array($capstone->status, [
            'returned_by_instructor',
            'returned_by_admin',
        ], true)) {
            return back()->with(
                'error',
                'This capstone is not currently available for resubmission.'
            );
        }

        $validated = $request->validate([
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
            'title' => $validated['title'],
            'abstract' => $validated['abstract'],
            'publication_date' => $validated['publication_date'],

            // Send it back to instructor review.
            'status' => 'submitted',
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),

            // Previous admin approval is no longer current.
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $this->logCapstoneAudit(
            $capstone,
            'resubmitted',
            'Student corrected and resubmitted the capstone.'
        );

        return redirect()
            ->route('student.group-room.show', $groupRoom->id)
            ->with(
                'success',
                'Capstone corrected and resubmitted successfully. Your instructor/adviser will review it again.'
            );
    }

    /**
     * Record an action in the capstone audit log.
     */
    private function logCapstoneAudit(
        CapstoneProject $capstone,
        string $action,
        ?string $description = null
    ): void {
        CapstoneAudit::create([
            'user_id' => auth()->id(),
            'capstone_project_id' => $capstone->id,
            'action' => $action,
            'description' => $description,
        ]);
    }

    /**
     * Ensure the current user is a student, an approved classroom member,
     * and a member of the requested group.
     */
    private function ensureStudentCanAccessGroup(GroupRoom $groupRoom): void
    {
        $user = auth()->user();

        if (! $user || $user->role !== 'student') {
            abort(403, 'Only students are authorized to access this group.');
        }

        $isClassroomMember = $groupRoom->classroom
            ->members()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists();

        if (! $isClassroomMember) {
            abort(403, 'You are not an approved student in this classroom.');
        }

        $isGroupMember = $groupRoom->members()
            ->where('user_id', $user->id)
            ->exists();

        if (! $isGroupMember) {
            abort(403, 'You are not a member of this group.');
        }
    }
}
