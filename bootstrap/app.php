
<?php

use App\Http\Middleware\CheckActiveAccount;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckAdminOrOjt;
use App\Http\Middleware\CheckInstructorRole;
use App\Http\Middleware\CheckStudentApproval;
use App\Http\Middleware\CheckStudentRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'student.approved' => CheckStudentApproval::class,
            'admin' => CheckAdmin::class,
            'admin.ocr' => CheckAdminOrOjt::class,
            'active' => CheckActiveAccount::class,
            'student.role' => CheckStudentRole::class,
            'instructor' => CheckInstructorRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
