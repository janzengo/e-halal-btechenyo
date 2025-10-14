<?php

declare(strict_types=1);

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOfficerRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ensure user is authenticated as admin first
        if (! Auth::guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->route('admin.login');
        }

        // Check if the authenticated admin has officer role
        $admin = Auth::guard('admin')->user();
        if (! $admin || $admin->role !== 'officer') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Access denied. Officer role required.',
                ], 403);
            }

            return redirect()->route('admin.dashboard')->withErrors([
                'role' => 'Access denied. Officer role required.',
            ]);
        }

        return $next($request);
    }
}
