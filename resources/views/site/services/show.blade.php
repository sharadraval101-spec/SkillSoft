
@extends('layouts.customer')

@section('content')
@php
    $provider = $service->providerProfile?->user;
    $avgRating = round((float) ($service->avg_rating ?? 0), 1);
@endphp

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" data-motion-section>
    <div class="mb-8">
        <a href="{{ route('site.services.index') }}" class="text-sm font-semibold text-sky-700 hover:text-sky-900" data-motion-kicker data-motion-action>&larr; Back to services</a>
        <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-sky-600" data-motion-kicker>{{ $service->category?->name ?? 'Service' }}</p>
        <h1 class="mt-2 customer-section-title" data-motion-title>{{ $service->name }}</h1>
        <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-sky-700" data-motion-copy>
            <span class="inline-flex items-center gap-1 font-semibold text-sky-900">&#9733; {{ $avgRating > 0 ? number_format($avgRating, 1) : 'New' }}</span>
            <span>{{ (int) ($service->reviews_count ?? 0) }} reviews</span>
            <span>{{ $service->duration_minutes }} mins</span>
            <span>Starting at INR {{ number_format((float) $service->base_price, 2) }}</span>
        </div>
    </div>

    <div class="grid items-start gap-8 lg:grid-cols-[1fr,22rem]">
        <div class="space-y-8">
            <div class="customer-surface overflow-hidden p-4 sm:p-5" data-motion-media data-motion-card>
                <div class="overflow-hidden rounded-2xl">
                    <img id="service-main-image" src="{{ $gallery[0] ?? 'https://picsum.photos/seed/'.urlencode((string) $service->id).'/1200/800' }}" alt="{{ $service->name }}" class="h-[18rem] w-full object-cover sm:h-[26rem]">
                </div>
                <div class="mt-4 grid grid-cols-4 gap-3">
                    @foreach($gallery as $image)
                        <button type="button" class="service-gallery-thumb overflow-hidden rounded-xl border border-sky-100" data-image="{{ $image }}">
                            <img src="{{ $image }}" alt="{{ $service->name }} gallery" class="h-20 w-full object-cover">
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="customer-surface p-6" data-motion-card>
                <h2 class="text-xl font-bold text-sky-950">About This Service</h2>
                <p class="mt-4 whitespace-pre-line text-sm leading-7 text-sky-700">
                    {{ $service->description ?: 'Detailed description will be updated soon.' }}
                </p>
                @if($service->variants->isNotEmpty())
                    <div class="mt-6 border-t border-sky-100 pt-5">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-sky-700">Service Variants</h3>
                        <div class="mt-3 space-y-3">
                            @foreach($service->variants as $variant)
                                <div class="flex flex-wrap items-center justify-between rounded-xl border border-sky-100 bg-sky-50/70 px-4 py-3 text-sm">
                                    <p class="font-semibold text-sky-900">{{ $variant->name }}</p>
                                    <p class="text-sky-700">{{ (int) $variant->duration_minutes }} mins - INR {{ number_format((float) $variant->price, 2) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="customer-surface p-6" data-motion-card>
                <h2 class="text-xl font-bold text-sky-950">Provider Information</h2>
                <div class="mt-4 flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-100 text-lg font-bold text-sky-700">
                        {{ strtoupper(substr($provider?->name ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-sky-900">{{ $provider?->name ?? 'Provider' }}</p>
                        <p class="text-sm text-sky-700">{{ $provider?->email }}</p>
                    </div>
                </div>
                @if($service->branch)
                    <div class="mt-4 rounded-xl border border-sky-100 bg-sky-50/70 p-4 text-sm text-sky-700">
                        <p class="font-semibold text-sky-900">{{ $service->branch->name }}</p>
                        <p class="mt-1">{{ trim(($service->branch->address_line_1 ?? '').' '.($service->branch->address_line_2 ?? '')) }}</p>
                        <p>{{ $service->branch->city }}, {{ $service->branch->state }}, {{ $service->branch->country }}</p>
                    </div>
                @endif
            </div>

            <div class="customer-surface p-6" data-motion-card>
                <h2 class="text-xl font-bold text-sky-950">Customer Reviews</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2" data-motion-group>
                    @forelse($service->reviews as $review)
                        <div data-motion-item>
                            <x-review-card :review="$review" />
                        </div>
                    @empty
                        <p class="text-sm text-sky-700 md:col-span-2">No reviews yet for this service.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="lg:sticky lg:top-24" data-motion-aside>
            <div class="customer-surface p-5" data-motion-card>
                <h2 class="text-lg font-bold text-sky-950">Book This Service</h2>
                <p class="mt-1 text-sm text-sky-700">Select date and slot to continue.</p>
                <form id="serviceAvailabilityForm" method="GET" action="{{ route('site.services.show', $service->slug) }}" class="mt-4 space-y-3">
                    <div>
                        <label for="detail-branch" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-sky-700">Branch</label>
                        <select id="detail-branch" name="branch_id" class="w-full rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>
                                    {{ $branch->name }} ({{ $branch->city }}, {{ $branch->state }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="detail-date" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-sky-700">Date</label>
                        <input id="detail-date" type="date" name="date" value="{{ $selectedDate->toDateString() }}"
                            class="w-full rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    </div>
                    <button type="submit" class="w-full rounded-xl border border-sky-300 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-50" data-motion-action>
                        Refresh Availability
                    </button>
                </form>

                <div class="mt-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-sky-700">Calendar</p>
                    <div id="availabilityCalendar" class="mt-3 grid grid-cols-2 gap-2">
                        @forelse($calendarDays as $day)
                            <button
                                type="button"
                                data-date="{{ $day['date']->toDateString() }}"
                                class="js-calendar-day rounded-xl border px-3 py-2 text-center text-xs font-semibold transition {{ $day['date']->isSameDay($selectedDate) ? 'border-sky-500 bg-sky-500 text-white' : 'border-sky-200 text-sky-700 hover:bg-sky-50' }}"
                            >
                                <span class="block">{{ $day['date']->format('d M') }}</span>
                                <span class="mt-0.5 block text-[10px] font-medium opacity-90">{{ $day['slot_count'] }} slots</span>
                            </button>
                        @empty
                            <p class="col-span-2 text-xs text-sky-700">No schedule set for this provider.</p>
                        @endforelse
                    </div>
                </div>

                @auth
                    @if((int) auth()->user()->role === \App\Models\User::ROLE_CUSTOMER)
                        @if($provider)
                            <form method="POST" action="{{ route('customer.bookings.store') }}" class="mt-5 space-y-3">
                                @csrf
                                <input type="hidden" name="provider_id" value="{{ $provider->id }}">
                                <input type="hidden" name="service_id" value="{{ $service->id }}">

                                @if($service->variants->isNotEmpty())
                                    <div>
                                        <label for="detail-variant" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-sky-700">Variant</label>
                                        <select id="detail-variant" name="service_variant_id" class="w-full rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                            <option value="">Base service</option>
                                            @foreach($service->variants as $variant)
                                                <option value="{{ $variant->id }}">{{ $variant->name }} (INR {{ number_format((float) $variant->price, 2) }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div>
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-sky-700">Available Slots</p>
                                    <div id="availabilitySlots" class="grid max-h-48 grid-cols-1 gap-2 overflow-y-auto">
                                        @forelse($availableSlots as $slot)
                                            <label class="flex cursor-pointer items-center justify-between rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-700 hover:bg-sky-50">
                                                <span>{{ $slot['label'] }}</span>
                                                <input type="radio" name="slot_id" value="{{ $slot['slot_id'] }}" class="h-4 w-4 border-sky-300 text-sky-600 focus:ring-sky-500" @checked($loop->first)>
                                            </label>
                                        @empty
                                            <p class="rounded-xl border border-dashed border-sky-200 px-3 py-4 text-xs text-sky-700">No available slots for selected date.</p>
                                        @endforelse
                                    </div>
                                </div>

                                <button id="confirmBookingButton" type="submit" class="w-full rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-500 {{ $availableSlots->isEmpty() ? 'cursor-not-allowed opacity-60' : '' }}" @disabled($availableSlots->isEmpty()) data-motion-action>
                                    Confirm Booking
                                </button>
                            </form>
                        @else
                            <p class="mt-5 rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm text-sky-700">
                                Provider details are not available for booking right now.
                            </p>
                        @endif
                    @else
                        <p class="mt-5 rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm text-sky-700">
                            Booking is available for customer accounts.
                        </p>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="mt-5 inline-flex w-full justify-center rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-500" data-motion-action>
                        Login to Book
                    </a>
                @endauth
            </div>
        </aside>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(function () {
        const mainImage = $('#service-main-image');
        const mainImageElement = mainImage.get(0);
        const gsap = window.gsap;
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const canAnimate = Boolean(gsap) && !prefersReducedMotion;

        const animateAvailabilityItems = function (container) {
            if (!canAnimate || !container.length) {
                return;
            }

            const children = container.children().toArray();
            if (!children.length) {
                return;
            }

            gsap.from(children, {
                y: 18,
                autoAlpha: 0,
                duration: 0.34,
                stagger: 0.05,
                ease: 'power2.out',
                overwrite: 'auto'
            });
        };

        $('.service-gallery-thumb').on('click', function () {
            const image = $(this).data('image');
            if (!image || mainImage.attr('src') === image) {
                return;
            }

            if (!canAnimate || !mainImageElement) {
                mainImage.attr('src', image);
                return;
            }

            gsap.to(mainImageElement, {
                autoAlpha: 0.35,
                scale: 0.985,
                duration: 0.18,
                ease: 'power2.out',
                overwrite: 'auto',
                onComplete: function () {
                    mainImage.attr('src', image);
                    gsap.to(mainImageElement, {
                        autoAlpha: 1,
                        scale: 1,
                        duration: 0.32,
                        ease: 'power2.out',
                        overwrite: 'auto'
                    });
                }
            });
        });

        const availabilityUrl = @json($availabilityUrl);
        const availabilityForm = $('#serviceAvailabilityForm');
        const dateInput = $('#detail-date');
        const branchInput = $('#detail-branch');
        const calendarContainer = $('#availabilityCalendar');
        const slotsContainer = $('#availabilitySlots');
        const confirmButton = $('#confirmBookingButton');

        if (!availabilityForm.length || !dateInput.length || !branchInput.length || !calendarContainer.length) {
            return;
        }

        const escapeHtml = function (value) {
            return $('<div>').text(value ?? '').html();
        };

        const setSubmitState = function (enabled) {
            if (!confirmButton.length) {
                return;
            }
            confirmButton.prop('disabled', !enabled);
            confirmButton.toggleClass('opacity-60 cursor-not-allowed', !enabled);
        };
        const renderSlots = function (slots) {
            if (!slotsContainer.length) {
                return;
            }

            slotsContainer.empty();
            if (!slots.length) {
                setSubmitState(false);
                slotsContainer.append('<p class="rounded-xl border border-dashed border-sky-200 px-3 py-4 text-xs text-sky-700">No available slots for selected date.</p>');
                animateAvailabilityItems(slotsContainer);
                window.ScrollTrigger?.refresh();
                return;
            }

            setSubmitState(true);
            slots.forEach(function (slot, index) {
                const slotId = escapeHtml(slot.slot_id);
                const label = escapeHtml(slot.label);
                const checked = index === 0 ? 'checked' : '';

                slotsContainer.append(`<label class="flex cursor-pointer items-center justify-between rounded-xl border border-sky-200 px-3 py-2 text-sm text-sky-700 hover:bg-sky-50">
                    <span>${label}</span>
                    <input type="radio" name="slot_id" value="${slotId}" class="h-4 w-4 border-sky-300 text-sky-600 focus:ring-sky-500" ${checked}>
                </label>`);
            });

            animateAvailabilityItems(slotsContainer);
            window.ScrollTrigger?.refresh();
        };

        const renderCalendar = function (days) {
            calendarContainer.empty();
            if (!days.length) {
                calendarContainer.append('<p class="col-span-2 text-xs text-sky-700">No schedule set for this provider.</p>');
                animateAvailabilityItems(calendarContainer);
                window.ScrollTrigger?.refresh();
                return;
            }

            days.forEach(function (day) {
                const selectedClass = day.is_selected
                    ? 'border-sky-500 bg-sky-500 text-white'
                    : 'border-sky-200 text-sky-700 hover:bg-sky-50';

                calendarContainer.append(`<button type="button" data-date="${day.date}" class="js-calendar-day rounded-xl border px-3 py-2 text-center text-xs font-semibold transition ${selectedClass}">
                    <span class="block">${escapeHtml(day.label)}</span>
                    <span class="mt-0.5 block text-[10px] font-medium opacity-90">${day.slot_count} slots</span>
                </button>`);
            });

            animateAvailabilityItems(calendarContainer);
            window.ScrollTrigger?.refresh();
        };

        const fetchAvailability = function () {
            const requestData = {
                date: dateInput.val()
            };
            const branchId = branchInput.val();
            if (branchId) {
                requestData.branch_id = branchId;
            }

            $.ajax({
                url: availabilityUrl,
                method: 'GET',
                dataType: 'json',
                data: requestData
            }).done(function (response) {
                const payload = response.data || {};
                renderCalendar(payload.calendar_days || []);
                renderSlots(payload.slots || []);
            }).fail(function () {
                if (slotsContainer.length) {
                    slotsContainer.html('<p class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-4 text-xs text-rose-700">Unable to load slots right now. Please try again.</p>');
                }
                setSubmitState(false);
            });
        };

        availabilityForm.on('submit', function (event) {
            event.preventDefault();
            fetchAvailability();
        });

        availabilityForm.on('change', '#detail-date, #detail-branch', function () {
            fetchAvailability();
        });

        calendarContainer.on('click', '.js-calendar-day', function () {
            const date = $(this).data('date');
            if (!date) {
                return;
            }
            dateInput.val(date);
            fetchAvailability();
        });
    });
</script>
@endpush
