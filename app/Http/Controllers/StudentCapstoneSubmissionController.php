<?php

namespace App\Http\Controllers;

use App\Models\StudentCapstoneAuthor;
use App\Models\StudentCapstoneSubmission;
use App\Models\StudentMasterlistRecord;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentCapstoneSubmissionController extends Controller
{
    public function create(): View
    {
        $submitterMasterlistRecord = StudentMasterlistRecord::query()
            ->where('program', auth()->user()->program)
            ->where('student_number', auth()->user()->student_number)
            ->first();
        $instructors = User::query()
            ->where('role', 'instructor')
            ->where('is_approved', true)
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('student.submission-create', [
            'submission' => auth()->user()
                ->studentCapstoneSubmissions()
                ->latest()
                ->first(),
            'masterlistRecords' => StudentMasterlistRecord::query()
                ->where('program', auth()->user()->program)
                ->orderBy('name')
                ->orderBy('student_number')
                ->get(),
            'submitterMasterlistRecord' => $submitterMasterlistRecord,
            'instructors' => $instructors,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string'],
            'adviser' => ['required', 'integer', 'exists:users,id'],
            'submitted_date' => ['required', 'date'],
            'authors' => ['required', 'array', 'min:1', 'max:3'],
            'authors.*' => ['required', 'integer', 'distinct'],
            'study' => ['required', 'file', 'mimes:pdf', 'max:51200'],
            'manual' => ['required', 'file', 'mimes:pdf', 'max:51200'],
            'system' => ['required', 'file', 'mimes:zip', 'max:204800'],
            'mentee_form' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
        ]);

        $adviser = User::query()
            ->whereKey($validated['adviser'])
            ->where('role', 'instructor')
            ->where('is_approved', true)
            ->where('is_active', true)
            ->first();

        if (! $adviser) {
            return back()->withInput()->withErrors([
                'adviser' => 'Select an active approved instructor as the adviser.',
            ]);
        }

        $contentHash = hash('sha256', $this->normalize($validated['title']).'|'.$this->normalize($validated['abstract']));

        if (StudentCapstoneSubmission::where('content_hash', $contentHash)->exists()) {
            return back()->withInput()->withErrors([
                'title' => 'This title and abstract have already been submitted. Please check your title and abstract because the same study is already on record.',
            ]);
        }

        $submitterRecord = StudentMasterlistRecord::query()
            ->where('program', auth()->user()->program)
            ->where('student_number', auth()->user()->student_number)
            ->first();

        if (! $submitterRecord) {
            return back()->withInput()->withErrors([
                'authors' => 'Your student record could not be found in the selected program masterlist.',
            ]);
        }

        $authorIds = collect($validated['authors'])
            ->push($submitterRecord->id)
            ->unique()
            ->values();

        if ($authorIds->count() > 3) {
            return back()->withInput()->withErrors([
                'authors' => 'The submitting student must be included among the maximum of 3 authors.',
            ]);
        }

        $authors = StudentMasterlistRecord::query()
            ->where('program', auth()->user()->program)
            ->whereIn('id', $authorIds)
            ->get()
            ->keyBy('id');

        if ($authors->count() !== $authorIds->count()) {
            return back()->withInput()->withErrors([
                'authors' => 'Every author must be selected from your program masterlist.',
            ]);
        }

        $directory = 'capstone-submissions/'.auth()->id();
        $submission = StudentCapstoneSubmission::create([
            'submitted_by' => auth()->id(),
            'title' => $validated['title'],
            'abstract' => $validated['abstract'],
            'adviser' => $adviser->id,
            'content_hash' => $contentHash,
            'submitted_date' => $validated['submitted_date'],
            'study_path' => $validated['study']->store($directory, 'local'),
            'manual_path' => $validated['manual']->store($directory, 'local'),
            'system_path' => $validated['system']->store($directory, 'local'),
            'mentee_form_path' => $validated['mentee_form']->store($directory, 'local'),
            'status' => 'submitted',
        ]);

        $authors->each(function (StudentMasterlistRecord $author) use ($submission): void {
            $user = User::where('student_number', $author->student_number)->first();

            StudentCapstoneAuthor::create([
                'student_capstone_submission_id' => $submission->id,
                'student_masterlist_record_id' => $author->id,
                'user_id' => $user?->id,
                'is_leader' => $user?->is(auth()->user()) ?? false,
            ]);
        });

        return redirect()->route('student.submissions.create')
            ->with('success', 'Your complete capstone submission was uploaded successfully.');
    }

    public function instructorIndex(): View
    {
        $this->ensureReviewer();

        return view('review.student-submissions', [
            'submissions' => StudentCapstoneSubmission::with('submitter')->latest()->get(),
            'audience' => 'Instructor',
        ]);
    }

    public function adminIndex(): View
    {
        $this->ensureReviewer();

        return view('review.student-submissions', [
            'submissions' => StudentCapstoneSubmission::with('submitter')->latest()->get(),
            'audience' => 'Admin',
        ]);
    }

    public function show(StudentCapstoneSubmission $submission): View
    {
        $this->ensureReviewer();
        $submission->loadMissing(['submitter', 'adviserUser', 'authors.masterlistRecord']);

        return view('review.student-submission-show', compact('submission'));
    }

    public function preview(StudentCapstoneSubmission $submission, string $file)
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'instructor'], true), 403);
        $path = $submission->filePath($file);

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
        ]);
    }

    public function returnSubmission(Request $request, StudentCapstoneSubmission $submission): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $validated = $request->validate([
            'return_reason' => ['required', 'string', 'max:1000'],
        ]);

        $submission->update([
            'status' => 'returned',
            'return_reason' => $validated['return_reason'],
        ]);

        return redirect()
            ->route('admin.student-submissions.show', $submission)
            ->with('success', 'The capstone was returned to the student for correction.');
    }

    public function addToRepository(StudentCapstoneSubmission $submission): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        if ($submission->status === 'approved') {
            return back()->with('error', 'This capstone is already in the repository.');
        }

        $submission->update([
            'status' => 'approved',
            'return_reason' => null,
        ]);

        return redirect()
            ->route('repository.show', [
                'capstoneId' => $submission->id,
                'type' => 'student',
            ])
            ->with('success', 'The capstone was added to the repository.');
    }

    private function ensureReviewer(): void
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'instructor'], true), 403);
    }

    private function normalize(string $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim($value))) ?? trim($value);
    }
}
