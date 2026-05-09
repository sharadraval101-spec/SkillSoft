<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProviderRequestRequest;
use App\Models\ServiceCategory;
use App\Services\ProviderRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProviderRequestController extends Controller
{
    public function __construct(private readonly ProviderRequestService $providerRequestService)
    {
    }

    public function create(): View
    {
        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('site.provider-requests.create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreProviderRequestRequest $request): RedirectResponse
    {
        $this->providerRequestService->submit($request->validated());

        return redirect()
            ->route('provider.requests.create')
            ->with('success', 'Your provider request has been submitted successfully. We will review it and contact you by email.');
    }
}
