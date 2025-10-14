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

class SendOtpEmailJob implements ShouldQueue
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
        private readonly string $otpCode,
        private readonly array $requestInfo = [],
        private readonly ?string $firstName = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $template = config('otp.email.template', 'email-templates.otp-verification');
            $subject = config('otp.email.subject', 'Your OTP Verification Code');

            $data = [
                'otpCode' => $this->otpCode,
                'firstName' => $this->firstName,
                'browser' => $this->getBrowserInfo($this->requestInfo['user_agent'] ?? request()->userAgent()),
                'platform' => $this->getPlatformInfo($this->requestInfo['user_agent'] ?? request()->userAgent()),
                'requestDate' => now()->format('M d, Y \a\t H:i'),
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

            Log::info('OTP email sent successfully', [
                'email' => $this->email,
                'otp_code' => $this->otpCode,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email', [
                'email' => $this->email,
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
        Log::error('OTP email job failed permanently', [
            'email' => $this->email,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }

    /**
     * Get browser information from user agent.
     */
    private function getBrowserInfo(string $userAgent): string
    {
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        }

        return 'Unknown Browser';
    }

    /**
     * Get platform information from user agent.
     */
    private function getPlatformInfo(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows';
        } elseif (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'macOS')) {
            return 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            return 'Android';
        } elseif (str_contains($userAgent, 'iOS') || str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            return 'iOS';
        }

        return 'Unknown Platform';
    }
}
