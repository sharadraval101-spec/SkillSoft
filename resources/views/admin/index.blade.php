@extends('layouts.app')

@section('content')
<div class="relative space-y-8 pb-6">
    <div class="absolute -top-24 -right-20 h-64 w-64 rounded-full bg-cyan-500/15 blur-3xl pointer-events-none"></div>
    <div class="absolute top-48 -left-24 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>

    <section class="dashboard-hero rounded-3xl border border-white/10 bg-gradient-to-r from-zinc-900/80 via-zinc-900/70 to-zinc-900/60 p-6 lg:p-8 shadow-2xl shadow-black/40">
        <p class="text-xs font-semibold tracking-[0.2em] uppercase text-cyan-300">System Overview</p>
        <h1 class="mt-3 text-3xl lg:text-4xl font-black tracking-tight text-white">Admin Dashboard</h1>
        <p class="mt-3 text-zinc-400 max-w-3xl">
            Monitor users, registrations, and login activity across SkillSlot in one view.
        </p>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        <article class="dashboard-card">
            <p class="dashboard-label">Total Users</p>
            <p class="dashboard-value">{{ number_format($metrics['total_users']) }}</p>
        </article>
        <article class="dashboard-card">
            <p class="dashboard-label">Admins</p>
            <p class="dashboard-value">{{ number_format($metrics['total_admins']) }}</p>
        </article>
        <article class="dashboard-card">
            <p class="dashboard-label">Providers</p>
            <p class="dashboard-value">{{ number_format($metrics['total_providers']) }}</p>
        </article>
        <article class="dashboard-card">
            <p class="dashboard-label">Students</p>
            <p class="dashboard-value">{{ number_format($metrics['total_students']) }}</p>
        </article>
        <article class="dashboard-card">
            <p class="dashboard-label">Registrations (7 Days)</p>
            <p class="dashboard-value">{{ number_format($metrics['registrations_7d']) }}</p>
        </article>
        <article class="dashboard-card">
            <p class="dashboard-label">Logins (24 Hours)</p>
            <p class="dashboard-value">{{ number_format($metrics['logins_24h']) }}</p>
        </article>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <article class="dashboard-panel xl:col-span-2">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-white">Registrations (Last 14 Days)</h2>
                <p class="text-sm text-zinc-400 mt-1">Daily trend of new user signups.</p>
            </div>
            <div id="registrationsChart" class="h-[300px]"></div>
        </article>

        <article class="dashboard-panel">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-white">Role Distribution</h2>
                <p class="text-sm text-zinc-400 mt-1">Current user mix by role.</p>
            </div>
            <div id="rolesChart" class="h-[300px]"></div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-4">
        <article class="dashboard-panel">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-white">Login Activity (Last 14 Days)</h2>
                <p class="text-sm text-zinc-400 mt-1">Daily successful sign-ins.</p>
            </div>
            <div id="loginsChart" class="h-[280px]"></div>
        </article>
    </section>

    <section class="dashboard-panel">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white">Recent Activity</h2>
                <p class="text-sm text-zinc-400 mt-1">Most recent auth-related events.</p>
            </div>
        </div>

        @if($recentActivities->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 py-10 text-center text-zinc-500">
                No activity recorded yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-zinc-400 border-b border-white/10">
                            <th class="py-3 pr-4 font-semibold">Event</th>
                            <th class="py-3 pr-4 font-semibold">User</th>
                            <th class="py-3 pr-4 font-semibold">IP Address</th>
                            <th class="py-3 pr-4 font-semibold">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivities as $activity)
                            @php
                                $eventClass = match($activity->event_type) {
                                    'auth.login' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
                                    'auth.logout' => 'bg-zinc-500/15 text-zinc-300 border-zinc-500/30',
                                    default => 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30',
                                };
                            @endphp
                            <tr class="border-b border-white/5">
                                <td class="py-3 pr-4">
                                    <span class="inline-flex rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $eventClass }}">
                                        {{ $activity->event_label }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-zinc-200">
                                    {{ $activity->user?->name ?? 'System' }}
                                    <span class="block text-xs text-zinc-500">
                                        {{ $activity->user?->email ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-zinc-300">{{ $activity->ip_address ?? 'N/A' }}</td>
                                <td class="py-3 pr-4 text-zinc-400">{{ $activity->created_at?->diffForHumans() }}</td>
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
            const chartRegistry = [];

            const getPalette = () => {
                const styles = getComputedStyle(document.documentElement);

                return {
                    text: styles.getPropertyValue('--dashboard-chart-text').trim() || '#a1a1aa',
                    grid: styles.getPropertyValue('--dashboard-chart-grid').trim() || 'rgba(255, 255, 255, 0.08)',
                    stroke: styles.getPropertyValue('--dashboard-chart-stroke').trim() || '#0b0b0f',
                };
            };

            const createCommonTheme = (palette) => ({
                chart: {
                    toolbar: { show: false },
                    foreColor: palette.text,
                    background: 'transparent'
                },
                grid: {
                    borderColor: palette.grid
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                dataLabels: { enabled: false },
                xaxis: {
                    labels: { style: { colors: palette.text } }
                },
                yaxis: {
                    labels: { style: { colors: palette.text } }
                }
            });

            const destroyCharts = () => {
                while (chartRegistry.length) {
                    const chart = chartRegistry.pop();
                    chart?.destroy();
                }
            };

            const renderCharts = () => {
                destroyCharts();

                const palette = getPalette();
                const commonTheme = createCommonTheme(palette);

                const registrationsChart = document.querySelector('#registrationsChart');
                if (registrationsChart) {
                    chartRegistry.push(new ApexCharts(registrationsChart, {
                        ...commonTheme,
                        chart: { ...commonTheme.chart, type: 'line', height: 300 },
                        series: [{ name: 'Registrations', data: chartsData.registrations_daily_14d.series }],
                        xaxis: { ...commonTheme.xaxis, categories: chartsData.registrations_daily_14d.labels },
                        colors: ['#06b6d4']
                    }));
                }

                const loginsChart = document.querySelector('#loginsChart');
                if (loginsChart) {
                    chartRegistry.push(new ApexCharts(loginsChart, {
                        ...commonTheme,
                        chart: { ...commonTheme.chart, type: 'line', height: 280 },
                        series: [{ name: 'Logins', data: chartsData.logins_daily_14d.series }],
                        xaxis: { ...commonTheme.xaxis, categories: chartsData.logins_daily_14d.labels },
                        colors: ['#818cf8']
                    }));
                }

                const rolesChart = document.querySelector('#rolesChart');
                if (rolesChart) {
                    chartRegistry.push(new ApexCharts(rolesChart, {
                        chart: {
                            type: 'donut',
                            height: 300,
                            foreColor: palette.text,
                            toolbar: { show: false }
                        },
                        labels: chartsData.role_distribution.labels,
                        series: chartsData.role_distribution.series,
                        legend: {
                            position: 'bottom',
                            labels: { colors: palette.text }
                        },
                        stroke: {
                            colors: [palette.stroke]
                        },
                        colors: ['#22d3ee', '#818cf8', '#34d399']
                    }));
                }

                chartRegistry.forEach((chart) => chart.render());
            };

            renderCharts();
            window.addEventListener('skillslot:dashboard-theme-changed', renderCharts);
        })();
    </script>
@endpush
