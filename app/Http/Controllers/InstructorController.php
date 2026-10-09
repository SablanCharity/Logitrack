<?php

namespace App\Http\Controllers;

use App\Models\ClassroomMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InstructorController extends Controller
{
    public function students(): View
    {
        $instructor = auth()->user();

        $classroomIds = $instructor->classrooms()->pluck('id');

        $students = ClassroomMember::with('user')
            ->whereIn('classroom_id', $classroomIds)
            ->where('status', 'pending')
            ->get()
            ->pluck('user');

        return view('instructor.students', compact('students'));
    }

    public function approveStudent(int $studentId): RedirectResponse
    {
        $instructor = auth()->user();

        $classroomIds = $instructor->classrooms()->pluck('id');

        $membership = ClassroomMember::whereIn('classroom_id', $classroomIds)
            ->where('user_id', $studentId)
            ->where('status', 'pending')
            ->firstOrFail();

        $membership->update([
            'status' => 'approved',
        ]);

        $membership->user->update([
            'is_approved' => true,
        ]);

        return back()->with('success', 'Student approved successfully.');
    }
}