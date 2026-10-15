<?php

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

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
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

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [RepositoryController::class, 'index'])->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Student Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/student/pending', function () {
        return view('student.pending');
    })->name('student.pending');


    /*
    |--------------------------------------------------------------------------
    | Student Group Rooms
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/student/classrooms/{classroomId}/groups',
        [GroupRoomController::class, 'index']
    )->name('student.group-rooms.index');

    Route::get(
        '/student/classrooms/{classroomId}/groups/create',
        [GroupRoomController::class, 'create']
    )->name('student.group-rooms.create');

    Route::post(
        '/student/classrooms/{classroomId}/groups',
        [GroupRoomController::class, 'store']
    )->name('student.group-rooms.store');

    Route::get(
        '/student/group-rooms/{groupRoomId}',
        [GroupRoomController::class, 'show']
    )->name('student.group-room.show');


    /*
    |--------------------------------------------------------------------------
    | Student Capstone Routes
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/student/group-rooms/{groupRoomId}/capstone/create',
        [CapstoneProjectController::class, 'create']
    )->name('student.capstone.create');

    Route::post(
        '/student/group-rooms/{groupRoomId}/capstone',
        [CapstoneProjectController::class, 'store']
    )->name('student.capstone.store');

    Route::get(
        '/student/group-rooms/{groupRoomId}/capstone/edit',
        [CapstoneProjectController::class, 'edit']
    )->name('student.capstone.edit');

    Route::put(
        '/student/group-rooms/{groupRoomId}/capstone',
        [CapstoneProjectController::class, 'update']
    )->name('student.capstone.update');


    /*
    |--------------------------------------------------------------------------
    | Instructor Routes
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/instructor/students',
        [InstructorController::class, 'students']
    )->name('instructor.students');

    Route::get(
        '/instructor/classrooms',
        [ClassroomController::class, 'index']
    )->name('instructor.classrooms');

    Route::get(
        '/instructor/classrooms/create',
        [ClassroomController::class, 'create']
    )->name('instructor.classrooms.create');

    Route::post(
        '/instructor/classrooms',
        [ClassroomController::class, 'store']
    )->name('instructor.classrooms.store');

    Route::get(
        '/instructor/classrooms/{classroomId}',
        [ClassroomController::class, 'show']
    )->name('instructor.classroom.show');


    /*
    |--------------------------------------------------------------------------
    | Instructor Capstone Review
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/instructor/capstones',
        [CapstoneProjectController::class, 'instructorIndex']
    )->name('instructor.capstones');

    Route::get(
        '/instructor/capstones/{capstoneId}',
        [CapstoneProjectController::class, 'instructorReview']
    )->name('instructor.capstone.review');

    Route::patch(
        '/instructor/capstones/{capstoneId}/verify',
        [CapstoneProjectController::class, 'instructorVerify']
    )->name('instructor.capstone.verify');

    Route::patch(
        '/instructor/capstones/{capstoneId}/return',
        [CapstoneProjectController::class, 'instructorReturn']
    )->name('instructor.capstone.return');


    /*
    |--------------------------------------------------------------------------
    | Admin Capstone Review
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/capstones',
        [CapstoneProjectController::class, 'adminIndex']
    )->name('admin.capstones');

    Route::get(
        '/admin/capstones/{capstoneId}',
        [CapstoneProjectController::class, 'adminReview']
    )->name('admin.capstone.review');

    Route::patch(
        '/admin/capstones/{capstoneId}/approve',
        [CapstoneProjectController::class, 'adminApprove']
    )->name('admin.capstone.approve');

    Route::patch(
        '/admin/capstones/{capstoneId}/return',
        [CapstoneProjectController::class, 'adminReturn']
    )->name('admin.capstone.return');

    Route::patch(
        '/admin/repository/{capstoneId}/best',
        [CapstoneProjectController::class, 'setBestStudy']
    )->middleware('admin')->name('admin.capstone.set-best');


    /*
    |--------------------------------------------------------------------------
    | Repository
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/repository',
        [RepositoryController::class, 'index']
    )->name('repository.index');

    Route::get(
        '/repository/{capstoneId}',
        [RepositoryController::class, 'show']
    )->name('repository.show');


    /*
    |--------------------------------------------------------------------------
    | Similarity Checker
    |--------------------------------------------------------------------------
    |
    | GET  = display the Similarity Checker form
    | POST = process the proposed study
    |
    */

    Route::get(
        '/similarity-checker',
        [SimilarityController::class, 'index']
    )->name('similarity.index');

    Route::post(
        '/similarity-checker',
        [SimilarityController::class, 'check']
    )->name('similarity.check');

    // Student: add a member to a group room (leader only)
Route::post(
    '/student/group-rooms/{groupRoomId}/members',
    [GroupRoomController::class, 'addMember']
)->name('student.group-room.add-member');

// Instructor: approve a pending student
Route::post(
    '/instructor/students/{userId}/approve',
    [InstructorController::class, 'approveStudent']
)->name('instructor.students.approve');

});




/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Login, registration, password reset, etc.
|
*/

require __DIR__ . '/auth.php';
