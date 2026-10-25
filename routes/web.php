<?php

use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\ArchivedCapstoneController;
use App\Http\Controllers\CapstoneProjectController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\GroupRoomController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\SimilarityController;
use App\Models\ClassroomMember;
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

Route::middleware(['auth', 'active'])->group(function () {

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
        $membership = ClassroomMember::where('user_id', auth()->id())->first();

        if (! $membership) {
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
    Route::get('/instructor/classrooms/{classroomId}/students', [ClassroomController::class, 'students'])->name('instructor.classroom.students');
    Route::get('/instructor/classrooms/{classroomId}/group-rooms', [ClassroomController::class, 'groupRooms'])->name('instructor.classroom.group-rooms');

    /* Instructor Capstone Review */
    Route::get('/instructor/capstones', [CapstoneProjectController::class, 'instructorIndex'])->name('instructor.capstones');
    Route::get('/instructor/capstones/{capstoneId}', [CapstoneProjectController::class, 'instructorReview'])->name('instructor.capstone.review');
    Route::patch('/instructor/capstones/{capstoneId}/verify', [CapstoneProjectController::class, 'instructorVerify'])->name('instructor.capstone.verify');
    Route::patch('/instructor/capstones/{capstoneId}/return', [CapstoneProjectController::class, 'instructorReturn'])->name('instructor.capstone.return');

    /* Admin Capstone Review and Account Management */
    Route::middleware('admin')->group(function () {
        Route::get('/admin/capstones', [CapstoneProjectController::class, 'adminIndex'])->name('admin.capstones');
        Route::patch('/admin/capstones/submission-window', [CapstoneProjectController::class, 'toggleSubmissionWindow'])->name('admin.capstones.submission-window');
        Route::get('/admin/capstones/{capstoneId}', [CapstoneProjectController::class, 'adminReview'])->name('admin.capstone.review');
        Route::patch('/admin/capstones/{capstoneId}/approve', [CapstoneProjectController::class, 'adminApprove'])->name('admin.capstone.approve');
        Route::patch('/admin/capstones/{capstoneId}/return', [CapstoneProjectController::class, 'adminReturn'])->name('admin.capstone.return');
        Route::patch('/admin/repository/{capstoneId}/best', [CapstoneProjectController::class, 'setBestStudy'])
            ->name('admin.capstone.set-best');
        Route::get('/admin/repository/{capstoneId}/edit', [RepositoryController::class, 'edit'])->name('admin.repository.edit');
        Route::put('/admin/repository/{capstoneId}', [RepositoryController::class, 'update'])->name('admin.repository.update');
        Route::get('/admin/accounts/create', [AdminAccountController::class, 'create'])->name('admin.accounts.create');
        Route::get('/admin/accounts', [AdminAccountController::class, 'index'])->name('admin.accounts.index');
        Route::get('/admin/students', [AdminAccountController::class, 'students'])->name('admin.students.index');
        Route::post('/admin/accounts', [AdminAccountController::class, 'store'])->name('admin.accounts.store');
        Route::get('/admin/accounts/{user}/edit', [AdminAccountController::class, 'edit'])->name('admin.accounts.edit');
        Route::put('/admin/accounts/{user}', [AdminAccountController::class, 'update'])->name('admin.accounts.update');
        Route::patch('/admin/accounts/{user}/toggle-active', [AdminAccountController::class, 'toggleActive'])->name('admin.accounts.toggle-active');
        Route::delete('/admin/accounts/{user}', [AdminAccountController::class, 'destroy'])->name('admin.accounts.destroy');
    });

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
    Route::middleware('admin')->group(function () {
        Route::get('/admin/archived-capstones/create', [ArchivedCapstoneController::class, 'create'])->name('ocr.upload');
        Route::post('/admin/archived-capstones', [ArchivedCapstoneController::class, 'store'])->name('ocr.store');
    });
    Route::middleware('admin.ocr')->group(function () {
        Route::get('/admin/archived-capstones/{archivedCapstone}/review', [ArchivedCapstoneController::class, 'review'])->name('ocr.review');
        Route::put('/admin/archived-capstones/{archivedCapstone}', [ArchivedCapstoneController::class, 'update'])->name('ocr.update');
    });
    Route::middleware('admin')->group(function () {
        Route::patch('/admin/archived-capstones/{archivedCapstone}/approve', [ArchivedCapstoneController::class, 'approve'])->name('ocr.approve');
    });

});

require __DIR__.'/auth.php';
