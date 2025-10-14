<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as FoundationQueueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetEmailJob implements ShouldQueue
{
    use FoundationQueueable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $email,
        private readonly array $resetData,
        private readonly array $requestInfo = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $template = 'email-templates.password-reset';
            $subject = 'Password Reset Request - E-Halal BTECHenyo';

            // Map reset data to template variable names
            $data = [
                'resetUrl' => $this->resetData['reset_url'] ?? '#',
                'expiryTime' => $this->resetData['expiry_time'] ?? '60',
                'username' => $this->resetData['username'] ?? 'User',
                'email' => $this->resetData['email'] ?? $this->email,
                'role' => $this->resetData['role'] ?? 'User',
                'requestTime' => $this->resetData['request_time'] ?? now()->format('M d, Y \a\t H:i:s'),
                'ipAddress' => $this->requestInfo['ip_address'] ?? request()->ip(),
            ];

            Mail::send($template, $data, function ($message) use ($subject) {
                $message->to($this->email)
                    ->subject($subject)
                    ->from(
                        config('mail.from.address', 'admin@ehalal.tech'),
                        config('mail.from.name', 'E-Halal BTECHenyo Admin')
                    );
            });

            Log::info('Password reset email sent successfully', [
                'email' => $this->email,
                'username' => $this->resetData['username'] ?? 'unknown',
                'role' => $this->resetData['role'] ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'email' => $this->email,
                'username' => $this->resetData['username'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Password reset email job failed permanently', [
            'email' => $this->email,
            'username' => $this->resetData['username'] ?? 'unknown',
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}
