<?php

namespace App\Http\Controllers;

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

        $capstoneStatuses = DB::table('group_capstone_members')
            ->join('group_capstone_submissions', 'group_capstone_submissions.id', '=', 'group_capstone_members.group_capstone_submission_id')
            ->leftJoin('student_masterlist_records', 'student_masterlist_records.id', '=', 'group_capstone_members.student_masterlist_record_id')
            ->leftJoin('users', 'users.id', '=', 'group_capstone_members.student_id')
            ->where(function ($query): void {
                $query->whereNotNull('group_capstone_members.student_id')
                    ->orWhereNotNull('group_capstone_members.student_masterlist_record_id');
            })
            ->selectRaw('COALESCE(student_masterlist_records.student_number, users.student_number) as student_number')
            ->addSelect('group_capstone_submissions.status', 'group_capstone_submissions.updated_at')
            ->get()
            ->merge(
                DB::table('student_capstone_authors')
                    ->join('student_capstone_submissions', 'student_capstone_submissions.id', '=', 'student_capstone_authors.student_capstone_submission_id')
                    ->join('student_masterlist_records', 'student_masterlist_records.id', '=', 'student_capstone_authors.student_masterlist_record_id')
                    ->select('student_masterlist_records.student_number', 'student_capstone_submissions.status', 'student_capstone_submissions.updated_at')
                    ->get(),
            )
            ->merge(
                DB::table('student_capstone_submissions')
                    ->join('users', 'users.id', '=', 'student_capstone_submissions.submitted_by')
                    ->select('users.student_number', 'student_capstone_submissions.status', 'student_capstone_submissions.updated_at')
                    ->get(),
            )
            ->filter(fn (object $submission): bool => $submission->student_number !== null)
            ->sortByDesc('updated_at')
            ->groupBy('student_number')
            ->map(fn ($submissions): string => $submissions->first()->status);

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
            'capstoneStatuses' => $capstoneStatuses,
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
