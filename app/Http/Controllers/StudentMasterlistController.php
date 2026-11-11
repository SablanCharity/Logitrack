<?php

namespace App\Http\Controllers;

use App\Models\GroupCapstoneMember;
use App\Models\StudentCapstoneAuthor;
use App\Models\StudentCapstoneSubmission;
use App\Models\StudentMasterlistRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentMasterlistController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'program_filter' => ['nullable', 'in:BSCS,BSIT'],
        ]);
        $search = trim((string) ($validated['search'] ?? ''));
        $programFilter = $validated['program_filter'] ?? null;

        $submittedStudentNumbers = GroupCapstoneMember::query()
            ->join('student_masterlist_records', 'student_masterlist_records.id', '=', 'group_capstone_members.student_masterlist_record_id')
            ->pluck('student_masterlist_records.student_number')
            ->merge(
                StudentCapstoneAuthor::query()
                    ->join('student_masterlist_records', 'student_masterlist_records.id', '=', 'student_capstone_authors.student_masterlist_record_id')
                    ->pluck('student_masterlist_records.student_number'),
            )
            ->merge(
                GroupCapstoneMember::query()
                    ->join('users', 'users.id', '=', 'group_capstone_members.student_id')
                    ->pluck('users.student_number'),
            )
            ->merge(
                StudentCapstoneSubmission::query()
                    ->join('users', 'users.id', '=', 'student_capstone_submissions.submitted_by')
                    ->pluck('users.student_number'),
            )
            ->unique()
            ->flip();

        return view('admin.student-masterlist', [
            'records' => StudentMasterlistRecord::query()
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('student_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                })
                ->when($programFilter, fn ($query) => $query->where('program', $programFilter))
                ->latest()
                ->get(),
            'submittedStudentNumbers' => $submittedStudentNumbers,
            'search' => $search,
            'programFilter' => $programFilter,
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'masterlist' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'program' => ['required', 'in:BSCS,BSIT'],
        ]);

        $handle = fopen($validated['masterlist']->getRealPath(), 'rb');
        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), fgetcsv($handle) ?: []);
        $required = ['student_number', 'name'];

        if (array_diff($required, $headers) !== []) {
            fclose($handle);

            return back()->withErrors([
                'masterlist' => 'The CSV must include student_number and name columns.',
            ]);
        }

        DB::transaction(function () use ($handle, $headers, $validated): void {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($headers, array_pad($row, count($headers), null));

                if (! $data || trim((string) ($data['student_number'] ?? '')) === '') {
                    continue;
                }

                StudentMasterlistRecord::updateOrCreate(
                    ['student_number' => trim($data['student_number'])],
                    [
                        'name' => trim((string) ($data['name'] ?? '')) ?: null,
                        'email' => trim((string) ($data['email'] ?? '')) ?: null,
                        'program' => $validated['program'],
                        'status' => 'listed',
                        'source_file' => $validated['masterlist']->getClientOriginalName(),
                    ],
                );
            }

            fclose($handle);
        });

        return back()->with('success', 'Masterlist uploaded successfully. All extracted students are enrolled and available for registration.');
    }
}
