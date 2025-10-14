<?php

declare(strict_types=1);

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Debug: Log middleware check
        \Log::info('AuthenticateAdmin middleware check', [
            'url' => $request->url(),
            'session_id' => $request->session()->getId(),
            'is_authenticated' => Auth::guard('admin')->check(),
        ]);

        // Check if user is authenticated as admin (officer or head)
        if (! Auth::guard('admin')->check()) {
            \Log::info('Admin not authenticated, redirecting to login');

            // If request expects JSON, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated. Admin access required.',
                ], 401);
            }

            // Otherwise, redirect to admin login
            return redirect()->route('admin.login');
        }

        // Verify the authenticated user is an admin (officer or head)
        $admin = Auth::guard('admin')->user();
        if (! $admin || ! in_array($admin->role, ['officer', 'head'])) {
            \Log::info('Invalid admin role', [
                'admin' => $admin ? $admin->username : 'null',
                'role' => $admin ? $admin->role : 'null',
            ]);

            Auth::guard('admin')->logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Invalid admin role.',
                ], 403);
            }

            return redirect()->route('admin.login')->withErrors([
                'role' => 'Invalid admin role.',
            ]);
        }

        \Log::info('Admin authenticated successfully', [
            'username' => $admin->username,
            'role' => $admin->role,
        ]);

        return $next($request);
    }
}
