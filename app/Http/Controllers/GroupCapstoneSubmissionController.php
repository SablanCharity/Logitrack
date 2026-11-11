<?php

namespace App\Http\Controllers;

use App\Models\GroupCapstoneMember;
use App\Models\GroupCapstoneSubmission;
use App\Models\StudentCapstoneSubmission;
use App\Models\StudentMasterlistRecord;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GroupCapstoneSubmissionController extends Controller
{
    public function create(): View
    {
        return view('student.group-submission-create', [
            'masterlistRecords' => StudentMasterlistRecord::query()
                ->orderBy('program')
                ->orderBy('name')
                ->orderBy('student_number')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program' => ['required', 'in:BSCS,BSIT'],
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string'],
            'adviser' => ['required', 'string', 'max:255'],
            'submitted_date' => ['required', 'date'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*' => ['required', 'integer', 'distinct'],
            'study' => ['required', 'file', 'mimes:pdf', 'max:51200'],
            'system' => ['required', 'file', 'mimes:zip', 'max:204800'],
            'document' => ['required', 'file', 'mimes:docx', 'max:20480'],
            'mentee_form' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
        ]);

        $masterlist = StudentMasterlistRecord::query()
            ->where('program', $validated['program'])
            ->whereIn('id', $validated['authors'])
            ->get()
            ->keyBy('id');

        if ($masterlist->count() !== count($validated['authors'])) {
            return back()->withInput()->withErrors([
                'authors' => 'Every author must be selected from the masterlist for the selected program.',
            ]);
        }

        $contentHash = hash('sha256', $this->normalize($validated['title']).'|'.$this->normalize($validated['abstract']));

        if (GroupCapstoneSubmission::where('content_hash', $contentHash)->exists()) {
            return back()->withInput()->withErrors([
                'title' => 'This title and abstract have already been submitted. Please check your title and abstract because the same study is already on record.',
            ]);
        }

        $directory = 'group-capstone-submissions/'.auth()->id();
        DB::transaction(function () use ($validated, $masterlist, $contentHash, $directory): void {
            $submission = GroupCapstoneSubmission::create([
                'submitted_by' => auth()->id(),
                'program' => $validated['program'],
                'title' => $validated['title'],
                'abstract' => $validated['abstract'],
                'adviser' => $validated['adviser'],
                'submitted_date' => $validated['submitted_date'],
                'study_path' => $validated['study']->store($directory, 'local'),
                'system_path' => $validated['system']->store($directory, 'local'),
                'document_path' => $validated['document']->store($directory, 'local'),
                'mentee_form_path' => $validated['mentee_form']->store($directory, 'local'),
                'content_hash' => $contentHash,
                'status' => 'pending',
            ]);

            $masterlist->each(function (StudentMasterlistRecord $student) use ($submission): void {
                $user = User::where('student_number', $student->student_number)->first();

                GroupCapstoneMember::create([
                    'group_capstone_submission_id' => $submission->id,
                    'student_id' => $user?->id,
                    'student_masterlist_record_id' => $student->id,
                    'is_leader' => $user?->is(auth()->user()) ?? false,
                ]);
            });
        });

        return redirect()->route('student.group-submissions.create')
            ->with('success', 'Submitted capstone and group members saved successfully.');
    }

    public function index(): View
    {
        $this->ensureAdmin();
        $program = request('program');

        $allStudentSubmissions = StudentCapstoneSubmission::with([
            'submitter',
            'adviserUser',
            'authors.masterlistRecord',
        ])->latest()->get();
        $studentSubmissions = $allStudentSubmissions
            ->when(
                in_array($program, ['BSCS', 'BSIT'], true),
                fn ($submissions) => $submissions->filter(
                    fn (StudentCapstoneSubmission $submission): bool => $submission->submitter?->program === $program
                )
            )
            ->values();

        $submittedStudentNumbers = $allStudentSubmissions
            ->flatMap(function (StudentCapstoneSubmission $submission): array {
                $authorNumbers = $submission->authors
                    ->map(fn ($author): ?string => $author->masterlistRecord?->student_number)
                    ->filter()
                    ->all();

                return array_values(array_filter([
                    $submission->submitter?->student_number,
                    ...$authorNumbers,
                ]));
            })
            ->filter()
            ->unique()
            ->values();

        $counts = [
            'enrolled_cscs' => StudentMasterlistRecord::where('program', 'BSCS')->count(),
            'enrolled_it' => StudentMasterlistRecord::where('program', 'BSIT')->count(),
            'submitted' => $submittedStudentNumbers->count(),
            'pending' => $allStudentSubmissions->where('status', 'submitted')->count(),
            'approved' => $allStudentSubmissions->where('status', 'approved')->count(),
        ];
        $counts['not_submitted'] = max(
            0,
            $counts['enrolled_cscs'] + $counts['enrolled_it'] - $counts['submitted']
        );

        return view('admin.group-submissions', compact('studentSubmissions', 'counts', 'program'));
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403);
    }

    private function normalize(string $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim($value))) ?? trim($value);
    }
}
