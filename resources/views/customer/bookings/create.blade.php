@extends('layouts.customer')

@php
    $filterAction = request()->routeIs('site.booking')
        ? route('site.booking')
        : route('customer.bookings.create');
    $canConfirmBooking = auth()->check() && (int) auth()->user()->role === \App\Models\User::ROLE_CUSTOMER;
    $selectedProvider = $providers->firstWhere('id', $selectedProviderId);
    $selectedBranch = $branches->firstWhere('id', $selectedBranchId);
    $bookingFilterCount = collect([$selectedProviderId, $selectedServiceId, $selectedBranchId])
        ->filter(fn ($value) => filled($value))
        ->count();
@endphp

@section('content')
<section class="mx-auto max-w-[1280px] px-4 pb-14 pt-10 sm:px-6 lg:px-8" data-motion-section>
    <div class="max-w-3xl">
        <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400" data-motion-kicker>Booking Page</p>
        <h1 class="mt-4 text-[2.65rem] font-semibold tracking-[-0.05em] text-zinc-900 sm:text-[3.5rem]" data-motion-title>
            Book a service with the same clean user-side experience
        </h1>
        <p class="mt-5 max-w-2xl text-[15px] leading-8 text-zinc-500" data-motion-copy>
            Choose a provider, select a service, check the available slots, and continue with a smooth booking flow that matches the new homepage layout and color system.
        </p>
    </div>

    @if($errors->any())
        <div class="mt-8 rounded-[24px] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700" data-motion-card>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="max-w-3xl">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400" data-motion-kicker>Booking Filters</p>
            <h2 class="mt-3 text-[2.2rem] font-semibold tracking-[-0.04em] text-zinc-900" data-motion-title>Choose provider, service, branch, and date</h2>
            <p class="mt-3 text-[15px] leading-7 text-zinc-500" data-motion-copy>
                Open the filter drawer from the right side to load available slots and update your booking summary.
            </p>
        </div>

        <button type="button" class="inline-flex min-w-[180px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" data-motion-actions data-motion-action data-filter-open="booking-create" aria-controls="bookingCreateFilterDrawer">
            Booking Filters
            @if($bookingFilterCount > 0)
                <span class="ml-3 inline-flex min-h-[1.4rem] min-w-[1.4rem] items-center justify-center rounded-full bg-zinc-950 px-1 text-[11px] font-semibold text-white">
                    {{ $bookingFilterCount }}
                </span>
            @endif
        </button>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(22rem,0.9fr)]">
        <div class="space-y-6">
            <section class="rounded-[32px] bg-white p-6 shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-8" data-motion-card>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Available Slots</p>
                        <h2 class="mt-2 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-900">Choose your slot</h2>
                    </div>
                    <p class="text-sm text-zinc-500">Available times update based on your drawer selections.</p>
                </div>

                @if(!$selectedProviderId || !$selectedServiceId)
                    <div class="mt-8 rounded-[24px] border border-dashed border-zinc-300 bg-zinc-50 px-6 py-12 text-center text-sm text-zinc-500">
                        Open the booking filter drawer and select a provider and service first to load booking slots.
                    </div>
                @elseif($availableSlots->isEmpty())
                    <div class="mt-8 rounded-[24px] border border-dashed border-zinc-300 bg-zinc-50 px-6 py-12 text-center text-sm text-zinc-500">
                        No available slots were found for the selected date and filters.
                    </div>
                @elseif($canConfirmBooking)
                    <form method="POST" action="{{ route('customer.bookings.store') }}" class="mt-8">
                        @csrf
                        <input type="hidden" name="provider_id" value="{{ $selectedProviderId }}">
                        <input type="hidden" name="service_id" value="{{ $selectedServiceId }}">

                        @if($selectedService && $selectedService->variants->isNotEmpty())
                            <div class="mb-6">
                                <label for="booking-variant" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Variant</label>
                                <select id="booking-variant" name="service_variant_id" class="mt-2 h-12 w-full max-w-md rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
                                    <option value="">Base Service</option>
                                    @foreach($selectedService->variants as $variant)
                                        <option value="{{ $variant->id }}">
                                            {{ $variant->name }} - &#8377;{{ number_format((float) $variant->price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

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

                        <div class="mt-6">
                            <label for="booking-notes" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Notes</label>
                            <textarea id="booking-notes" name="notes" rows="4" class="mt-2 w-full rounded-[18px] border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white"></textarea>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                                Confirm Booking
                            </button>
                            <a href="{{ route('customer.bookings.index') }}" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
                                View Booking History
                            </a>
                        </div>
                    </form>
                @else
                    <div class="mt-8 space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($availableSlots as $slot)
                                <div class="rounded-[22px] border border-zinc-200 bg-zinc-50 px-5 py-4">
                                    <p class="text-lg font-medium text-zinc-900">{{ $slot['label'] }}</p>
                                    <p class="mt-2 text-sm text-zinc-500">{{ \Illuminate\Support\Carbon::parse($slot['start_at'])->format('d M Y') }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="rounded-[24px] border border-zinc-200 bg-zinc-50 px-6 py-6">
                            <h3 class="text-xl font-semibold text-zinc-900">Ready to continue?</h3>
                            <p class="mt-2 max-w-2xl text-sm leading-7 text-zinc-500">
                                You can preview providers, services, and available slots here. Sign in with a customer account to complete the booking confirmation step.
                            </p>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <a href="{{ route('login') }}" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                                    Log In
                                </a>
                                <a href="{{ route('site.services.index') }}" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
                                    Browse Services
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-[32px] bg-white p-6 shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-7" data-motion-aside data-motion-card>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Booking Summary</p>
                <div class="mt-6 space-y-5">
                    <div class="rounded-[20px] bg-zinc-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Provider</p>
                        <p class="mt-2 text-lg font-medium text-zinc-900">{{ $selectedProvider?->name ?? 'Not selected yet' }}</p>
                    </div>

                    <div class="rounded-[20px] bg-zinc-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Service</p>
                        <p class="mt-2 text-lg font-medium text-zinc-900">{{ $selectedService?->name ?? 'Not selected yet' }}</p>
                        @if($selectedService)
                            <p class="mt-1 text-sm text-zinc-500">{{ (int) $selectedService->duration_minutes }} minutes</p>
                        @endif
                    </div>

                    <div class="rounded-[20px] bg-zinc-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Branch</p>
                        <p class="mt-2 text-lg font-medium text-zinc-900">{{ $selectedBranch?->name ?? 'Default / Any branch' }}</p>
                    </div>

                    <div class="rounded-[20px] bg-zinc-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Date</p>
                        <p class="mt-2 text-lg font-medium text-zinc-900">{{ $selectedDate->format('d M Y') }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-[32px] bg-zinc-950 px-6 py-7 text-white shadow-[0_18px_50px_rgba(0,0,0,0.12)] sm:px-7" data-motion-card>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Need Help?</p>
                <h3 class="mt-3 text-[1.8rem] font-semibold tracking-[-0.04em]">Booking guidance made simple</h3>
                <p class="mt-3 text-sm leading-7 text-zinc-300">
                    Start by selecting a provider and service. Once slots appear, choose the best time and continue with confirmation.
                </p>
            </section>
        </aside>
    </div>
</section>

<div class="pointer-events-none fixed inset-0 z-50 bg-black/35 opacity-0 transition duration-300" data-filter-overlay="booking-create"></div>

<aside id="bookingCreateFilterDrawer" class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md translate-x-full flex-col bg-white shadow-2xl transition duration-300" data-filter-drawer="booking-create">
    <div class="flex items-center justify-between border-b border-black/5 px-6 py-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Booking Filters</p>
            <h3 class="mt-2 text-xl font-semibold text-zinc-950">Find available slots</h3>
        </div>
        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-zinc-200 text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" aria-label="Close booking filters" data-motion-action data-filter-close="booking-create">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/>
            </svg>
        </button>
    </div>

    <form method="GET" action="{{ $filterAction }}" class="flex flex-1 flex-col overflow-y-auto px-6 py-6">
        <div class="space-y-6">
            <div>
                <label for="drawer-booking-provider" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Provider</label>
                <select id="drawer-booking-provider" name="provider_id" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
                    <option value="">Select provider</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}" @selected((string) $selectedProviderId === (string) $provider->id)>{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="drawer-booking-service" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Service</label>
                <select id="drawer-booking-service" name="service_id" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
                    <option value="">Select service</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected((string) $selectedServiceId === (string) $service->id)>
                            {{ $service->name }} ({{ $service->duration_minutes }}m)
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="drawer-booking-branch" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Branch</label>
                <select id="drawer-booking-branch" name="branch_id" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
                    <option value="">Default / Any</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="drawer-booking-date" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Date</label>
                <input id="drawer-booking-date" type="date" name="date" min="{{ now()->toDateString() }}" value="{{ $selectedDate->toDateString() }}" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-3 border-t border-black/5 pt-6">
            <button type="submit" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800" data-motion-action>
                Load Slots
            </button>
            <a href="{{ $filterAction }}" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" data-motion-action>
                Reset
            </a>
        </div>
    </form>
</aside>
@endsection
