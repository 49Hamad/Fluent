<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Only a signed-in STUDENT (guard "student") may pass. Employees' Filament login does not count. */
class EnsureStudentIsAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('student')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'انتهت الجلسة. سجّل الدخول مرة أخرى.'], 401);
            }
            return redirect()->route('fluent.login');
        }

        $response = $next($request);
        // Personal pages: never cached by the browser or proxies.
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
