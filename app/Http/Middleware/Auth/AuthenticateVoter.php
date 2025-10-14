<?php

declare(strict_types=1);

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateVoter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if voter is authenticated
        if (! Auth::guard('voter')->check()) {
            // If request expects JSON, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated. Voter access required.',
                ], 401);
            }

            // Otherwise, redirect to voter login
            return redirect()->route('voter.login');
        }

        // Verify the authenticated user is a valid voter
        $voter = Auth::guard('voter')->user();
        if (! $voter) {
            Auth::guard('voter')->logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Invalid voter.',
                ], 403);
            }

            return redirect()->route('voter.login')->withErrors([
                'voter' => 'Invalid voter.',
            ]);
        }

        return $next($request);
    }
}
