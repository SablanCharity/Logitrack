<?php

use App\Http\Controllers\OldCapstoneController;
use App\Http\Controllers\CapstoneProjectController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\GroupRoomController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\SimilarityController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes for the Logitrack web application.
|
*/

Route::get('/', function () {
    $instructors = User::query()
        ->where('role', 'instructor')
        ->where('is_approved', true)
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();

    return view('welcome', compact('instructors'));
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [RepositoryController::class, 'index'])->name('dashboard');

    /* Profile */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* Student Routes */
    Route::get('/student/pending', function () {
        if (auth()->check() && auth()->user()->is_approved) {
            return redirect()->route('dashboard');
        }
        return view('student.pending');
    })->name('student.pending');

    Route::get('/student/classroom-redirect', function () {
        $membership = \App\Models\ClassroomMember::where('user_id', auth()->id())->first();

        if (!$membership) {
            return redirect()->route('dashboard')->with('error', 'You are not assigned to any classroom yet.');
        }

        return redirect()->route('student.group-rooms.index', ['classroomId' => $membership->classroom_id]);
    })->name('student.classroom.redirect');

    Route::get('/student/classrooms/{classroomId}/groups', [GroupRoomController::class, 'index'])->name('student.group-rooms.index');
    Route::get('/student/classrooms/{classroomId}/groups/create', [GroupRoomController::class, 'create'])->name('student.group-rooms.create');
    Route::post('/student/classrooms/{classroomId}/groups', [GroupRoomController::class, 'store'])->name('student.group-rooms.store');
    Route::get('/student/group-rooms/{groupRoomId}', [GroupRoomController::class, 'show'])->name('student.group-room.show');
    Route::post('/student/group-rooms/{groupRoomId}/members', [GroupRoomController::class, 'addMember'])->name('student.group-room.add-member');

    /* Student Capstone Routes */
    Route::get('/student/group-rooms/{groupRoomId}/capstone/create', [CapstoneProjectController::class, 'create'])->name('student.capstone.create');
    Route::post('/student/group-rooms/{groupRoomId}/capstone', [CapstoneProjectController::class, 'store'])->name('student.capstone.store');
    Route::get('/student/group-rooms/{groupRoomId}/capstone/edit', [CapstoneProjectController::class, 'edit'])->name('student.capstone.edit');
    Route::put('/student/group-rooms/{groupRoomId}/capstone', [CapstoneProjectController::class, 'update'])->name('student.capstone.update');

    /* Instructor Routes */
    Route::get('/instructor/students', [InstructorController::class, 'students'])->name('instructor.students');
    Route::post('/instructor/students/{studentId}/approve', [InstructorController::class, 'approveStudent'])->name('instructor.students.approve');
    Route::get('/instructor/classrooms', [ClassroomController::class, 'index'])->name('instructor.classrooms');
    Route::get('/instructor/classrooms/create', [ClassroomController::class, 'create'])->name('instructor.classrooms.create');
    Route::post('/instructor/classrooms', [ClassroomController::class, 'store'])->name('instructor.classrooms.store');
    Route::get('/instructor/classrooms/{classroomId}', [ClassroomController::class, 'show'])->name('instructor.classroom.show');

    /* Instructor Capstone Review */
    Route::get('/instructor/capstones', [CapstoneProjectController::class, 'instructorIndex'])->name('instructor.capstones');
    Route::get('/instructor/capstones/{capstoneId}', [CapstoneProjectController::class, 'instructorReview'])->name('instructor.capstone.review');
    Route::patch('/instructor/capstones/{capstoneId}/verify', [CapstoneProjectController::class, 'instructorVerify'])->name('instructor.capstone.verify');
    Route::patch('/instructor/capstones/{capstoneId}/return', [CapstoneProjectController::class, 'instructorReturn'])->name('instructor.capstone.return');

    /* Admin Capstone Review */
    Route::get('/admin/capstones', [CapstoneProjectController::class, 'adminIndex'])->name('admin.capstones');
    Route::get('/admin/capstones/{capstoneId}', [CapstoneProjectController::class, 'adminReview'])->name('admin.capstone.review');
    Route::patch('/admin/capstones/{capstoneId}/approve', [CapstoneProjectController::class, 'adminApprove'])->name('admin.capstone.approve');
    Route::patch('/admin/capstones/{capstoneId}/return', [CapstoneProjectController::class, 'adminReturn'])->name('admin.capstone.return');
    Route::patch('/admin/repository/{capstoneId}/best', [CapstoneProjectController::class, 'setBestStudy'])
        ->middleware('admin')
        ->name('admin.capstone.set-best');

    /* Repository */
    Route::get('/repository', [RepositoryController::class, 'index'])->name('repository.index');
    Route::get('/repository/{capstoneId}', [RepositoryController::class, 'show'])->name('repository.show');

    /* Similarity Checker */
    Route::get('/similarity-checker', [SimilarityController::class, 'index'])->name('similarity.index');
    Route::post('/similarity-checker', [SimilarityController::class, 'check'])->name('similarity.check');

    /*
    |--------------------------------------------------------------------------
    | Legacy OCR Capstone Routes (Old Capstones)
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/old-capstones/create', [OldCapstoneController::class, 'create'])->name('ocr.upload');
    Route::post('/admin/old-capstones', [OldCapstoneController::class, 'store'])->name('ocr.store');
    Route::get('/admin/old-capstones/{oldCapstone}/review', [OldCapstoneController::class, 'review'])->name('ocr.review');
    Route::put('/admin/old-capstones/{oldCapstone}', [OldCapstoneController::class, 'update'])->name('ocr.update');
    Route::patch('/admin/old-capstones/{oldCapstone}/approve', [OldCapstoneController::class, 'approve'])->name('ocr.approve');

});

require __DIR__ . '/auth.php';