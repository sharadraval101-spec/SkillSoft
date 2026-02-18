<?php

namespace App\Http\Controllers;

use App\Services\AdminDashboardMetricsService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardMetricsService $metricsService
    ) {
    }

    public function index(): View
    {
        $data = $this->metricsService->getData();

        return view('admin.index', [
            'metrics' => $data['metrics'],
            'charts' => $data['charts'],
            'recentActivities' => $data['recentActivities'],
        ]);
    }
}
