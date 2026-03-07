<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserManagementController extends Controller
{
    public function index(): View
    {
        return view('admin.users', [
            'usersDataUrl' => route('admin.users.data'),
        ]);
    }

    public function data(): JsonResponse
    {
        $users = User::query()
            ->latest('id')
            ->get();

        return response()->json([
            'data' => $users->map(fn (User $user): array => $this->toDataRow($user)),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', Rule::in([
                User::ROLE_CUSTOMER,
                User::ROLE_ADMIN,
                User::ROLE_PROVIDER,
            ])],
            'is_active' => 'nullable|boolean',
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => (int) $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncProviderProfile($user);
        $user->syncRoleFromLegacyValue();

        return $this->successResponse($request, 'User created successfully.', 201);
    }

    public function update(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', Rule::in([
                User::ROLE_CUSTOMER,
                User::ROLE_ADMIN,
                User::ROLE_PROVIDER,
            ])],
            'is_active' => 'nullable|boolean',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = (int) $validated['role'];
        $user->is_active = $request->boolean('is_active');

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();
        $this->syncProviderProfile($user);
        $user->syncRoleFromLegacyValue();

        return $this->successResponse($request, 'User updated successfully.');
    }

    public function toggleActive(Request $request, User $user): JsonResponse|RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();

        if ($admin->is($user)) {
            return $this->errorResponse($request, 'You cannot change your own active status.', 422);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $message = $user->is_active
            ? 'User marked as active.'
            : 'User marked as inactive.';

        return $this->successResponse($request, $message);
    }

    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();

        if ($admin->is($user)) {
            return $this->errorResponse($request, 'You cannot delete your own account.', 422);
        }

        try {
            $user->delete();
        } catch (QueryException) {
            return $this->errorResponse(
                $request,
                'This user is linked to existing records and cannot be deleted.',
                422
            );
        }

        return $this->successResponse($request, 'User deleted successfully.');
    }

    private function syncProviderProfile(User $user): void
    {
        if ((int) $user->role !== User::ROLE_PROVIDER) {
            return;
        }

        $providerProfile = ProviderProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $user->name.' Services',
                'status' => 'active',
                'verified_at' => now(),
            ]
        );

        if ($providerProfile->status !== 'active') {
            $providerProfile->status = 'active';
            $providerProfile->verified_at = now();
            $providerProfile->save();
        }
    }

    private function toDataRow(User $user): array
    {
        $role = (int) $user->role;
        $isActive = (bool) $user->is_active;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role,
            'role_label' => match ($role) {
                User::ROLE_ADMIN => 'Admin',
                User::ROLE_PROVIDER => 'Provider',
                default => 'Customer',
            },
            'is_active' => $isActive,
            'status_label' => $isActive ? 'Active' : 'Inactive',
            'joined_at' => $user->created_at?->format('d M Y, h:i A') ?? '-',
            'joined_at_timestamp' => $user->created_at?->timestamp ?? 0,
            'update_url' => route('admin.users.update', $user),
            'toggle_active_url' => route('admin.users.toggle-active', $user),
            'delete_url' => route('admin.users.destroy', $user),
        ];
    }

    private function successResponse(
        Request $request,
        string $message,
        int $status = 200
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with('success', $message);
    }

    private function errorResponse(
        Request $request,
        string $message,
        int $status = 422
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with('error', $message);
    }
}
