<?php
namespace App\Http\Controllers;

use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordResetCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        if ((int) $user->role === User::ROLE_CUSTOMER) {
            return redirect()->to(route('customer.dashboard').'#profile-center');
        }

        return view('profile.index', ['user' => $user]);
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $requiresProfilePhoto = (int) $user->role !== User::ROLE_CUSTOMER && !$user->profile_photo_path;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'profile_photo' => [
                Rule::requiredIf($requiresProfilePhoto),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'profile_photo.required' => 'Please upload a profile photo before saving your profile.',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $user->profile_photo_path = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user->save();

        return $this->profileResponse($user, 'success', 'Profile updated successfully.');
    }

    public function sendPasswordResetCode(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $latestCode = PasswordResetCode::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        if ($latestCode && $latestCode->created_at->gt(now()->subMinute())) {
            throw ValidationException::withMessages([
                'send_code' => 'Please wait a minute before requesting another code.',
            ]);
        }

        $code = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        PasswordResetCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => $expiresAt,
        ]);

        Mail::to($user->email)->send(new PasswordResetCodeMail(
            $user,
            $code,
            $expiresAt->format('M d, Y h:i A')
        ));

        return $this->profileResponse($user, 'code_sent', 'Verification code sent to your registered email.');
    }

    public function resetPasswordWithCode(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'code' => 'required|digits:6',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $resetCode = PasswordResetCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (!$resetCode || !Hash::check($request->code, $resetCode->code_hash)) {
            throw ValidationException::withMessages([
                'code' => 'Invalid or expired verification code.',
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $resetCode->used_at = now();
        $resetCode->save();

        PasswordResetCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('id', '!=', $resetCode->id)
            ->update(['used_at' => now()]);

        return $this->profileResponse($user, 'password_reset_success', 'Password reset completed successfully.');
    }

    public function showPhoto(User $user): StreamedResponse
    {
        abort_unless($user->profile_photo_path, 404);
        abort_unless(Storage::disk('public')->exists($user->profile_photo_path), 404);

        return Storage::disk('public')->response($user->profile_photo_path);
    }

    private function profileResponse(User $user, string $key, string $message): RedirectResponse
    {
        if ((int) $user->role === User::ROLE_CUSTOMER) {
            return redirect()->to(route('customer.dashboard').'#profile-center')->with($key, $message);
        }

        return back()->with($key, $message);
    }
}
