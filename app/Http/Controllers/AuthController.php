<?php
namespace App\Http\Controllers;

use App\Mail\PasswordResetCodeMail;
use App\Models\ActivityLog;
use App\Models\PasswordResetCode;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Handle Registration
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => 'required|in:'.implode(',', [User::ROLE_CUSTOMER, User::ROLE_PROVIDER]),
            'business_name' => 'required_if:role,'.User::ROLE_PROVIDER.'|nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => (int) $data['role'],
        ]);
        $user->syncRoleFromLegacyValue();

        if ((int) $user->role === User::ROLE_PROVIDER) {
            ProviderProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $data['business_name'] ?? $user->name.' Services',
                    'status' => 'pending',
                ]
            );

            $this->recordActivity($request, 'auth.register.provider_pending', 'Provider registration submitted', $user, [
                'role' => (int) $user->role,
            ]);

            return redirect()->route('login')
                ->with('status', 'Provider registration submitted. Wait for admin approval.');
        }

        $this->recordActivity($request, 'auth.register.customer', 'Customer registration submitted', $user, [
            'role' => (int) $user->role,
        ]);

        return redirect()->route('login')
            ->with('status', 'Customer account created successfully.');
    }

    // Handle Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'nullable|boolean',
        ]);
        $this->ensureIsNotRateLimited($request);

        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$this->isApprovedProvider($user)) {
                $this->recordActivity($request, 'auth.login.blocked', 'Provider login blocked (pending approval)', $user);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'Provider account is pending admin approval.',
                ]);
            }

            RateLimiter::clear($this->throttleKey($request));
            $this->recordActivity($request, 'auth.login', 'User logged in', $user);

            return $this->redirectUser($user);
        }

        RateLimiter::hit($this->throttleKey($request), 60);

        throw ValidationException::withMessages([
            'email' => 'Credentials do not match our records.',
        ]);
    }

    public function sendForgotPasswordOtp(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::query()
            ->where('email', $data['email'])
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'No account found for this email address.',
            ]);
        }

        $latestCode = PasswordResetCode::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        if ($latestCode && $latestCode->created_at->gt(now()->subMinute())) {
            throw ValidationException::withMessages([
                'email' => 'Please wait a minute before requesting another OTP.',
            ]);
        }

        $otp = (string) random_int(1000, 9999);
        $expiresAt = now()->addMinutes(10);

        PasswordResetCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($otp),
            'expires_at' => $expiresAt,
        ]);

        Mail::to($user->email)->send(new PasswordResetCodeMail(
            $user,
            $otp,
            $expiresAt->format('M d, Y h:i A')
        ));

        $this->recordActivity($request, 'auth.forgot_password.otp_sent', 'Forgot password OTP sent', $user);

        return response()->json([
            'message' => '4-digit OTP sent to your email.',
        ]);
    }

    public function verifyForgotPasswordOtp(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:4',
        ]);

        $user = User::query()
            ->where('email', $data['email'])
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid OTP.',
            ]);
        }

        $resetCode = PasswordResetCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (!$resetCode || !Hash::check($data['otp'], $resetCode->code_hash)) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid OTP.',
            ]);
        }

        $this->recordActivity($request, 'auth.forgot_password.otp_verified', 'Forgot password OTP verified', $user);

        return response()->json([
            'verified' => true,
            'message' => 'OTP verified. Enter your new password.',
        ]);
    }

    public function resetForgotPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:4',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::query()
            ->where('email', $data['email'])
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'No account found for this email address.',
            ]);
        }

        $resetCode = PasswordResetCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (!$resetCode || !Hash::check($data['otp'], $resetCode->code_hash)) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid OTP.',
            ]);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        $resetCode->used_at = now();
        $resetCode->save();

        PasswordResetCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('id', '!=', $resetCode->id)
            ->update(['used_at' => now()]);

        $this->recordActivity($request, 'auth.forgot_password.password_reset', 'Forgot password reset completed', $user);

        return response()->json([
            'message' => 'Password reset successful. Please login with your new password.',
            'redirect' => route('login'),
        ]);
    }

    // Centralized Redirection Logic
    protected function redirectUser(User $user)
    {
        $role = (int) $user->role;

        return match($role) {
            User::ROLE_ADMIN => redirect('/admin/dashboard'),
            User::ROLE_PROVIDER => $this->isApprovedProvider($user)
                ? redirect('/provider/dashboard')
                : redirect()->route('login')->with('error', 'Provider account is pending admin approval.'),
            default => redirect()->route('site.home'),
        };
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user) {
            $this->recordActivity($request, 'auth.logout', 'User logged out', $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function recordActivity(
        Request $request,
        string $eventType,
        string $eventLabel,
        ?User $user = null,
        array $metadata = []
    ): void {
        ActivityLog::create([
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'event_label' => $eventLabel,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ? substr($request->userAgent(), 0, 1000) : null,
            'metadata' => $metadata ?: null,
        ]);
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => "Too many login attempts. Try again in {$seconds} seconds.",
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower(trim((string) $request->input('email', ''))).'|'.$request->ip();
    }

    private function isApprovedProvider(User $user): bool
    {
        if ((int) $user->role !== User::ROLE_PROVIDER) {
            return true;
        }

        if (!Schema::hasTable('provider_profiles')) {
            return false;
        }

        $profile = ProviderProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['business_name' => $user->name.' Services', 'status' => 'pending']
        );

        return $profile->status === 'active';
    }
}
