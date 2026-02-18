<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminDashboardMetricsService
{
    public function getData(): array
    {
        $timezone = config('app.timezone');
        $now = Carbon::now($timezone);

        $metrics = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', User::ROLE_ADMIN)->count(),
            'total_providers' => User::where('role', User::ROLE_PROVIDER)->count(),
            'total_students' => User::where('role', User::ROLE_USER)->count(),
            'registrations_7d' => User::whereBetween('created_at', [
                $now->copy()->subDays(6)->startOfDay(),
                $now->copy()->endOfDay(),
            ])->count(),
            'logins_24h' => ActivityLog::where('event_type', 'auth.login')
                ->where('created_at', '>=', $now->copy()->subDay())
                ->count(),
        ];

        $registrationSeries = $this->dailySeries(
            User::query(),
            'created_at',
            $now,
            14
        );

        $loginSeries = $this->dailySeries(
            ActivityLog::query()->where('event_type', 'auth.login'),
            'created_at',
            $now,
            14
        );

        $charts = [
            'registrations_daily_14d' => $registrationSeries,
            'logins_daily_14d' => $loginSeries,
            'role_distribution' => [
                'labels' => ['Admins', 'Providers', 'Students'],
                'series' => [
                    $metrics['total_admins'],
                    $metrics['total_providers'],
                    $metrics['total_students'],
                ],
            ],
        ];

        $recentActivities = ActivityLog::query()
            ->with('user:id,name,email')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return [
            'metrics' => $metrics,
            'charts' => $charts,
            'recentActivities' => $recentActivities,
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $query
     */
    private function dailySeries($query, string $column, Carbon $now, int $days): array
    {
        $start = $now->copy()->subDays($days - 1)->startOfDay();
        $end = $now->copy()->endOfDay();

        /** @var Collection<string,int> $raw */
        $raw = $query->whereBetween($column, [$start, $end])
            ->selectRaw("DATE($column) as day, COUNT(*) as total")
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $series = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('M d');
            $series[] = (int) ($raw[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }
}
