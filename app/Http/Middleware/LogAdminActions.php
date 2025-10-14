<?php

namespace App\Http\Middleware;

use App\Services\LoggingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log successful requests
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    /**
     * Log the request details
     */
    private function logRequest(Request $request, Response $response): void
    {
        $user = $request->user('admin');

        if (! $user) {
            return;
        }

        $action = $this->determineAction($request);

        if ($action) {
            LoggingService::logAdminAction(
                actionType: $action['type'],
                description: $action['description'],
                metadata: [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'status_code' => $response->getStatusCode(),
                ]
            );
        }
    }

    /**
     * Determine the action type and description based on the request
     */
    private function determineAction(Request $request): ?array
    {
        $method = $request->method();
        $path = $request->path();
        $user = $request->user('admin');

        // Login actions
        if (str_contains($path, 'admin/login') && $method === 'POST') {
            return [
                'type' => 'login',
                'description' => 'Admin login successful',
            ];
        }

        // Logout actions
        if (str_contains($path, 'admin/logout') && $method === 'POST') {
            return [
                'type' => 'logout',
                'description' => 'Admin logout',
            ];
        }

        // OTP verification
        if (str_contains($path, 'admin/otp-verify') && $method === 'POST') {
            return [
                'type' => 'otp_verification',
                'description' => 'OTP verification successful',
            ];
        }

        // Dashboard access
        if (str_contains($path, 'head/dashboard') && $method === 'GET') {
            return [
                'type' => 'dashboard_access',
                'description' => 'Accessed dashboard',
            ];
        }

        // Position management
        if (str_contains($path, 'head/positions') && $method === 'POST') {
            return [
                'type' => 'position_management',
                'description' => 'Position management action',
            ];
        }

        // Candidate management
        if (str_contains($path, 'head/candidates') && $method === 'POST') {
            return [
                'type' => 'candidate_management',
                'description' => 'Candidate management action',
            ];
        }

        // Voter management
        if (str_contains($path, 'head/voters') && $method === 'POST') {
            return [
                'type' => 'voter_management',
                'description' => 'Voter management action',
            ];
        }

        // Election management
        if (str_contains($path, 'head/elections') && $method === 'POST') {
            return [
                'type' => 'election_management',
                'description' => 'Election management action',
            ];
        }

        return null;
    }
}
