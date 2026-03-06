@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <h1 class="text-2xl font-black text-white">Create Booking</h1>
        <p class="mt-2 text-sm text-zinc-400">
            Select provider, service, and slot. Slot availability includes booking window and block checks.
        </p>
    </section>

    <section class="dashboard-panel">
        <h2 class="text-lg font-bold text-white">Step 1: Find Slots</h2>
        <form method="GET" action="{{ route('customer.bookings.create') }}" class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Provider</label>
                <select name="provider_id" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100">
                    <option value="">Select provider</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}" @selected((string) $selectedProviderId === (string) $provider->id)>{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Service</label>
                <select name="service_id" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100">
                    <option value="">Select service</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected((string) $selectedServiceId === (string) $service->id)>
                            {{ $service->name }} ({{ $service->duration_minutes }}m)
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Branch</label>
                <select name="branch_id" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100">
                    <option value="">Default / Any</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Date</label>
                <input type="date" name="date" value="{{ $selectedDate->toDateString() }}" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-400">
                    Load Slots
                </button>
            </div>
        </form>
    </section>

    <section class="dashboard-panel">
        <h2 class="text-lg font-bold text-white">Step 2: Confirm Booking</h2>

        @if(!$selectedProviderId || !$selectedServiceId)
            <div class="mt-4 rounded-2xl border border-dashed border-white/15 py-8 text-center text-zinc-500">
                Select provider and service to view available slots.
            </div>
        @elseif($availableSlots->isEmpty())
            <div class="mt-4 rounded-2xl border border-dashed border-white/15 py-8 text-center text-zinc-500">
                No available slots for the selected criteria.
            </div>
        @else
            <form method="POST" action="{{ route('customer.bookings.store') }}" class="mt-4">
                @csrf
                <input type="hidden" name="provider_id" value="{{ $selectedProviderId }}">
                <input type="hidden" name="service_id" value="{{ $selectedServiceId }}">

                @if($selectedService && $selectedService->variants->isNotEmpty())
                    <div class="mb-4">
                        <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Variant (Optional)</label>
                        <select name="service_variant_id" class="mt-1 w-full max-w-md rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100">
                            <option value="">Base Service</option>
                            @foreach($selectedService->variants as $variant)
                                <option value="{{ $variant->id }}">
                                    {{ $variant->name }} - {{ number_format((float) $variant->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <p class="text-xs uppercase tracking-wider text-zinc-500 mb-3">Select Slot</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach($availableSlots as $slot)
                        <label class="rounded-xl border border-white/10 bg-zinc-950/40 px-4 py-3 hover:border-cyan-400/40 cursor-pointer">
                            <input type="radio" name="slot_id" value="{{ $slot['slot_id'] }}" class="mr-2 align-middle" required>
                            <span class="text-sm font-semibold text-zinc-100">{{ $slot['label'] }}</span>
                            <span class="block text-xs text-zinc-500 mt-1">{{ \Illuminate\Support\Carbon::parse($slot['start_at'])->format('d M Y') }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-5">
                    <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Notes (Optional)</label>
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100"></textarea>
                </div>

                <div class="mt-5 flex gap-3">
                    <button type="submit" class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-cyan-400">
                        Book Slot
                    </button>
                    <a href="{{ route('customer.bookings.index') }}" class="rounded-xl border border-white/15 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/5">
                        View History
                    </a>
                </div>
            </form>
        @endif
    </section>
</div>
@endsection
