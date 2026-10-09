<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStudentApproval
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user &&
            $user->role === 'student' &&
            ! $user->is_approved
        ) {
            return redirect()->route('student.pending');
        }

        return $next($request);
    }
}