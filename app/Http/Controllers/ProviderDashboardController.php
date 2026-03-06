<?php

namespace App\Http\Controllers;

use App\Services\ProviderDashboardMetricsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderDashboardController extends Controller
{
    public function __construct(
        private readonly ProviderDashboardMetricsService $metricsService
    ) {
    }

    public function index(Request $request): View
    {
        /** @var \App\Models\User $provider */
        $provider = $request->user();
        $data = $this->metricsService->getData($provider);

        return view('provider.index', [
            'metrics' => $data['metrics'],
            'charts' => $data['charts'],
            'recentBookings' => $data['recentBookings'],
            'provider' => $provider,
        ]);
    }
}
