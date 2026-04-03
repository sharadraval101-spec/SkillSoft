@extends('layouts.customer')

@php
    $rescheduleFilterCount = collect([$selectedBranchId])->filter(fn ($value) => filled($value))->count();
@endphp

@section('content')
<section class="mx-auto max-w-[1280px] px-4 pb-14 pt-10 sm:px-6 lg:px-8" data-motion-section>
    <div class="max-w-3xl">
        <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400" data-motion-kicker>Reschedule Booking</p>
        <h1 class="mt-4 text-[2.5rem] font-semibold tracking-[-0.05em] text-zinc-900 sm:text-[3.1rem]" data-motion-title>Update your appointment with the same booking flow</h1>
        <p class="mt-4 text-[15px] leading-8 text-zinc-500" data-motion-copy>
            Review the current appointment, pick a new date or branch if needed, and choose another available slot.
        </p>
    </div>

    <div class="mt-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="max-w-3xl">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400" data-motion-kicker>Reschedule Filters</p>
            <h2 class="mt-3 text-[2.2rem] font-semibold tracking-[-0.04em] text-zinc-900" data-motion-title>Change the date or branch from the drawer</h2>
            <p class="mt-3 text-[15px] leading-7 text-zinc-500" data-motion-copy>
                Use the right-side drawer to refresh the available slots without keeping the filters as a full card on the page.
            </p>
        </div>

        <button type="button" class="inline-flex min-w-[190px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" data-motion-actions data-motion-action data-filter-open="booking-reschedule" aria-controls="bookingRescheduleFilterDrawer">
            Reschedule Filters
            @if($rescheduleFilterCount > 0)
                <span class="ml-3 inline-flex min-h-[1.4rem] min-w-[1.4rem] items-center justify-center rounded-full bg-zinc-950 px-1 text-[11px] font-semibold text-white">
                    {{ $rescheduleFilterCount }}
                </span>
            @endif
        </button>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(22rem,0.8fr)]">
        <div class="space-y-6">
            <section class="rounded-[32px] bg-white p-6 shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-8" data-motion-card>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Current Slot</p>
                <h2 class="mt-3 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-900">{{ $booking->booking_number }}</h2>
                <p class="mt-3 text-sm text-zinc-500">{{ $booking->scheduled_at?->format('d M Y, h:i A') }}</p>
            </section>

            <section class="rounded-[32px] bg-white p-6 shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-8" data-motion-card>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Available Slots</p>
                        <h2 class="mt-2 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-900">Select a new time</h2>
                    </div>
                    <p class="text-sm text-zinc-500">Choose the slot that works best for you after updating the drawer filters.</p>
                </div>

                @if($availableSlots->isEmpty())
                    <div class="mt-8 rounded-[24px] border border-dashed border-zinc-300 bg-zinc-50 px-6 py-12 text-center text-sm text-zinc-500">
                        No available slots for the selected date. Open the reschedule filter drawer to try another date or branch.
                    </div>
                @else
                    <form method="POST" action="{{ route('customer.bookings.reschedule', $booking) }}" class="mt-8">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" data-motion-group>
                            @foreach($availableSlots as $slot)
                                <label class="group cursor-pointer" data-motion-item>
                                    <input type="radio" name="slot_id" value="{{ $slot['slot_id'] }}" class="peer sr-only" required>
                                    <div class="rounded-[22px] border border-zinc-200 bg-zinc-50 px-5 py-4 transition peer-checked:border-zinc-950 peer-checked:bg-white peer-checked:shadow-[0_16px_35px_rgba(0,0,0,0.08)] group-hover:border-zinc-900">
                                        <p class="text-lg font-medium text-zinc-900">{{ $slot['label'] }}</p>
                                        <p class="mt-2 text-sm text-zinc-500">{{ \Illuminate\Support\Carbon::parse($slot['start_at'])->format('d M Y') }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex min-w-[170px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                                Confirm Reschedule
                            </button>
                            <a href="{{ route('customer.bookings.index') }}" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
                                Back to Bookings
                            </a>
                        </div>
                    </form>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-[32px] bg-white p-6 shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-7" data-motion-aside data-motion-card>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Booking Details</p>
                <div class="mt-6 space-y-5">
                    <div class="rounded-[20px] bg-zinc-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Service</p>
                        <p class="mt-2 text-lg font-medium text-zinc-900">{{ $booking->service?->name ?? 'Service' }}</p>
                    </div>

                    <div class="rounded-[20px] bg-zinc-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Provider</p>
                        <p class="mt-2 text-lg font-medium text-zinc-900">{{ $booking->provider?->name ?? 'Provider' }}</p>
                    </div>

                    <div class="rounded-[20px] bg-zinc-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Branch</p>
                        <p class="mt-2 text-lg font-medium text-zinc-900">{{ $booking->branch?->name ?? 'Default / Any branch' }}</p>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</section>

<div class="pointer-events-none fixed inset-0 z-50 bg-black/35 opacity-0 transition duration-300" data-filter-overlay="booking-reschedule"></div>

<aside id="bookingRescheduleFilterDrawer" class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md translate-x-full flex-col bg-white shadow-2xl transition duration-300" data-filter-drawer="booking-reschedule">
    <div class="flex items-center justify-between border-b border-black/5 px-6 py-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Reschedule Filters</p>
            <h3 class="mt-2 text-xl font-semibold text-zinc-950">Find new slots</h3>
        </div>
        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-zinc-200 text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" aria-label="Close reschedule filters" data-motion-action data-filter-close="booking-reschedule">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/>
            </svg>
        </button>
    </div>

    <form method="GET" action="{{ route('customer.bookings.reschedule.form', $booking) }}" class="flex flex-1 flex-col overflow-y-auto px-6 py-6">
        <div class="space-y-6">
            <div>
                <label for="drawer-reschedule-date" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Date</label>
                <input id="drawer-reschedule-date" type="date" name="date" value="{{ $selectedDate->toDateString() }}" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
            </div>

            <div>
                <label for="drawer-reschedule-branch" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Branch</label>
                <select id="drawer-reschedule-branch" name="branch_id" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
                    <option value="">Default / Any</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-3 border-t border-black/5 pt-6">
            <button type="submit" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800" data-motion-action>
                Load Slots
            </button>
            <a href="{{ route('customer.bookings.reschedule.form', $booking) }}" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" data-motion-action>
                Reset
            </a>
        </div>
    </form>
</aside>
@endsection
