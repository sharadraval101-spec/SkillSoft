<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Handle Registration
    public function register(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:1,3' // Only allow User or Provider to register
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        Auth::login($user);
        return $this->redirectUser($user);
    }

    // Handle Login
    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate(); // Security: Prevent session fixation
            return $this->redirectUser(Auth::user());
        }

        return back()->withErrors(['email' => 'Credentials do not match our records.']);
    }

    // Centralized Redirection Logic
   protected function redirectUser($user) {

    $role = (int)$user->role;

    // Use direct redirects instead of intended() during testing
    // to ensure you go to the right place.
    return match($role) {
        User::ROLE_ADMIN    => redirect('/admin/dashboard'),
        User::ROLE_PROVIDER => redirect('/provider/dashboard'),
        User::ROLE_USER     => redirect('/user/dashboard'),
    };
}

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
