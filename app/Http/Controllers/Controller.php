<?php

namespace App\Http\Controllers;

use App\Services\LoggingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

abstract class Controller
{
    /**
     * Return a success JSON response
     */
    protected function successResponse(string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Return an error JSON response
     */
    protected function errorResponse(string $message, mixed $errors = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Return a redirect with success message
     */
    protected function redirectWithSuccess(string $route, string $message): RedirectResponse
    {
        return redirect()->route($route)->with('success', $message);
    }

    /**
     * Return a redirect with error message
     */
    protected function redirectWithError(string $route, string $message): RedirectResponse
    {
        return redirect()->route($route)->with('error', $message);
    }

    /**
     * Log an admin action (convenience method)
     */
    protected function logAction(string $actionType, string $description, ?array $metadata = null): void
    {
        LoggingService::logAdminAction($actionType, $description, metadata: $metadata);
    }

    /**
     * Log a model creation (convenience method)
     */
    protected function logCreate(\Illuminate\Database\Eloquent\Model $model, ?string $customDescription = null): void
    {
        LoggingService::logCreate($model, $customDescription);
    }

    /**
     * Log a model update (convenience method)
     */
    protected function logUpdate(\Illuminate\Database\Eloquent\Model $model, array $oldValues, ?string $customDescription = null): void
    {
        LoggingService::logUpdate($model, $oldValues, $customDescription);
    }

    /**
     * Log a model deletion (convenience method)
     */
    protected function logDelete(\Illuminate\Database\Eloquent\Model $model, ?string $customDescription = null): void
    {
        LoggingService::logDelete($model, $customDescription);
    }

    /**
     * Validate and return validated data
     */
    protected function validateRequest(array $rules, array $messages = []): array
    {
        return request()->validate($rules, $messages);
    }

    /**
     * Get the authenticated admin user
     */
    protected function getAuthAdmin(): ?\App\Models\Admin
    {
        return auth()->guard('admin')->user();
    }

    /**
     * Get the authenticated voter
     */
    protected function getAuthVoter(): ?\App\Models\Voter
    {
        return auth()->guard('voter')->user();
    }

    /**
     * Check if user is authenticated as admin
     */
    protected function isAdminAuthenticated(): bool
    {
        return auth()->guard('admin')->check();
    }

    /**
     * Check if user is authenticated as voter
     */
    protected function isVoterAuthenticated(): bool
    {
        return auth()->guard('voter')->check();
    }

    /**
     * Get current election status
     */
    protected function getCurrentElection(): ?\App\Models\Election
    {
        return \App\Models\Election::first();
    }

    /**
     * Check if election is active
     */
    protected function isElectionActive(): bool
    {
        $election = $this->getCurrentElection();

        return $election && $election->isActive();
    }

    /**
     * Abort if election is not active
     */
    protected function abortIfElectionNotActive(string $message = 'Election is not currently active.'): void
    {
        if (! $this->isElectionActive()) {
            abort(403, $message);
        }
    }
}
