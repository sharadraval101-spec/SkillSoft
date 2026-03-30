@extends('layouts.customer')

@php
    $filterAction = request()->routeIs('site.booking')
        ? route('site.booking')
        : route('customer.bookings.create');
    $canConfirmBooking = auth()->check() && (int) auth()->user()->role === \App\Models\User::ROLE_CUSTOMER;
    $selectedProvider = $providers->firstWhere('id', $selectedProviderId);
    $selectedBranch = $branches->firstWhere('id', $selectedBranchId);
@endphp

@section('content')
<section class="mx-auto max-w-[1280px] px-4 pb-14 pt-10 sm:px-6 lg:px-8">
    <div class="max-w-3xl">
        <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">Booking Page</p>
        <h1 class="mt-4 text-[2.65rem] font-semibold tracking-[-0.05em] text-zinc-900 sm:text-[3.5rem]">
            Book a service with the same clean user-side experience
        </h1>
        <p class="mt-5 max-w-2xl text-[15px] leading-8 text-zinc-500">
            Choose a provider, select a service, check the available slots, and continue with a smooth booking flow that matches the new homepage layout and color system.
        </p>
    </div>

    @if($errors->any())
        <div class="mt-8 rounded-[24px] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-10 grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(22rem,0.9fr)]">
        <div class="space-y-6">
            <section class="rounded-[32px] bg-white p-6 shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Step 1</p>
                        <h2 class="mt-2 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-900">Find available slots</h2>
                    </div>
                    <p class="text-sm text-zinc-500">Choose provider, service, branch, and date.</p>
                </div>

                <form method="GET" action="{{ $filterAction }}" class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div class="xl:col-span-1">
                        <label for="booking-provider" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Provider</label>
                        <select id="booking-provider" name="provider_id" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
                            <option value="">Select provider</option>
                            @foreach($providers as $provider)
                                <option value="{{ $provider->id }}" @selected((string) $selectedProviderId === (string) $provider->id)>{{ $provider->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-1">
                        <label for="booking-service" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Service</label>
                        <select id="booking-service" name="service_id" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
                            <option value="">Select service</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" @selected((string) $selectedServiceId === (string) $service->id)>
                                    {{ $service->name }} ({{ $service->duration_minutes }}m)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-1">
                        <label for="booking-branch" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Branch</label>
                        <select id="booking-branch" name="branch_id" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
                            <option value="">Default / Any</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-1">
                        <label for="booking-date" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Date</label>
                        <input id="booking-date" type="date" name="date" min="{{ now()->toDateString() }}" value="{{ $selectedDate->toDateString() }}" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
                    </div>

                    <div class="flex items-end xl:col-span-1">
                        <button type="submit" class="inline-flex h-12 w-full items-center justify-center rounded-[14px] bg-zinc-950 px-4 text-sm font-medium text-white transition hover:bg-zinc-800">
                            Load Slots
                        </button>
                    </div>
                </form>
            </section>

            <section class="rounded-[32px] bg-white p-6 shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Step 2</p>
                        <h2 class="mt-2 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-900">Choose your slot</h2>
                    </div>
                    <p class="text-sm text-zinc-500">Available times update based on your selections.</p>
                </div>

                @if(!$selectedProviderId || !$selectedServiceId)
                    <div class="mt-8 rounded-[24px] border border-dashed border-zinc-300 bg-zinc-50 px-6 py-12 text-center text-sm text-zinc-500">
                        Select a provider and a service first to load booking slots.
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

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($availableSlots as $slot)
                                <label class="group cursor-pointer">
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
            <section class="rounded-[32px] bg-white p-6 shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-7">
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

            <section class="rounded-[32px] bg-zinc-950 px-6 py-7 text-white shadow-[0_18px_50px_rgba(0,0,0,0.12)] sm:px-7">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Need Help?</p>
                <h3 class="mt-3 text-[1.8rem] font-semibold tracking-[-0.04em]">Booking guidance made simple</h3>
                <p class="mt-3 text-sm leading-7 text-zinc-300">
                    Start by selecting a provider and service. Once slots appear, choose the best time and continue with confirmation.
                </p>
            </section>
        </aside>
    </div>
</section>
@endsection
