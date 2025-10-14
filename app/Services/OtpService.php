<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendOtpEmailJob;
use App\Jobs\SendPasswordResetEmailJob;
use App\Jobs\SendVoteReceiptEmailJob;
use App\Models\Admin;
use App\Models\AdminOtpRequest;
use App\Models\OtpRequest;
use App\Models\Voter;

class OtpService
{
    /**
     * Check if OTP login is enabled.
     */
    public function isOtpLoginEnabled(): bool
    {
        return config('otp.login_enabled', false);
    }

    /**
     * Generate a random OTP code.
     */
    public function generateOtpCode(): string
    {
        // In development, if test OTP is enabled, return test code
        if (config('otp.development.always_use_test_otp', false)) {
            return config('otp.development.test_otp_code', '123456');
        }

        $length = config('otp.length', 6);

        return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP to admin user.
     */
    public function sendOtpToAdmin(Admin $admin, array $requestInfo = []): bool
    {
        if (! $this->isOtpLoginEnabled()) {
            return false;
        }

        // Check rate limiting
        if (! $this->checkAdminRateLimit($admin->email)) {
            throw new \Exception('Rate limit exceeded. Please try again later.');
        }

        $otpCode = $this->generateOtpCode();
        $expiryMinutes = config('otp.expiry_minutes', 5);

        // Store OTP request in database
        AdminOtpRequest::create([
            'email' => $admin->email,
            'otp' => $otpCode,
            'expires_at' => now()->addMinutes($expiryMinutes),
            'attempts' => 0,
        ]);

        // Dispatch OTP email job
        SendOtpEmailJob::dispatch($admin->email, $otpCode, $requestInfo, $admin->firstname);

        return true;
    }

    /**
     * Send OTP to voter.
     */
    public function sendOtpToVoter(Voter $voter, array $requestInfo = []): bool
    {
        if (! $this->isOtpLoginEnabled()) {
            return false;
        }

        // Check rate limiting
        if (! $this->checkVoterRateLimit($voter->student_number)) {
            throw new \Exception('Rate limit exceeded. Please try again later.');
        }

        $otpCode = $this->generateOtpCode();
        $expiryMinutes = config('otp.expiry_minutes', 5);

        // Store OTP request in database
        OtpRequest::create([
            'student_number' => $voter->student_number,
            'otp_code' => $otpCode,
            'expires_at' => now()->addMinutes($expiryMinutes),
            'used' => false,
            'ip_address' => $requestInfo['ip_address'] ?? request()->ip(),
            'user_agent' => $requestInfo['user_agent'] ?? request()->userAgent(),
        ]);

        // Dispatch OTP email job (assuming voters have email in the future)
        SendOtpEmailJob::dispatch($voter->student_number.'@student.ehalal.test', $otpCode, $requestInfo);

        return true;
    }

    /**
     * Verify OTP for admin.
     */
    public function verifyAdminOtp(string $email, string $otpCode): bool
    {
        $otpRequest = AdminOtpRequest::where('email', $email)
            ->where('otp', $otpCode)
            ->where('expires_at', '>', now())
            ->first();

        if (! $otpRequest) {
            // Log failed OTP attempt
            LoggingService::logAdminAction(
                actionType: 'otp_verification_failed',
                description: "Failed OTP verification attempt for email: {$email}",
                metadata: ['email' => $email, 'otp_code' => $otpCode]
            );

            return false;
        }

        // Log successful OTP verification
        LoggingService::logAdminAction(
            actionType: 'otp_verification_success',
            description: "OTP verification successful for email: {$email}",
            metadata: ['email' => $email]
        );

        // Delete the OTP request after successful verification
        $otpRequest->delete();

        return true;
    }

    /**
     * Verify OTP for voter.
     */
    public function verifyVoterOtp(string $studentNumber, string $otpCode): bool
    {
        $otpRequest = OtpRequest::where('student_number', $studentNumber)
            ->where('otp_code', $otpCode)
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->first();

        if (! $otpRequest) {
            return false;
        }

        // Mark as used
        $otpRequest->update(['used' => true]);

        return true;
    }

    /**
     * Check rate limiting for admin.
     */
    protected function checkAdminRateLimit(string $email): bool
    {
        if (config('otp.development.skip_rate_limiting', false)) {
            return true;
        }

        $rateLimit = config('otp.rate_limit_per_hour', 5);
        $attempts = AdminOtpRequest::where('email', $email)
            ->where('created_at', '>', now()->subHour())
            ->count();

        return $attempts < $rateLimit;
    }

    /**
     * Check rate limiting for voter.
     */
    protected function checkVoterRateLimit(string $studentNumber): bool
    {
        if (config('otp.development.skip_rate_limiting', false)) {
            return true;
        }

        $rateLimit = config('otp.rate_limit_per_hour', 5);
        $attempts = OtpRequest::where('student_number', $studentNumber)
            ->where('created_at', '>', now()->subHour())
            ->count();

        return $attempts < $rateLimit;
    }

    /**
     * Send vote receipt email.
     */
    public function sendVoteReceiptEmail(string $email, array $receiptData, array $requestInfo = []): bool
    {
        try {
            SendVoteReceiptEmailJob::dispatch($email, $receiptData, $requestInfo);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to dispatch vote receipt email job: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Send password reset email.
     */
    public function sendPasswordResetEmail(string $email, array $resetData, array $requestInfo = []): bool
    {
        try {
            SendPasswordResetEmailJob::dispatch($email, $resetData, $requestInfo);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to dispatch password reset email job: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get browser information from user agent.
     */
    protected function getBrowserInfo(string $userAgent): string
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
    protected function getPlatformInfo(string $userAgent): string
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

    /**
     * Clean up expired OTP requests.
     */
    public function cleanupExpiredOtps(): int
    {
        $adminDeleted = AdminOtpRequest::where('expires_at', '<', now())->delete();
        $voterDeleted = OtpRequest::where('expires_at', '<', now())->delete();

        return $adminDeleted + $voterDeleted;
    }
}
