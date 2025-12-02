<?php
// app/Http/Controllers/Auth/ForgotPasswordController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OTPMail;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Show forgot password form
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send OTP to email
     */
    public function sendOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // Find user
        $user = User::where('email', $request->email)->first();

        // Generate OTP
        $otp = PasswordReset::generateOTP();

        // Delete old password reset tokens for this email
        PasswordReset::where('email', $request->email)->delete();

        // Create new password reset record
        PasswordReset::create([
            'email' => $request->email,
            'token' => Str::random(60),
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10), // OTP valid for 10 minutes
            'created_at' => now()
        ]);

        // Send OTP email
        try {
            Mail::to($request->email)->send(new OTPMail($otp, $user->name));

            return redirect()->route('password.verify-otp')
                ->with('email', $request->email)
                ->with('success', 'OTP has been sent to your email!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send OTP. Please try again.');
        }
    }

    /**
     * Show OTP verification form
     */
    public function showVerifyOTPForm()
    {
        if (!session('email')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp');
    }

    /**
     * Verify OTP
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6'
        ]);

        $passwordReset = PasswordReset::where('email', $request->email)->first();

        if (!$passwordReset) {
            return back()->with('error', 'Invalid request. Please try again.');
        }

        if (!$passwordReset->isValidOTP($request->otp)) {
            if ($passwordReset->isExpired()) {
                return back()->with('error', 'OTP has expired. Please request a new one.');
            }
            return back()->with('error', 'Invalid OTP. Please try again.');
        }

        // Store persistently
        session([
            'email' => $request->email,
            'token' => $passwordReset->token,
        ]);

        return redirect()->route('password.reset')
            ->with('success', 'OTP verified! Please set your new password.');
    }

    /**
     * Show reset password form
     */
    public function showResetForm()
    {
        if (!session('email') || !session('token')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|string|min:8|confirmed'
        ]);

        // Verify token
        $passwordReset = PasswordReset::where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return back()->with('error', 'Invalid reset token.');
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete password reset record
        $passwordReset->delete();

        return redirect()->route('login')
            ->with('success', 'Password has been reset successfully! Please login.');
    }

    /**
     * Resend OTP
     */
    public function resendOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        return $this->sendOTP($request);
    }
}