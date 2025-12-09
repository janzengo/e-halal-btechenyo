<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthenticatedSessionController extends Controller
{
    /**
     * Show the admin login page.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        // If user is already authenticated, redirect to appropriate dashboard
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();

            if ($admin->isHead()) {
                return redirect()->route('head.dashboard');
            } elseif ($admin->isOfficer()) {
                return redirect()->route('officers.dashboard');
            }
        }

        return Inertia::render('auth/admin/login', [
            'canResetPassword' => Route::has('admin.password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming admin authentication request.
     */
    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $admin = $request->validateCredentials();

        // Check if OTP is enabled
        if (config('otp.login_enabled')) {
            // Store admin info in session for OTP verification
            $request->session()->put('admin_otp_verification', [
                'admin_id' => $admin->id,
                'username' => $admin->username,
                'email' => $admin->email,
                'remember' => $request->boolean('remember'),
            ]);

            // Generate and send OTP
            $otpService = app(OtpService::class);
            $otpService->sendOtpToAdmin($admin);

            return redirect()->route('admin.otp.verify')->with('status', 'OTP sent to your email address.');
        }

        // Traditional login without OTP
        Auth::guard('admin')->login($admin, $request->boolean('remember'));
        $request->session()->regenerate();

        // Debug: Log the login attempt
        \Log::info('Admin login successful', [
            'username' => $admin->username,
            'role' => $admin->role,
            'session_id' => $request->session()->getId(),
        ]);

        // Redirect based on admin role
        if ($admin->isHead()) {
            return redirect()->intended(route('head.dashboard'))->with('success', 'Login successful! Welcome back.');
        } elseif ($admin->isOfficer()) {
            return redirect()->intended(route('officers.dashboard'))->with('success', 'Login successful! Welcome back.');
        }

        // Fallback redirect to head dashboard
        return redirect()->intended(route('head.dashboard'))->with('success', 'Login successful! Welcome back.');
    }

    /**
     * Show the OTP verification page.
     */
    public function showOtpVerification(Request $request): Response|RedirectResponse
    {
        // Check if there's a pending OTP verification
        $otpData = $request->session()->get('admin_otp_verification');

        if (! $otpData) {
            return redirect()->route('admin.login')->with('error', 'No OTP verification pending.');
        }

        return Inertia::render('auth/admin/otp-verification', [
            'email' => $otpData['email'],
            'username' => $otpData['username'],
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle OTP verification.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $otpData = $request->session()->get('admin_otp_verification');

        if (! $otpData) {
            return redirect()->route('admin.login')->with('error', 'No OTP verification pending.');
        }

        $otpService = app(OtpService::class);

        // Verify OTP
        if ($otpService->verifyAdminOtp($otpData['email'], $request->input('otp'))) {
            // OTP is valid, complete login
            $admin = \App\Models\Admin::find($otpData['admin_id']);

            if ($admin) {
                Auth::guard('admin')->login($admin, $otpData['remember']);
                $request->session()->regenerate();

                // Clear OTP verification data
                $request->session()->forget('admin_otp_verification');

                // Debug: Log the login attempt
                \Log::info('Admin OTP login successful', [
                    'username' => $admin->username,
                    'role' => $admin->role,
                    'session_id' => $request->session()->getId(),
                ]);

                // Redirect based on admin role
                if ($admin->isHead()) {
                    return redirect()->intended(route('head.dashboard'))->with('success', 'Login successful! Welcome back.');
                } elseif ($admin->isOfficer()) {
                    return redirect()->intended(route('officers.dashboard'))->with('success', 'Login successful! Welcome back.');
                }

                return redirect()->intended(route('head.dashboard'))->with('success', 'Login successful! Welcome back.');
            }
        }

        return back()->withErrors(['otp' => 'Invalid OTP code. Please try again.']);
    }

    /**
     * Destroy an authenticated admin session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'You have been successfully logged out.');
    }
}
