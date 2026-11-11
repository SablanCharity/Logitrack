<?php

namespace App\Http\Controllers;

use App\Models\StudentCapstoneAuthor;
use App\Models\StudentCapstoneSubmission;
use App\Models\StudentMasterlistRecord;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $submission = auth()->user()->studentCapstoneSubmissions()->latest()->first();

        if ($submission === null && $submitterMasterlistRecord !== null) {
            $submission = StudentCapstoneSubmission::query()
                ->whereHas('authors', function ($query) use ($submitterMasterlistRecord): void {
                    $query->where('student_masterlist_record_id', $submitterMasterlistRecord->id);
                })
                ->latest()
                ->first();
        }

        $instructors = User::query()
            ->where('role', 'instructor')
            ->where('is_approved', true)
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('student.submission-create', [
            'submission' => $submission,
            'canEditReturnedSubmission' => $submission?->status === 'returned'
                && $submission->submitted_by === auth()->id(),
            'masterlistRecords' => StudentMasterlistRecord::query()
                ->where('program', auth()->user()->program)
                ->whereNotIn('id', StudentCapstoneAuthor::query()->select('student_masterlist_record_id'))
                ->whereNotIn('student_number', User::query()
                    ->whereNotNull('student_number')
                    ->whereHas('studentCapstoneSubmissions')
                    ->select('student_number'))
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

        $contentHash = hash('sha256', $this->normalize($validated['title']).'|'.$this->normalize($validated['abstract']));
        $storedPaths = [];

        try {
            $submission = DB::transaction(function () use ($validated, $adviser, $authorIds, $contentHash, &$storedPaths): StudentCapstoneSubmission|RedirectResponse {
                $authors = StudentMasterlistRecord::query()
                    ->where('program', auth()->user()->program)
                    ->whereIn('id', $authorIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($authors->count() !== $authorIds->count()) {
                    return back()->withInput()->withErrors([
                        'authors' => 'Every author must be selected from your program masterlist.',
                    ]);
                }

                $hasExistingSubmission = StudentCapstoneSubmission::query()
                    ->where('submitted_by', auth()->id())
                    ->orWhereHas('authors', function ($query) use ($authorIds): void {
                        $query->whereIn('student_masterlist_record_id', $authorIds);
                    })
                    ->exists();

                if ($hasExistingSubmission) {
                    return back()->withInput()->withErrors([
                        'authors' => 'You or one or more selected students have already been included in a capstone submission. Each student may participate in only one submission.',
                    ]);
                }

                if (StudentCapstoneSubmission::query()->where('content_hash', $contentHash)->exists()) {
                    return back()->withInput()->withErrors([
                        'title' => 'This title and abstract have already been submitted. Please check your title and abstract because the same study is already on record.',
                    ]);
                }

                $directory = 'capstone-submissions/'.auth()->id();
                $studyPath = $validated['study']->store($directory, 'local');
                $storedPaths[] = $studyPath;
                $manualPath = $validated['manual']->store($directory, 'local');
                $storedPaths[] = $manualPath;
                $systemPath = $validated['system']->store($directory, 'local');
                $storedPaths[] = $systemPath;
                $menteeFormPath = $validated['mentee_form']->store($directory, 'local');
                $storedPaths[] = $menteeFormPath;

                $submission = StudentCapstoneSubmission::create([
                    'submitted_by' => auth()->id(),
                    'title' => $validated['title'],
                    'abstract' => $validated['abstract'],
                    'adviser' => $adviser->id,
                    'content_hash' => $contentHash,
                    'submitted_date' => $validated['submitted_date'],
                    'study_path' => $studyPath,
                    'manual_path' => $manualPath,
                    'system_path' => $systemPath,
                    'mentee_form_path' => $menteeFormPath,
                    'status' => 'submitted',
                ]);

                $users = User::query()
                    ->whereIn('student_number', $authors->pluck('student_number'))
                    ->get()
                    ->keyBy('student_number');

                $authors->each(function (StudentMasterlistRecord $author) use ($submission, $users): void {
                    $user = $users->get($author->student_number);

                    StudentCapstoneAuthor::create([
                        'student_capstone_submission_id' => $submission->id,
                        'student_masterlist_record_id' => $author->id,
                        'user_id' => $user?->id,
                        'is_leader' => $user?->is(auth()->user()) ?? false,
                    ]);
                });

                return $submission;
            });
        } catch (QueryException $exception) {
            if ($storedPaths !== []) {
                Storage::disk('local')->delete($storedPaths);
            }

            if (! $this->isDuplicateContentHash($exception)) {
                throw $exception;
            }

            return back()->withInput()->withErrors([
                'title' => 'This title and abstract have already been submitted. Please check your title and abstract because the same study is already on record.',
            ]);
        }

        if ($submission instanceof RedirectResponse) {
            return $submission;
        }

        return redirect()->route('student.submissions.create')
            ->with('success', 'Your complete capstone submission was uploaded successfully.');
    }

    public function update(Request $request, StudentCapstoneSubmission $submission): RedirectResponse
    {
        abort_unless($submission->submitted_by === auth()->id(), 403);
        abort_unless($submission->status === 'returned', 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string'],
            'adviser' => ['required', 'integer', 'exists:users,id'],
            'submitted_date' => ['required', 'date'],
            'study' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
            'manual' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
            'system' => ['nullable', 'file', 'mimes:zip', 'max:204800'],
            'mentee_form' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
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

        if (StudentCapstoneSubmission::query()
            ->where('content_hash', $contentHash)
            ->where('id', '!=', $submission->id)
            ->exists()) {
            return back()->withInput()->withErrors([
                'title' => 'This title and abstract have already been submitted. Please check your title and abstract because the same study is already on record.',
            ]);
        }

        $storedPaths = [];
        $previousPaths = [];

        try {
            $updated = DB::transaction(function () use ($validated, $adviser, $submission, $contentHash, &$storedPaths, &$previousPaths): bool {
                $lockedSubmission = StudentCapstoneSubmission::query()
                    ->whereKey($submission->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedSubmission->submitted_by !== auth()->id() || $lockedSubmission->status !== 'returned') {
                    return false;
                }

                $attributes = [
                    'title' => $validated['title'],
                    'abstract' => $validated['abstract'],
                    'adviser' => $adviser->id,
                    'content_hash' => $contentHash,
                    'submitted_date' => $validated['submitted_date'],
                    'status' => 'submitted',
                    'return_reason' => null,
                ];
                $directory = 'capstone-submissions/'.auth()->id();

                foreach ([
                    'study' => 'study_path',
                    'manual' => 'manual_path',
                    'system' => 'system_path',
                    'mentee_form' => 'mentee_form_path',
                ] as $field => $pathColumn) {
                    if (! isset($validated[$field])) {
                        continue;
                    }

                    $newPath = $validated[$field]->store($directory, 'local');
                    $storedPaths[] = $newPath;
                    $previousPaths[] = $lockedSubmission->{$pathColumn};
                    $attributes[$pathColumn] = $newPath;
                }

                $lockedSubmission->update($attributes);

                return true;
            });
        } catch (QueryException $exception) {
            if ($storedPaths !== []) {
                Storage::disk('local')->delete($storedPaths);
            }

            if (! $this->isDuplicateContentHash($exception)) {
                throw $exception;
            }

            return back()->withInput()->withErrors([
                'title' => 'This title and abstract have already been submitted. Please check your title and abstract because the same study is already on record.',
            ]);
        }

        if (! $updated) {
            if ($storedPaths !== []) {
                Storage::disk('local')->delete($storedPaths);
            }

            abort(403);
        }

        if ($previousPaths !== []) {
            Storage::disk('local')->delete($previousPaths);
        }

        return redirect()->route('student.submissions.create')
            ->with('success', 'Your changes were resubmitted for review.');
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

    private function isDuplicateContentHash(QueryException $exception): bool
    {
        return str_contains($exception->getMessage(), 'student_capstone_submissions_content_hash_unique')
            || str_contains($exception->getMessage(), 'student_capstone_submissions.content_hash');
    }
}
