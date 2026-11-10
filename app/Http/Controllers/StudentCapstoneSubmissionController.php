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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string'],
            'submitted_date' => ['required', 'date'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*' => ['required', 'integer', 'distinct'],
            'study' => ['required', 'file', 'mimes:pdf', 'max:51200'],
            'manual' => ['required', 'file', 'mimes:pdf', 'max:51200'],
            'system' => ['required', 'file', 'mimes:zip', 'max:204800'],
            'mentee_form' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
        ]);

        $contentHash = hash('sha256', $this->normalize($validated['title']).'|'.$this->normalize($validated['abstract']));

        if (StudentCapstoneSubmission::where('content_hash', $contentHash)->exists()) {
            return back()->withInput()->withErrors([
                'title' => 'This title and abstract have already been submitted. Please check your title and abstract because the same study is already on record.',
            ]);
        }

        $authors = StudentMasterlistRecord::query()
            ->where('program', auth()->user()->program)
            ->whereIn('id', $validated['authors'])
            ->get()
            ->keyBy('id');

        if ($authors->count() !== count($validated['authors'])) {
            return back()->withInput()->withErrors([
                'authors' => 'Every author must be selected from your program masterlist.',
            ]);
        }

        $directory = 'capstone-submissions/'.auth()->id();
        $submission = StudentCapstoneSubmission::create([
            'submitted_by' => auth()->id(),
            'title' => $validated['title'],
            'abstract' => $validated['abstract'],
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

        return view('review.student-submission-show', compact('submission'));
    }

    public function download(StudentCapstoneSubmission $submission, string $file)
    {
        abort_unless(auth()->user()->role === 'admin', 403);
        $path = $submission->filePath($file);

        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, basename($path));
    }

    public function preview(StudentCapstoneSubmission $submission, string $file)
    {
        abort_unless(auth()->user()->role === 'instructor', 403);
        $path = $submission->filePath($file);

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
        ]);
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
