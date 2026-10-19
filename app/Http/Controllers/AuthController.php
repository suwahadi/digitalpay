<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Notifications\EmailVerificationOTP;
use App\Notifications\ForgotPasswordNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/user')->with('info', 'Anda sudah login.');
        }
        
        return inertia('Auth/Login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        
        // Check if user exists and is a member
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email tidak ditemukan.'],
            ]);
        }

        if ($user->role !== 'member') {
            throw ValidationException::withMessages([
                'email' => ['Akses login khusus untuk member.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda tidak aktif. Silakan hubungi Admin.'],
            ]);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password yang Anda masukkan salah.'],
            ]);
        }

        // Attempt to login
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended('/user');
        }

        throw ValidationException::withMessages([
            'email' => ['Login gagal. Silakan coba lagi.'],
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('message', 'Anda telah logout.');
    }

    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        // If user is already logged in, redirect to dashboard
        if (Auth::check()) {
            return redirect('/user')->with('info', 'Anda sudah login.');
        }
        
        return inertia('Auth/Register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            // Create user with inactive status
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'member',
                'status' => 'inactive', // User starts as inactive
            ]);

            // Generate OTP code
            $otpCode = $user->generateOtpCode();

            // Send OTP via email (queued notification)
            $user->notify(new EmailVerificationOTP($otpCode, $user->name));

            return redirect('/verify?email=' . urlencode($user->email))
                ->with('message', 'Registrasi berhasil! Silakan cek email Anda untuk kode verifikasi.');

        } catch (\Exception $e) {
            // Log::error('Registration failed', [
            //     'error' => $e->getMessage(),
            //     'email' => $validated['email']
            // ]);

            return back()->withErrors([
                'general' => 'Terjadi kesalahan saat registrasi. Silakan coba lagi.'
            ])->withInput();
        }
    }

    /**
     * Show email verification form
     */
    public function showVerifyForm(Request $request)
    {
        $email = $request->query('email');
        $status = $request->query('status');
        
        if (!$email) {
            return redirect('/register')->withErrors([
                'general' => 'Email diperlukan untuk verifikasi.'
            ]);
        }

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return redirect('/register')->withErrors([
                'general' => 'Email tidak ditemukan.'
            ]);
        }

        if ($user->isEmailVerified()) {
            return redirect('/login')->with('message', 'Verifikasi email berhasil! Silakan login untuk melanjutkan.');
        }

        // Calculate resend ability
        $canResendOtp = !$user->otp_expires_at || $user->isOtpExpired();
        $remainingSeconds = 0;
        
        if ($user->otp_expires_at && !$user->isOtpExpired()) {
            $remainingSeconds = max(0, $user->otp_expires_at->diffInSeconds(now()));
        }

        return inertia('Auth/Verify', [
            'email' => $email,
            'status' => $status,
            'canResendOtp' => $canResendOtp,
            'remainingSeconds' => $remainingSeconds
        ]);
    }

    /**
     * Handle OTP verification
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'general' => 'Email tidak ditemukan.'
            ]);
        }

        if ($user->isEmailVerified()) {
            return redirect('/login')->with('message', 'Verifikasi email berhasil! Silakan login untuk melanjutkan.');
        }

        if (!$user->isOtpValid($request->otp_code)) {
            return back()->withErrors([
                'otp_code' => 'Kode OTP salah!'
            ]);
        }

        // Mark email as verified and activate user
        $user->markEmailAsVerified();

        return redirect('/verify?status=success&email=' . urlencode($user->email));
    }

    /**
     * Resend OTP code
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'general' => 'Email tidak ditemukan.'
            ]);
        }

        if ($user->isEmailVerified()) {
            return back()->withErrors([
                'general' => 'Email sudah terverifikasi.'
            ]);
        }

        // Strict validation: Check if there's an active OTP (within 10 minutes)
        if ($user->otp_expires_at && !$user->isOtpExpired()) {
            $remainingSeconds = max(0, $user->otp_expires_at->diffInSeconds(now()));
            
            if ($remainingSeconds <= 0) {
                // Edge case: OTP just expired, allow resend
                // This shouldn't happen due to isOtpExpired() check, but safety first
            } else {
                $minutes = floor($remainingSeconds / 60);
                $seconds = $remainingSeconds % 60;
                
                if ($minutes > 0) {
                    $timeText = "{$minutes} menit {$seconds} detik";
                } else {
                    $timeText = "{$seconds} detik";
                }
                
                return back()->withErrors([
                    'general' => "OTP masih aktif. Silakan tunggu {$timeText} lagi."
                ]);
            }
        }

        $lastOtpTime = $user->updated_at;
        if ($lastOtpTime && $lastOtpTime->diffInMinutes(now()) < 1) {
            return back()->withErrors([
                'general' => 'Harap tunggu sebentar sebelum meminta kode OTP baru.'
            ]);
        }

        try {
            // Destroy old OTP first (security measure)
            $user->forceFill([
                'otp_code' => null,
                'otp_expires_at' => null,
            ])->save();

            // Generate new OTP code
            $otpCode = $user->generateOtpCode();

            // Send OTP via email (queued notification)
            $user->notify(new EmailVerificationOTP($otpCode, $user->name));

            return back()->with([
                'message' => 'Kode OTP baru telah dikirim ke email Anda.',
                'otp_sent' => true
            ]);

        } catch (\Exception $e) {
            return back()->withErrors([
                'general' => 'Gagal mengirim ulang kode OTP. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        // If user is already logged in, redirect to dashboard
        if (Auth::check()) {
            return redirect('/user')->with('info', 'Anda sudah login.');
        }
        
        return inertia('Auth/ForgotPassword');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Return success even if user doesn't exist (security best practice)
            return back()->with('message', 'Email belum terdaftar member.');
        }

        if ($user->role !== 'member') {
            return back()->with('message', 'Link reset password telah dikirim ke email Anda.');
        }

        // Generate password reset token
        $token = Str::random(64);
        
        // Store token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Build reset URL
        $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($user->email));

        try {
            // Send password reset notification via Brevo
            $user->notify(new ForgotPasswordNotification($token, $user->name, $resetUrl));

            return back()->with('message', 'Jika email tersebut terdaftar, link reset password telah dikirim ke email Anda.');
        } catch (\Exception $e) {
            // Log::error('Failed to send password reset email', [
            //     'error' => $e->getMessage(),
            //     'email' => $request->email
            // ]);

            return back()->withErrors([
                'general' => 'Gagal mengirim email reset password. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Show reset password form
     */
    public function showResetPasswordForm(Request $request)
    {
        // If user is already logged in, redirect to dashboard
        if (Auth::check()) {
            return redirect('/user')->with('info', 'Anda sudah login.');
        }

        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return redirect('/forgot-password')->withErrors([
                'general' => 'Token atau email tidak valid.'
            ]);
        }

        // Verify token
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$passwordReset) {
            return redirect('/forgot-password')->withErrors([
                'general' => 'Token reset password tidak valid atau sudah kedaluwarsa.'
            ]);
        }

        // Check if token is valid (created within last 60 minutes)
        $tokenAge = now()->diffInMinutes($passwordReset->created_at);
        if ($tokenAge > 60) {
            // Delete expired token
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            
            return redirect('/forgot-password')->withErrors([
                'general' => 'Token reset password sudah kedaluwarsa. Silakan request link baru.'
            ]);
        }

        // Verify token hash
        if (!Hash::check($token, $passwordReset->token)) {
            return redirect('/forgot-password')->withErrors([
                'general' => 'Token reset password tidak valid.'
            ]);
        }

        return inertia('Auth/ResetPassword', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Handle reset password request
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Verify token
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return back()->withErrors([
                'general' => 'Token reset password tidak valid atau sudah kedaluwarsa.'
            ]);
        }

        // Check if token is valid (created within last 60 minutes)
        $tokenAge = now()->diffInMinutes($passwordReset->created_at);
        if ($tokenAge > 60) {
            // Delete expired token
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            
            return back()->withErrors([
                'general' => 'Token reset password sudah kedaluwarsa. Silakan request link baru.'
            ]);
        }

        // Verify token hash
        if (!Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors([
                'general' => 'Token reset password tidak valid.'
            ]);
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'general' => 'Email tidak ditemukan.'
            ]);
        }

        try {
            // Update password
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete used token
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return redirect('/login')->with('message', 'Password berhasil direset! Silakan login dengan password baru Anda.');
        } catch (\Exception $e) {
            // Log::error('Failed to reset password', [
            //     'error' => $e->getMessage(),
            //     'email' => $request->email
            // ]);

            return back()->withErrors([
                'general' => 'Gagal mereset password. Silakan coba lagi.'
            ]);
        }
    }
}