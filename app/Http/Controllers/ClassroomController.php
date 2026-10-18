<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(): View
    {
        $classrooms = auth()->user()
            ->classrooms()
            ->latest()
            ->get();

        return view('instructor.classrooms', compact('classrooms'));
    }

    public function show(int $classroomId): View
    {
        $classroom = auth()->user()
            ->classrooms()
            ->with(['members.user'])
            ->findOrFail($classroomId);

        return view('instructor.classroom-show', compact('classroom'));
    }

    public function create(): View
    {
        return view('instructor.classrooms-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'classroom_name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $classCode = strtoupper(
            'CLS-' . \Illuminate\Support\Str::random(6)
        );

        auth()->user()->classrooms()->create([
            'classroom_name' => $request->classroom_name,
            'class_code' => $classCode,
        ]);

        return redirect()
            ->route('instructor.classrooms')
            ->with('success', 'Classroom created successfully.');
    }

    /**
     * Save the group creation opening and closing times.
     * Only the instructor who owns the classroom can update them.
     */
    public function updateGroupCreationPeriod(
        Request $request,
        int $classroomId
    ): RedirectResponse {
        $classroom = auth()->user()
            ->classrooms()
            ->findOrFail($classroomId);

        $validated = $request->validate([
            'group_creation_opens_at' => [
                'nullable',
                'date',
            ],
            'group_creation_closes_at' => [
                'nullable',
                'date',
            ],
        ]);

        $opensAt = $validated['group_creation_opens_at'] ?? null;
        $closesAt = $validated['group_creation_closes_at'] ?? null;

        if (
            $opensAt !== null &&
            $closesAt !== null &&
            strtotime($closesAt) <= strtotime($opensAt)
        ) {
            return back()
                ->withErrors([
                    'group_creation_closes_at' =>
                        'The closing date and time must be after the opening date and time.',
                ])
                ->withInput();
        }

        $classroom->update([
            'group_creation_opens_at' => $opensAt,
            'group_creation_closes_at' => $closesAt,
        ]);

        return redirect()
            ->route('instructor.classroom.show', $classroom->id)
            ->with(
                'success',
                'Group creation period updated successfully.'
            );
    }
}
