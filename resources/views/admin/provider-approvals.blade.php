@extends('layouts.app')

@section('content')
<div id="admin-provider-approvals-page" class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <h1 class="text-2xl font-black text-white">Provider Requests</h1>
        <p class="mt-2 text-sm text-zinc-400">
            Review onboarding requests, handle legacy pending providers, and update provider operational availability from one admin workspace.
        </p>
    </section>

    <section class="dashboard-panel">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-white">New Provider Requests</h2>
                <p class="mt-1 text-sm text-zinc-400">Approval creates the provider account automatically and emails login credentials.</p>
            </div>
            <span class="rounded-full border border-cyan-400/30 bg-cyan-500/10 px-3 py-1 text-xs font-semibold text-cyan-200">{{ $providerRequests->count() }} Pending</span>
        </div>

        @if($providerRequests->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 py-10 text-center text-zinc-500">
                No pending provider requests.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-left text-zinc-400">
                            <th class="py-3 pr-4 font-semibold">Business</th>
                            <th class="py-3 pr-4 font-semibold">Owner</th>
                            <th class="py-3 pr-4 font-semibold">Email</th>
                            <th class="py-3 pr-4 font-semibold">Category</th>
                            <th class="py-3 pr-4 font-semibold">Documents</th>
                            <th class="py-3 pr-4 font-semibold">Submitted</th>
                            <th class="py-3 pr-4 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($providerRequests as $providerRequest)
                            <tr class="border-b border-white/5">
                                <td class="py-3 pr-4 text-zinc-100">{{ $providerRequest->business_name }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ $providerRequest->owner_name }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ $providerRequest->email }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ $providerRequest->serviceCategory?->name ?? 'N/A' }}</td>
                                <td class="py-3 pr-4 text-zinc-400">{{ count($providerRequest->documents ?? []) }}</td>
                                <td class="py-3 pr-4 text-zinc-400">{{ $providerRequest->created_at?->diffForHumans() }}</td>
                                <td class="py-3 pr-4">
                                    <button type="button" data-modal-open="provider-request-{{ $providerRequest->id }}" class="smooth-action-btn rounded-xl border border-cyan-400/35 px-3 py-2 text-xs font-semibold text-cyan-200 hover:bg-cyan-500/10">
                                        Review Request
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="dashboard-panel">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-white">Legacy Pending Providers</h2>
                <p class="mt-1 text-sm text-zinc-400">Backward-compatible approval queue for provider accounts created before the new request flow.</p>
            </div>
            <span class="rounded-full border border-amber-400/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-200">{{ $pendingProviders->count() }} Pending</span>
        </div>

        @if($pendingProviders->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 py-10 text-center text-zinc-500">
                No legacy pending providers.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-left text-zinc-400">
                            <th class="py-3 pr-4 font-semibold">Name</th>
                            <th class="py-3 pr-4 font-semibold">Email</th>
                            <th class="py-3 pr-4 font-semibold">Business</th>
                            <th class="py-3 pr-4 font-semibold">Joined</th>
                            <th class="py-3 pr-4 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingProviders as $providerProfile)
                            <tr class="border-b border-white/5">
                                <td class="py-3 pr-4 text-zinc-100">{{ $providerProfile->user?->name }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ $providerProfile->user?->email }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ $providerProfile->business_name }}</td>
                                <td class="py-3 pr-4 text-zinc-400">{{ $providerProfile->created_at?->diffForHumans() }}</td>
                                <td class="py-3 pr-4">
                                    <form method="POST" action="{{ route('admin.providers.approve', $providerProfile) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="smooth-action-btn rounded-xl border border-emerald-400/35 px-3 py-2 text-xs font-semibold text-emerald-200 hover:bg-emerald-500/10">
                                            Approve Legacy Provider
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="dashboard-panel">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-white">Active Provider Availability</h2>
                <p class="mt-1 text-sm text-zinc-400">Admins can mark a provider unavailable and trigger automated rescheduling for affected appointments.</p>
            </div>
            <span class="rounded-full border border-emerald-400/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-200">{{ $activeProviders->count() }} Active</span>
        </div>

        @if($activeProviders->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 py-10 text-center text-zinc-500">
                No active providers available yet.
            </div>
        @else
            <div class="space-y-4">
                @foreach($activeProviders as $provider)
                    <article class="rounded-2xl border border-white/10 bg-zinc-950/40 p-4">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                            <div>
                                <p class="text-base font-semibold text-zinc-100">{{ $provider->name }}</p>
                                <p class="mt-1 text-sm text-zinc-400">{{ $provider->email }}</p>
                                <p class="mt-1 text-sm text-zinc-500">{{ $provider->providerProfile?->business_name ?? 'Provider business not set' }}</p>
                            </div>

                            <form method="POST" action="{{ route('admin.providers.availability.update', $provider) }}" class="grid w-full gap-3 xl:max-w-4xl xl:grid-cols-4">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Status</label>
                                    <select name="availability_status" class="w-full rounded-xl border border-white/10 bg-zinc-950/70 px-3 py-2 text-sm text-zinc-100">
                                        <option value="available" @selected(($provider->providerProfile?->availability_status ?? 'available') === 'available')>Available</option>
                                        <option value="unavailable" @selected(($provider->providerProfile?->availability_status ?? 'available') === 'unavailable')>Unavailable</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">From</label>
                                    <input type="date" name="unavailable_from" value="{{ $provider->providerProfile?->unavailable_from?->toDateString() }}" class="w-full rounded-xl border border-white/10 bg-zinc-950/70 px-3 py-2 text-sm text-zinc-100">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Until</label>
                                    <input type="date" name="unavailable_until" value="{{ $provider->providerProfile?->unavailable_until?->toDateString() }}" class="w-full rounded-xl border border-white/10 bg-zinc-950/70 px-3 py-2 text-sm text-zinc-100">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Reason</label>
                                    <input type="text" name="unavailability_reason" value="{{ $provider->providerProfile?->unavailability_reason }}" placeholder="Vacation / Emergency / Medical Leave" class="w-full rounded-xl border border-white/10 bg-zinc-950/70 px-3 py-2 text-sm text-zinc-100">
                                </div>
                                <div class="xl:col-span-4 flex items-center justify-end">
                                    <button type="submit" class="smooth-action-btn rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-rose-400">
                                        Save Availability Status
                                    </button>
                                </div>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>

@foreach($providerRequests as $providerRequest)
    <x-modal id="provider-request-{{ $providerRequest->id }}" title="Provider Request Review" max-width="max-w-3xl">
        <div class="space-y-4 text-sm text-zinc-300">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-white/10 bg-zinc-950/50 p-4">
                    <p class="text-xs uppercase tracking-wider text-zinc-500">Business</p>
                    <p class="mt-1 font-semibold text-zinc-100">{{ $providerRequest->business_name }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-zinc-950/50 p-4">
                    <p class="text-xs uppercase tracking-wider text-zinc-500">Owner</p>
                    <p class="mt-1 font-semibold text-zinc-100">{{ $providerRequest->owner_name }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-zinc-950/50 p-4">
                    <p class="text-xs uppercase tracking-wider text-zinc-500">Email</p>
                    <p class="mt-1 font-semibold text-zinc-100">{{ $providerRequest->email }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-zinc-950/50 p-4">
                    <p class="text-xs uppercase tracking-wider text-zinc-500">Phone</p>
                    <p class="mt-1 font-semibold text-zinc-100">{{ $providerRequest->phone }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-zinc-950/50 p-4 sm:col-span-2">
                    <p class="text-xs uppercase tracking-wider text-zinc-500">Category</p>
                    <p class="mt-1 font-semibold text-zinc-100">{{ $providerRequest->serviceCategory?->name ?? 'N/A' }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-zinc-950/50 p-4 sm:col-span-2">
                    <p class="text-xs uppercase tracking-wider text-zinc-500">Business Details</p>
                    <p class="mt-1 leading-7 text-zinc-300">{{ $providerRequest->business_details }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-zinc-950/50 p-4 sm:col-span-2">
                    <p class="text-xs uppercase tracking-wider text-zinc-500">Address</p>
                    <p class="mt-1 leading-7 text-zinc-300">{{ $providerRequest->address }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-zinc-950/50 p-4 sm:col-span-2">
                    <p class="text-xs uppercase tracking-wider text-zinc-500">Documents</p>
                    @if(empty($providerRequest->documents))
                        <p class="mt-1 text-zinc-400">No documents uploaded.</p>
                    @else
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($providerRequest->documents as $document)
                                <a href="{{ $document['url'] ?? '#' }}" target="_blank" rel="noreferrer" class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-cyan-200 hover:bg-cyan-500/10">
                                    {{ $document['name'] ?? 'Document' }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <form method="POST" action="{{ route('admin.provider-requests.approve', $providerRequest) }}" class="rounded-xl border border-emerald-400/20 bg-emerald-500/5 p-4">
                    @csrf
                    @method('PATCH')
                    <p class="text-sm font-semibold text-emerald-100">Approve Request</p>
                    <p class="mt-2 text-xs leading-6 text-emerald-200/80">This creates the provider account, activates the provider profile, and emails credentials.</p>
                    <button type="submit" class="mt-4 smooth-action-btn rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                        Approve and Create Account
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.provider-requests.reject', $providerRequest) }}" class="rounded-xl border border-rose-400/20 bg-rose-500/5 p-4">
                    @csrf
                    @method('PATCH')
                    <label class="text-sm font-semibold text-rose-100">Reject Request</label>
                    <textarea name="reason" rows="4" placeholder="Optional reason for rejection" class="mt-3 w-full rounded-xl border border-white/10 bg-zinc-950/70 px-3 py-2 text-sm text-zinc-100"></textarea>
                    <button type="submit" class="mt-4 smooth-action-btn rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                        Reject Request
                    </button>
                </form>
            </div>
        </div>
    </x-modal>
@endforeach
@endsection
