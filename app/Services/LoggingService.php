<?php

namespace App\Services;

use App\Models\AdminLog;
use App\Models\VoterLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoggingService
{
    /**
     * Log an admin action
     */
    public static function logAdminAction(
        string $actionType,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
        ?Request $request = null
    ): AdminLog {
        $request = $request ?? request();
        $user = Auth::guard('admin')->user();
        $election = \App\Models\ElectionStatus::first();

        return AdminLog::create([
            'user_id' => $user?->id,
            'role' => $user?->role ?? 'system',
            'action_type' => $actionType,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'action_description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'election_id' => $election?->id,
        ]);
    }

    /**
     * Log a voter action
     */
    public static function logVoterAction(
        string $actionType,
        string $description,
        ?string $voterId = null,
        ?array $metadata = null,
        ?Request $request = null
    ): VoterLog {
        $request = $request ?? request();
        $voter = Auth::guard('voter')->user();
        $election = \App\Models\ElectionStatus::first();

        return VoterLog::create([
            'voter_id' => $voterId ?? $voter?->student_number,
            'action_type' => $actionType,
            'action_description' => $description,
            'metadata' => $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'election_id' => $election?->id,
        ]);
    }

    /**
     * Log a model creation
     */
    public static function logCreate(Model $model, ?string $customDescription = null): AdminLog
    {
        $modelName = class_basename($model);
        $description = $customDescription ?? "Added {$modelName}: ".self::getModelIdentifier($model);

        return self::logAdminAction(
            actionType: 'create',
            description: $description,
            modelType: get_class($model),
            modelId: $model->id,
            newValues: $model->getAttributes(),
            metadata: ['model_class' => get_class($model)]
        );
    }

    /**
     * Log a model update
     */
    public static function logUpdate(Model $model, array $oldValues, ?string $customDescription = null): AdminLog
    {
        $modelName = class_basename($model);
        $description = $customDescription ?? "Updated {$modelName}: ".self::getModelIdentifier($model);

        return self::logAdminAction(
            actionType: 'update',
            description: $description,
            modelType: get_class($model),
            modelId: $model->id,
            oldValues: $oldValues,
            newValues: $model->getChanges(),
            metadata: ['model_class' => get_class($model)]
        );
    }

    /**
     * Log a model deletion
     */
    public static function logDelete(Model $model, ?string $customDescription = null): AdminLog
    {
        $modelName = class_basename($model);
        $description = $customDescription ?? "Deleted {$modelName}: ".self::getModelIdentifier($model);

        return self::logAdminAction(
            actionType: 'delete',
            description: $description,
            modelType: get_class($model),
            modelId: $model->id,
            oldValues: $model->getAttributes(),
            metadata: ['model_class' => get_class($model)]
        );
    }

    /**
     * Log a login action
     */
    public static function logLogin(string $userType, string $userId, ?string $customDescription = null): void
    {
        $description = $customDescription ?? 'Login successful';

        if ($userType === 'admin') {
            self::logAdminAction('login', $description);
        } else {
            self::logVoterAction('login', $description, $userId);
        }
    }

    /**
     * Log a logout action
     */
    public static function logLogout(string $userType, string $userId, ?string $customDescription = null): void
    {
        $description = $customDescription ?? 'User logged out';

        if ($userType === 'admin') {
            self::logAdminAction('logout', $description);
        } else {
            self::logVoterAction('logout', $description, $userId);
        }
    }

    /**
     * Log a vote submission
     */
    public static function logVoteSubmission(string $voterId, string $voteReference, array $votes = []): VoterLog
    {
        return self::logVoterAction(
            actionType: 'vote_submitted',
            description: "Vote submitted successfully. Reference: {$voteReference}",
            voterId: $voterId,
            metadata: [
                'vote_reference' => $voteReference,
                'votes' => $votes,
                'submitted_at' => now()->toISOString(),
            ]
        );
    }

    /**
     * Log election status change
     */
    public static function logElectionStatusChange(string $oldStatus, string $newStatus, ?string $electionName = null): AdminLog
    {
        $description = "Updated election configuration: Status changed from {$oldStatus} to {$newStatus}";
        if ($electionName) {
            $description .= " for election: {$electionName}";
        }

        return self::logAdminAction(
            actionType: 'election_status_change',
            description: $description,
            metadata: [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'election_name' => $electionName,
            ]
        );
    }

    /**
     * Log system events
     */
    public static function logSystemEvent(string $event, string $description, ?array $metadata = null): AdminLog
    {
        return self::logAdminAction(
            actionType: 'system_event',
            description: $description,
            metadata: array_merge($metadata ?? [], ['event' => $event])
        );
    }

    /**
     * Get a human-readable identifier for a model
     */
    private static function getModelIdentifier(Model $model): string
    {
        // Try common identifier fields
        $identifierFields = ['name', 'title', 'description', 'firstname', 'student_number', 'email'];

        foreach ($identifierFields as $field) {
            if (isset($model->$field)) {
                return $model->$field;
            }
        }

        // Fallback to ID
        return "ID: {$model->id}";
    }

    /**
     * Get admin logs with optional filtering
     */
    public static function getAdminLogs(array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        $query = AdminLog::query();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action_type'])) {
            $query->where('action_type', $filters['action_type']);
        }

        if (isset($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['election_id'])) {
            $query->where('election_id', $filters['election_id']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get voter logs with optional filtering
     */
    public static function getVoterLogs(array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        $query = VoterLog::query();

        if (isset($filters['voter_id'])) {
            $query->where('voter_id', $filters['voter_id']);
        }

        if (isset($filters['action_type'])) {
            $query->where('action_type', $filters['action_type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['election_id'])) {
            $query->where('election_id', $filters['election_id']);
        }

        return $query->orderBy('created_at', 'desc');
    }
}
