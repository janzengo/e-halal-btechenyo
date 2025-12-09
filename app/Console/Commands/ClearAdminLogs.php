<?php

namespace App\Console\Commands;

use App\Models\AdminLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearAdminLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clear-admin 
                            {--days= : Clear logs older than specified days (default: all)}
                            {--confirm : Skip confirmation prompt}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear admin logs from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $confirm = $this->option('confirm');
        $dryRun = $this->option('dry-run');

        // Build the query
        $query = AdminLog::query();
        
        if ($days) {
            $cutoffDate = now()->subDays((int) $days);
            $query->where('created_at', '<', $cutoffDate);
            $timeframe = "older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})";
        } else {
            $timeframe = "all admin logs";
        }

        // Get count of logs to be deleted
        $count = $query->count();

        if ($count === 0) {
            $this->info('No admin logs found to clear.');
            return 0;
        }

        // Show what will be deleted
        $this->info("Found {$count} admin log(s) {$timeframe}");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No logs will actually be deleted');
            
            // Show sample of logs that would be deleted
            $sampleLogs = $query->with('admin')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            if ($sampleLogs->isNotEmpty()) {
                $this->info('Sample logs that would be deleted:');
                $headers = ['ID', 'Date', 'Admin', 'Action', 'Description'];
                $rows = [];

                foreach ($sampleLogs as $log) {
                    $rows[] = [
                        $log->id,
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->admin ? "{$log->admin->firstname} {$log->admin->lastname}" : 'System',
                        $log->action_type,
                        \Str::limit($log->action_description, 50)
                    ];
                }

                $this->table($headers, $rows);
            }

            return 0;
        }

        // Confirmation prompt
        if (!$confirm) {
            if (!$this->confirm("Are you sure you want to delete {$count} admin log(s) {$timeframe}?")) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Perform the deletion
        $this->info('Clearing admin logs...');
        
        try {
            DB::beginTransaction();
            
            $deletedCount = $query->delete();
            
            DB::commit();
            
            $this->info("Successfully cleared {$deletedCount} admin log(s).");
            
            // Log this action
            \App\Services\LoggingService::logSystemEvent(
                'admin_logs_cleared',
                "Cleared {$deletedCount} admin logs via command line" . ($days ? " (older than {$days} days)" : " (all logs)"),
                [
                    'deleted_count' => $deletedCount,
                    'days_filter' => $days,
                    'command_user' => get_current_user(),
                ]
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to clear admin logs: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}