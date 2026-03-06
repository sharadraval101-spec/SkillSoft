<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminProviderApprovalController extends Controller
{
    public function index(): View
    {
        $pendingProviders = ProviderProfile::query()
            ->with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('admin.provider-approvals', compact('pendingProviders'));
    }

    public function approve(ProviderProfile $providerProfile): RedirectResponse
    {
        $providerProfile->update([
            'status' => 'active',
            'verified_at' => now(),
        ]);

        if ($providerProfile->user && (int) $providerProfile->user->role !== User::ROLE_PROVIDER) {
            $providerProfile->user->update(['role' => User::ROLE_PROVIDER]);
        }

        $providerProfile->user?->syncRoleFromLegacyValue();

        return back()->with('success', 'Provider approved successfully.');
    }
}
