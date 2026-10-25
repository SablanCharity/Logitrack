<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            ->findOrFail($classroomId);

        return view('instructor.classroom-show', compact('classroom'));
    }

    public function students(int $classroomId): View
    {
        $classroom = auth()->user()
            ->classrooms()
            ->with('members.user')
            ->findOrFail($classroomId);

        return view('instructor.classroom-section', [
            'classroom' => $classroom,
            'section' => 'students',
        ]);
    }

    public function groupRooms(int $classroomId): View
    {
        $classroom = auth()->user()
            ->classrooms()
            ->with(['groupRooms.members.user', 'groupRooms.capstoneProject'])
            ->findOrFail($classroomId);

        return view('instructor.classroom-section', [
            'classroom' => $classroom,
            'section' => 'group-rooms',
        ]);
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
            'CLS-'.Str::random(6)
        );

        auth()->user()->classrooms()->create([
            'classroom_name' => $request->classroom_name,
            'class_code' => $classCode,
        ]);

        return redirect()
            ->route('instructor.classrooms')
            ->with('success', 'Classroom created successfully.');
    }
}
