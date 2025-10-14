<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OtpTestController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService
    ) {}

    /**
     * Show OTP status and configuration.
     */
    public function index(): View
    {
        $otpEnabled = $this->otpService->isOtpLoginEnabled();
        $config = config('otp');

        return view('admin.otp-test', compact('otpEnabled', 'config'));
    }

    /**
     * Send test OTP to current admin.
     */
    public function sendTestOtp(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not authenticated.',
            ], 401);
        }

        try {
            $requestInfo = [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            $sent = $this->otpService->sendOtpToAdmin($admin, $requestInfo);

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP email job dispatched successfully to '.$admin->email,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP login is disabled or failed to dispatch job.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Verify test OTP.
     */
    public function verifyTestOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not authenticated.',
            ], 401);
        }

        try {
            $verified = $this->otpService->verifyAdminOtp($admin->email, $request->otp_code);

            if ($verified) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP code.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Test email template rendering.
     */
    public function testEmailTemplate(Request $request)
    {
        $template = $request->get('template', 'otp-verification');
        $email = $request->get('email', 'test@example.com');

        try {
            $data = [
                'otpCode' => '123456',
                'browser' => 'Chrome',
                'platform' => 'Windows',
                'requestDate' => now()->format('M d, Y \a\t H:i'),
                'ipAddress' => $request->ip(),
            ];

            // Add template-specific data
            if ($template === 'vote-receipt') {
                $data = array_merge($data, [
                    'electionName' => 'BTECHenyo Student Council Election 2025',
                    'voteRef' => 'VOTE-'.now()->format('ymd').'-1234',
                    'voterName' => 'Juan Dela Cruz',
                    'studentNumber' => '2024-00001',
                    'courseName' => 'Bachelor of Science in Information Technology',
                    'votedAt' => now()->format('M d, Y \a\t H:i:s'),
                    'votes' => [
                        [
                            'position' => 'President',
                            'candidate' => 'John Doe',
                            'party' => 'Progressive Party',
                        ],
                        [
                            'position' => 'Vice President',
                            'candidate' => 'Alice Brown',
                            'party' => 'Unity Party',
                        ],
                    ],
                ]);
            } elseif ($template === 'password-reset') {
                $data = array_merge($data, [
                    'resetUrl' => route('admin.password.reset', ['token' => 'sample-token']),
                    'expiryTime' => '60',
                    'username' => 'head',
                    'email' => 'ehalal.btecheenyo@gmail.com',
                    'role' => 'head',
                    'requestTime' => now()->format('M d, Y \a\t H:i:s'),
                ]);
            }

            return view("email-templates.{$template}", $data);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to render template: '.$e->getMessage(),
            ], 400);
        }
    }

    /**
     * Send test vote receipt email.
     */
    public function sendTestVoteReceipt(Request $request)
    {
        $email = $request->get('email', 'test@example.com');

        try {
            $receiptData = [
                'election_name' => 'BTECHenyo Student Council Election 2025',
                'vote_ref' => 'VOTE-'.now()->format('ymd').'-1234',
                'voter_name' => 'Juan Dela Cruz',
                'student_number' => '2024-00001',
                'course_name' => 'Bachelor of Science in Information Technology',
                'voted_at' => now()->format('M d, Y \a\t H:i:s'),
                'votes' => [
                    [
                        'position' => 'President',
                        'candidate' => 'John Doe',
                        'party' => 'Progressive Party',
                    ],
                    [
                        'position' => 'Vice President',
                        'candidate' => 'Alice Brown',
                        'party' => 'Unity Party',
                    ],
                ],
            ];

            $requestInfo = [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            $sent = $this->otpService->sendVoteReceiptEmail($email, $receiptData, $requestInfo);

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vote receipt email job dispatched successfully to '.$email,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to dispatch vote receipt email job.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Send test password reset email.
     */
    public function sendTestPasswordReset(Request $request)
    {
        $email = $request->get('email', 'test@example.com');

        try {
            $resetData = [
                'reset_url' => route('admin.password.reset', ['token' => 'sample-token']),
                'expiry_time' => '60',
                'username' => 'head',
                'email' => $email,
                'role' => 'head',
                'request_time' => now()->format('M d, Y \a\t H:i:s'),
            ];

            $requestInfo = [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            $sent = $this->otpService->sendPasswordResetEmail($email, $resetData, $requestInfo);

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password reset email job dispatched successfully to '.$email,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to dispatch password reset email job.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Toggle OTP login (for development only).
     */
    public function toggleOtpLogin(Request $request)
    {
        if (! app()->environment('local')) {
            return response()->json([
                'success' => false,
                'message' => 'This feature is only available in development environment.',
            ], 403);
        }

        $enabled = $request->boolean('enabled');

        // Update the .env file (this is a simple approach for development)
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        if (str_contains($envContent, 'OTP_LOGIN=')) {
            $envContent = preg_replace('/OTP_LOGIN=.*/', "OTP_LOGIN={$enabled}", $envContent);
        } else {
            $envContent .= "\nOTP_LOGIN={$enabled}\n";
        }

        file_put_contents($envFile, $envContent);

        // Clear config cache
        \Artisan::call('config:clear');

        return response()->json([
            'success' => true,
            'message' => 'OTP login has been '.($enabled ? 'enabled' : 'disabled'),
            'enabled' => $enabled,
        ]);
    }
}
