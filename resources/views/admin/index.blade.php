@extends('layouts.app')

@section('content')
<div class="relative space-y-8 pb-6">
    <div class="absolute -top-24 -right-20 h-64 w-64 rounded-full bg-cyan-500/15 blur-3xl pointer-events-none"></div>
    <div class="absolute top-48 -left-24 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>

    <section class="rounded-3xl border border-white/10 bg-gradient-to-r from-zinc-900/80 via-zinc-900/70 to-zinc-900/60 p-6 lg:p-8 shadow-2xl shadow-black/40">
        <p class="text-xs font-semibold tracking-[0.2em] uppercase text-cyan-300">System Overview</p>
        <h1 class="mt-3 text-3xl lg:text-4xl font-black tracking-tight text-white">Admin Dashboard</h1>
        <p class="mt-3 text-zinc-400 max-w-3xl">
            Monitor users, registrations, and login activity across SkillSoft in one view.
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

        new ApexCharts(document.querySelector('#registrationsChart'), {
            ...commonTheme,
            chart: { ...commonTheme.chart, type: 'line', height: 300 },
            series: [{ name: 'Registrations', data: chartsData.registrations_daily_14d.series }],
            xaxis: { ...commonTheme.xaxis, categories: chartsData.registrations_daily_14d.labels },
            colors: ['#06b6d4']
        }).render();

        new ApexCharts(document.querySelector('#loginsChart'), {
            ...commonTheme,
            chart: { ...commonTheme.chart, type: 'line', height: 280 },
            series: [{ name: 'Logins', data: chartsData.logins_daily_14d.series }],
            xaxis: { ...commonTheme.xaxis, categories: chartsData.logins_daily_14d.labels },
            colors: ['#818cf8']
        }).render();

        new ApexCharts(document.querySelector('#rolesChart'), {
            chart: {
                type: 'donut',
                height: 300,
                foreColor: '#a1a1aa',
                toolbar: { show: false }
            },
            labels: chartsData.role_distribution.labels,
            series: chartsData.role_distribution.series,
            legend: {
                position: 'bottom',
                labels: { colors: '#a1a1aa' }
            },
            stroke: {
                colors: ['#0b0b0f']
            },
            colors: ['#22d3ee', '#818cf8', '#34d399']
        }).render();
    </script>
@endpush
