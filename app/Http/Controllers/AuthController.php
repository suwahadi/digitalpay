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
     * Send an already signed-in user to their own area. Members go to /user;
     * admins go to the admin panel, since /user would bounce them back here.
     */
    protected function redirectAuthenticated()
    {
        if (Auth::user()->role === 'member') {
            return redirect('/user')->with('info', 'Anda sudah login.');
        }

        return redirect('/admin');
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectAuthenticated();
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
        if (Auth::check()) {
            return $this->redirectAuthenticated();
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
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'member',
                'status' => 'inactive',
            ]);

            $otpCode = $user->generateOtpCode();

            /**
             * The account already exists at this point, so a failed OTP email must
             * not surface as a failed registration; the user can resend from /verify.
             */
            try {
                $user->notify(new EmailVerificationOTP($otpCode, $user->name));
                $message = 'Registrasi berhasil! Silakan cek email Anda untuk kode verifikasi.';
            } catch (\Exception $e) {
                report($e);
                $message = 'Registrasi berhasil, tetapi email kode verifikasi gagal terkirim. Silakan klik kirim ulang kode.';
            }

            return redirect('/verify?email=' . urlencode($user->email))
                ->with('message', $message);

        } catch (\Exception $e) {
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

        $canResendOtp = !$user->otp_expires_at || $user->isOtpExpired();
        $remainingSeconds = 0;
        
        if ($user->otp_expires_at && !$user->isOtpExpired()) {
            $remainingSeconds = (int) max(0, now()->diffInSeconds($user->otp_expires_at));
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

        if ($user->otp_expires_at && !$user->isOtpExpired()) {
            $remainingSeconds = (int) max(0, now()->diffInSeconds($user->otp_expires_at));
            
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

            $otpCode = $user->generateOtpCode();

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
        if (Auth::check()) {
            return $this->redirectAuthenticated();
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

        $token = Str::random(64);
        
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($user->email));

        try {
            $user->notify(new ForgotPasswordNotification($token, $user->name, $resetUrl));

            return back()->with('message', 'Jika email tersebut terdaftar, link reset password telah dikirim ke email Anda.');
        } catch (\Exception $e) {
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
        if (Auth::check()) {
            return $this->redirectAuthenticated();
        }

        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return redirect('/forgot-password')->withErrors([
                'general' => 'Token atau email tidak valid.'
            ]);
        }

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$passwordReset) {
            return redirect('/forgot-password')->withErrors([
                'general' => 'Token reset password tidak valid atau sudah kedaluwarsa.'
            ]);
        }

        if (now()->subMinutes(60)->gt($passwordReset->created_at)) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            
            return redirect('/forgot-password')->withErrors([
                'general' => 'Token reset password sudah kedaluwarsa. Silakan request link baru.'
            ]);
        }

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

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return back()->withErrors([
                'general' => 'Token reset password tidak valid atau sudah kedaluwarsa.'
            ]);
        }

        if (now()->subMinutes(60)->gt($passwordReset->created_at)) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            
            return back()->withErrors([
                'general' => 'Token reset password sudah kedaluwarsa. Silakan request link baru.'
            ]);
        }

        if (!Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors([
                'general' => 'Token reset password tidak valid.'
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'general' => 'Email tidak ditemukan.'
            ]);
        }

        try {
            $user->password = Hash::make($request->password);
            $user->save();

            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return redirect('/login')->with('message', 'Password berhasil direset! Silakan login dengan password baru Anda.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'general' => 'Gagal mereset password. Silakan coba lagi.'
            ]);
        }
    }
}