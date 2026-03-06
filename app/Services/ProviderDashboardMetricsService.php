<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ProviderPayout;
use App\Models\Review;
use App\Models\Service;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProviderDashboardMetricsService
{
    public function getData(User $provider): array
    {
        $timezone = config('app.timezone');
        $now = Carbon::now($timezone);
        $providerProfileId = $provider->providerProfile?->id;

        $servicesBaseQuery = Service::query()
            ->when(
                $providerProfileId,
                fn (Builder $query) => $query->where('provider_profile_id', $providerProfileId),
                fn (Builder $query) => $query->whereRaw('1 = 0')
            );

        $bookingsBaseQuery = Booking::query()->where('provider_id', $provider->id);
        $payoutsBaseQuery = ProviderPayout::query()->where('provider_id', $provider->id);
        $reviewsBaseQuery = Review::query()
            ->where('provider_id', $provider->id)
            ->where('is_approved', true);
        $slotsBaseQuery = Slot::query()->where('provider_id', $provider->id);

        $monthlyEarnings = (float) (clone $payoutsBaseQuery)
            ->where('status', ProviderPayout::STATUS_PAID)
            ->whereBetween('paid_at', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])
            ->sum('net_amount');

        $pendingPayoutAmount = (float) (clone $payoutsBaseQuery)
            ->whereIn('status', [
                ProviderPayout::STATUS_PENDING,
                ProviderPayout::STATUS_PROCESSING,
            ])
            ->sum('net_amount');

        $averageRating = (float) (clone $reviewsBaseQuery)->avg('rating');

        $metrics = [
            'total_services' => (clone $servicesBaseQuery)->count(),
            'active_services' => (clone $servicesBaseQuery)
                ->where(function (Builder $query): void {
                    $query->where('is_active', true)
                        ->orWhere('status', 'active');
                })
                ->count(),
            'total_bookings' => (clone $bookingsBaseQuery)->count(),
            'upcoming_bookings' => (clone $bookingsBaseQuery)
                ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_ACCEPTED])
                ->where('scheduled_at', '>=', $now)
                ->count(),
            'completed_bookings' => (clone $bookingsBaseQuery)
                ->where('status', Booking::STATUS_COMPLETED)
                ->count(),
            'monthly_earnings' => round($monthlyEarnings, 2),
            'pending_payout_amount' => round($pendingPayoutAmount, 2),
            'avg_rating' => $averageRating > 0 ? round($averageRating, 1) : 0,
            'total_reviews' => (clone $reviewsBaseQuery)->count(),
            'available_slots_7d' => (clone $slotsBaseQuery)
                ->where('is_available', true)
                ->whereBetween('start_at', [
                    $now->copy()->startOfDay(),
                    $now->copy()->addDays(6)->endOfDay(),
                ])
                ->count(),
        ];

        $bookingCountSeries = $this->dailyCountSeries(
            clone $bookingsBaseQuery,
            'created_at',
            $now,
            14
        );

        $earningsSeries = $this->dailySumSeries(
            (clone $payoutsBaseQuery)->where('status', ProviderPayout::STATUS_PAID),
            'paid_at',
            'net_amount',
            $now,
            14
        );

        $statusCounts = (clone $bookingsBaseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $serviceTypeCounts = (clone $servicesBaseQuery)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $charts = [
            'bookings_daily_14d' => $bookingCountSeries,
            'earnings_daily_14d' => $earningsSeries,
            'booking_status_distribution' => [
                'labels' => ['Pending', 'Accepted', 'Completed', 'Cancelled', 'Rejected'],
                'series' => [
                    (int) ($statusCounts['pending'] ?? 0),
                    (int) ($statusCounts['accepted'] ?? 0),
                    (int) ($statusCounts['completed'] ?? 0),
                    (int) ($statusCounts['cancelled'] ?? 0),
                    (int) ($statusCounts['rejected'] ?? 0),
                ],
            ],
            'service_type_distribution' => [
                'labels' => ['1-on-1', 'Group'],
                'series' => [
                    (int) ($serviceTypeCounts['1-on-1'] ?? 0),
                    (int) ($serviceTypeCounts['group'] ?? 0),
                ],
            ],
        ];

        $recentBookings = (clone $bookingsBaseQuery)
            ->with([
                'customer:id,name,email',
                'service:id,name',
            ])
            ->latest('created_at')
            ->limit(8)
            ->get();

        return [
            'metrics' => $metrics,
            'charts' => $charts,
            'recentBookings' => $recentBookings,
        ];
    }

    /**
     * @param Builder<\Illuminate\Database\Eloquent\Model> $query
     */
    private function dailyCountSeries(Builder $query, string $dateColumn, Carbon $now, int $days): array
    {
        $start = $now->copy()->subDays($days - 1)->startOfDay();
        $end = $now->copy()->endOfDay();

        /** @var Collection<string,int> $raw */
        $raw = $query->whereBetween($dateColumn, [$start, $end])
            ->selectRaw("DATE($dateColumn) as day, COUNT(*) as total")
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

    /**
     * @param Builder<\Illuminate\Database\Eloquent\Model> $query
     */
    private function dailySumSeries(
        Builder $query,
        string $dateColumn,
        string $sumColumn,
        Carbon $now,
        int $days
    ): array {
        $start = $now->copy()->subDays($days - 1)->startOfDay();
        $end = $now->copy()->endOfDay();

        /** @var Collection<string,float> $raw */
        $raw = $query->whereBetween($dateColumn, [$start, $end])
            ->selectRaw("DATE($dateColumn) as day, SUM($sumColumn) as total")
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $series = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('M d');
            $series[] = round((float) ($raw[$key] ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }
}
