<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Handle Registration
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:1,3',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        Auth::login($user);
        $this->recordActivity($request, 'auth.register', 'New account registered', $user, [
            'role' => (int) $user->role,
        ]);

        return $this->redirectUser($user);
    }

    // Handle Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::user();
            $this->recordActivity($request, 'auth.login', 'User logged in', $user);

            return $this->redirectUser($user);
        }

        return back()->withErrors(['email' => 'Credentials do not match our records.']);
    }

    // Centralized Redirection Logic
    protected function redirectUser(User $user)
    {
        $role = (int) $user->role;

        return match($role) {
            User::ROLE_ADMIN => redirect('/admin/dashboard'),
            User::ROLE_PROVIDER => redirect('/provider/dashboard'),
            User::ROLE_USER => redirect('/user/dashboard'),
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
}
