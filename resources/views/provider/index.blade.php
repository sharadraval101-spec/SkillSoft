@extends('layouts.app')

@section('content')
<div class="relative space-y-8 pb-6" id="provider-dashboard-page">
    <div class="absolute -top-24 -right-20 h-64 w-64 rounded-full bg-cyan-500/15 blur-3xl pointer-events-none"></div>
    <div class="absolute top-52 -left-20 h-64 w-64 rounded-full bg-emerald-500/15 blur-3xl pointer-events-none"></div>

    <section class="rounded-3xl border border-white/10 bg-gradient-to-r from-zinc-900/80 via-zinc-900/70 to-zinc-900/60 p-6 lg:p-8 shadow-2xl shadow-black/40">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-[0.2em] uppercase text-cyan-300">Provider Console</p>
                <h1 class="mt-3 text-3xl lg:text-4xl font-black tracking-tight text-white">Welcome back, {{ $provider->name }}</h1>
                <p class="mt-3 text-zinc-400 max-w-3xl">
                    Monitor services, bookings, earnings, and slot performance from one real-time dashboard.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('provider.services.index') }}" class="smooth-action-btn inline-flex items-center rounded-xl border border-cyan-400/35 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-200 hover:bg-cyan-500/20">
                    Manage Services
                </a>
                <a href="{{ route('provider.availability.index') }}" class="smooth-action-btn inline-flex items-center rounded-xl border border-emerald-400/35 bg-emerald-500/10 px-4 py-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/20">
                    Manage Availability
                </a>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <article class="dashboard-card">
            <p class="dashboard-label">Active Services</p>
            <p class="dashboard-value">{{ number_format($metrics['active_services']) }}</p>
            <p class="mt-2 text-xs text-zinc-500">Total: {{ number_format($metrics['total_services']) }}</p>
        </article>

        <article class="dashboard-card">
            <p class="dashboard-label">Total Bookings</p>
            <p class="dashboard-value">{{ number_format($metrics['total_bookings']) }}</p>
            <p class="mt-2 text-xs text-zinc-500">Upcoming: {{ number_format($metrics['upcoming_bookings']) }}</p>
        </article>

        <article class="dashboard-card">
            <p class="dashboard-label">Completed Bookings</p>
            <p class="dashboard-value">{{ number_format($metrics['completed_bookings']) }}</p>
            <p class="mt-2 text-xs text-zinc-500">Available Slots (7d): {{ number_format($metrics['available_slots_7d']) }}</p>
        </article>

        <article class="dashboard-card">
            <p class="dashboard-label">Monthly Earnings</p>
            <p class="dashboard-value">Rs. {{ number_format((float) $metrics['monthly_earnings'], 2) }}</p>
            <p class="mt-2 text-xs text-zinc-500">Pending Payout: Rs. {{ number_format((float) $metrics['pending_payout_amount'], 2) }}</p>
        </article>

        <article class="dashboard-card">
            <p class="dashboard-label">Average Rating</p>
            <p class="dashboard-value">{{ number_format((float) $metrics['avg_rating'], 1) }}</p>
            <p class="mt-2 text-xs text-zinc-500">Reviews: {{ number_format($metrics['total_reviews']) }}</p>
        </article>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <article class="dashboard-panel xl:col-span-2">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-white">Bookings Trend (Last 14 Days)</h2>
                <p class="text-sm text-zinc-400 mt-1">Daily booking volume for your account.</p>
            </div>
            <div id="providerBookingsChart" class="h-[300px]"></div>
        </article>

        <article class="dashboard-panel">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-white">Booking Status Mix</h2>
                <p class="text-sm text-zinc-400 mt-1">Distribution by booking state.</p>
            </div>
            <div id="providerBookingStatusChart" class="h-[300px]"></div>
        </article>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <article class="dashboard-panel xl:col-span-2">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-white">Earnings Trend (Last 14 Days)</h2>
                <p class="text-sm text-zinc-400 mt-1">Paid payout values by day.</p>
            </div>
            <div id="providerEarningsChart" class="h-[280px]"></div>
        </article>

        <article class="dashboard-panel">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-white">Service Type Split</h2>
                <p class="text-sm text-zinc-400 mt-1">1-on-1 versus group offerings.</p>
            </div>
            <div id="providerServiceTypeChart" class="h-[280px]"></div>
        </article>
    </section>

    <section class="dashboard-panel">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white">Recent Bookings</h2>
                <p class="text-sm text-zinc-400 mt-1">Latest booking activity for your services.</p>
            </div>
            <a href="{{ route('provider.payouts.index') }}" class="hidden sm:inline-flex smooth-action-btn rounded-lg border border-white/15 px-3 py-2 text-xs font-semibold text-zinc-300 hover:bg-white/10">
                View Payouts
            </a>
        </div>

        @if($recentBookings->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 py-10 text-center text-zinc-500">
                No bookings yet. Once customers book your services, activity will appear here.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-zinc-400 border-b border-white/10">
                            <th class="py-3 pr-4 font-semibold">Booking #</th>
                            <th class="py-3 pr-4 font-semibold">Service</th>
                            <th class="py-3 pr-4 font-semibold">Customer</th>
                            <th class="py-3 pr-4 font-semibold">Scheduled</th>
                            <th class="py-3 pr-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentBookings as $booking)
                            @php
                                $statusClass = match($booking->status) {
                                    'completed' => 'border-emerald-400/40 bg-emerald-500/10 text-emerald-200',
                                    'accepted' => 'border-cyan-400/40 bg-cyan-500/10 text-cyan-200',
                                    'cancelled', 'rejected' => 'border-rose-400/40 bg-rose-500/10 text-rose-200',
                                    default => 'border-amber-400/40 bg-amber-500/10 text-amber-200',
                                };
                            @endphp
                            <tr class="border-b border-white/5">
                                <td class="py-3 pr-4 text-zinc-100 font-semibold">{{ $booking->booking_number }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ $booking->service?->name ?? 'N/A' }}</td>
                                <td class="py-3 pr-4 text-zinc-300">
                                    {{ $booking->customer?->name ?? 'N/A' }}
                                    <span class="block text-xs text-zinc-500">{{ $booking->customer?->email ?? 'N/A' }}</span>
                                </td>
                                <td class="py-3 pr-4 text-zinc-400">{{ $booking->scheduled_at?->format('d M Y, h:i A') ?? '-' }}</td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ ucfirst($booking->status) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        (() => {
            const chartsData = @json($charts);

            const commonTheme = {
                chart: {
                    toolbar: { show: false },
                    foreColor: '#a1a1aa',
                    background: 'transparent'
                },
                grid: {
                    borderColor: 'rgba(255,255,255,0.08)'
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                dataLabels: { enabled: false },
                xaxis: {
                    labels: { style: { colors: '#a1a1aa' } }
                },
                yaxis: {
                    labels: { style: { colors: '#a1a1aa' } }
                }
            };

            const bookingsChartEl = document.querySelector('#providerBookingsChart');
            if (bookingsChartEl) {
                new ApexCharts(bookingsChartEl, {
                    ...commonTheme,
                    chart: { ...commonTheme.chart, type: 'line', height: 300 },
                    series: [{ name: 'Bookings', data: chartsData.bookings_daily_14d.series }],
                    xaxis: { ...commonTheme.xaxis, categories: chartsData.bookings_daily_14d.labels },
                    colors: ['#06b6d4']
                }).render();
            }

            const earningsChartEl = document.querySelector('#providerEarningsChart');
            if (earningsChartEl) {
                new ApexCharts(earningsChartEl, {
                    ...commonTheme,
                    chart: { ...commonTheme.chart, type: 'area', height: 280 },
                    series: [{ name: 'Earnings (Rs.)', data: chartsData.earnings_daily_14d.series }],
                    xaxis: { ...commonTheme.xaxis, categories: chartsData.earnings_daily_14d.labels },
                    yaxis: {
                        ...commonTheme.yaxis,
                        labels: {
                            style: { colors: '#a1a1aa' },
                            formatter: function (value) {
                                return 'Rs. ' + Number(value).toFixed(0);
                            }
                        }
                    },
                    colors: ['#34d399'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.35,
                            opacityTo: 0.05,
                            stops: [0, 100]
                        }
                    }
                }).render();
            }

            const bookingStatusChartEl = document.querySelector('#providerBookingStatusChart');
            if (bookingStatusChartEl) {
                new ApexCharts(bookingStatusChartEl, {
                    chart: {
                        type: 'donut',
                        height: 300,
                        foreColor: '#a1a1aa',
                        toolbar: { show: false }
                    },
                    labels: chartsData.booking_status_distribution.labels,
                    series: chartsData.booking_status_distribution.series,
                    legend: {
                        position: 'bottom',
                        labels: { colors: '#a1a1aa' }
                    },
                    stroke: {
                        colors: ['#0b0b0f']
                    },
                    colors: ['#f59e0b', '#22d3ee', '#34d399', '#f43f5e', '#a78bfa']
                }).render();
            }

            const serviceTypeChartEl = document.querySelector('#providerServiceTypeChart');
            if (serviceTypeChartEl) {
                new ApexCharts(serviceTypeChartEl, {
                    chart: {
                        type: 'bar',
                        height: 280,
                        toolbar: { show: false },
                        foreColor: '#a1a1aa',
                        background: 'transparent'
                    },
                    series: [{ name: 'Services', data: chartsData.service_type_distribution.series }],
                    xaxis: {
                        categories: chartsData.service_type_distribution.labels,
                        labels: { style: { colors: '#a1a1aa' } }
                    },
                    yaxis: {
                        labels: { style: { colors: '#a1a1aa' } }
                    },
                    grid: {
                        borderColor: 'rgba(255,255,255,0.08)'
                    },
                    dataLabels: { enabled: false },
                    plotOptions: {
                        bar: {
                            borderRadius: 8,
                            columnWidth: '45%'
                        }
                    },
                    colors: ['#818cf8']
                }).render();
            }
        })();
    </script>
@endpush
