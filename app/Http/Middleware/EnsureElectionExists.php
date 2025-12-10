<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Election;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureElectionExists
{
    /**
     * Handle an incoming request.
     * Redirects to setup page if no election exists.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for setup-related routes
        if ($request->is('head/setup*') || $request->is('head/setup')) {
            return $next($request);
        }

        // Check if an election exists
        $election = Election::first();

        if (!$election) {
            // No election exists, redirect to setup
            return redirect()->route('head.setup');
        }

        return $next($request);
    }
}
