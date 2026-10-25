<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminOrOjt
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->user()?->role, ['admin', 'ojt'], true)) {
            abort(403, 'You are not authorized to access OCR review.');
        }

        return $next($request);
    }
}
