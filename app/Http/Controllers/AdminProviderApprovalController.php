<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ReviewProviderRequestRequest;
use App\Http\Requests\Provider\UpdateProviderOperationalStatusRequest;
use App\Models\ProviderProfile;
use App\Models\ProviderRequest;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ProviderRequestService;
use App\Services\ProviderUnavailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminProviderApprovalController extends Controller
{
    public function __construct(
        private readonly ProviderRequestService $providerRequestService,
        private readonly ProviderUnavailabilityService $providerUnavailabilityService,
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function index(): View
    {
        $pendingProviders = ProviderProfile::query()
            ->with('user')
            ->where('status', ProviderProfile::STATUS_PENDING)
            ->latest()
            ->get();

        $providerRequests = ProviderRequest::query()
            ->with(['serviceCategory', 'reviewer'])
            ->where('status', ProviderRequest::STATUS_PENDING)
            ->latest()
            ->get();

        $activeProviders = User::query()
            ->with('providerProfile')
            ->where('role', User::ROLE_PROVIDER)
            ->whereHas('providerProfile', fn ($query) => $query->where('status', ProviderProfile::STATUS_ACTIVE))
            ->orderBy('name')
            ->get();

        return view('admin.provider-approvals', compact('pendingProviders', 'providerRequests', 'activeProviders'));
    }

    public function approve(ProviderProfile $providerProfile): RedirectResponse
    {
        $oldValues = [
            'status' => $providerProfile->status,
            'verified_at' => $providerProfile->verified_at?->toIso8601String(),
        ];

        $providerProfile->update([
            'status' => ProviderProfile::STATUS_ACTIVE,
            'verified_at' => now(),
        ]);

        if ($providerProfile->user && (int) $providerProfile->user->role !== User::ROLE_PROVIDER) {
            $providerProfile->user->update(['role' => User::ROLE_PROVIDER]);
        }

        $providerProfile->user?->syncRoleFromLegacyValue();

        /** @var User|null $admin */
        $admin = request()->user();
        $this->auditLogService->log(
            'provider_profile.approved',
            $providerProfile,
            $admin,
            $oldValues,
            [
                'status' => $providerProfile->status,
                'verified_at' => $providerProfile->verified_at?->toIso8601String(),
            ]
        );

        return back()->with('success', 'Provider approved successfully.');
    }

    public function approveRequest(ProviderRequest $providerRequest): RedirectResponse
    {
        /** @var User $admin */
        $admin = request()->user();

        $this->providerRequestService->approve($providerRequest, $admin);

        return back()->with('success', 'Provider request approved and account created successfully.');
    }

    public function rejectRequest(ReviewProviderRequestRequest $request, ProviderRequest $providerRequest): RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();

        $this->providerRequestService->reject(
            $providerRequest,
            $admin,
            $request->validated('reason')
        );

        return back()->with('success', 'Provider request rejected successfully.');
    }

    public function updateAvailability(UpdateProviderOperationalStatusRequest $request, User $provider): RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();

        abort_unless((int) $provider->role === User::ROLE_PROVIDER, 404);

        $this->providerUnavailabilityService->updateOperationalStatus(
            $provider,
            $request->validated(),
            $admin
        );

        return back()->with('success', 'Provider availability status updated successfully.');
    }
}
