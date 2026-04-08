@extends('layouts.customer')

@php
    $provider = $service->providerProfile?->user;
    $providerName = $provider?->name ?? 'Service Provider';
    $avgRating = round((float) ($service->avg_rating ?? 0), 1);
    $reviewsCount = (int) ($service->reviews_count ?? 0);
    $serviceTypeLabel = $service->type === 'group' ? 'Group session' : '1-on-1 session';
    $serviceLocation = $service->branch?->city
        ? trim(($service->branch->city ?? '').', '.($service->branch->state ?? ''))
        : 'Multiple locations';
    $primaryImage = $gallery[0] ?? 'https://picsum.photos/seed/'.urlencode((string) $service->id).'/1200/800';
    $galleryItems = collect($gallery)->filter()->values();

    if ($galleryItems->isEmpty()) {
        $galleryItems = collect([$primaryImage]);
    }

    $serviceIsUnavailable = (bool) ($serviceIsUnavailable ?? false);
    $serviceAvailabilityMessage = $serviceAvailabilityMessage ?? 'This service is currently not available for booking.';

    $bookingQuery = array_filter([
        'provider_id' => $service->providerProfile?->user_id,
        'service_id' => $service->id,
        'branch_id' => $selectedBranchId ?: $service->branch_id,
    ]);
@endphp

@section('content')
<section class="mx-auto max-w-[1280px] px-4 pb-8 pt-10 sm:px-6 lg:px-8" data-motion-section>
    <div class="overflow-hidden rounded-[36px] bg-white px-6 py-8 shadow-[0_20px_60px_rgba(15,23,42,0.08)] ring-1 ring-black/5 sm:px-8 lg:px-10 lg:py-10">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(18rem,0.95fr)] lg:items-end">
            <div class="max-w-3xl">
                <a href="{{ route('site.services.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-500 transition hover:text-zinc-950" data-motion-kicker data-motion-action>
                    <span aria-hidden="true">&larr;</span>
                    <span>Back to services</span>
                </a>
                <p class="mt-5 text-sm font-medium uppercase tracking-[0.22em] text-zinc-400" data-motion-kicker>{{ $service->category?->name ?? 'Service Detail' }}</p>
                <h1 class="mt-4 text-[2.65rem] font-semibold leading-[1.08] tracking-[-0.05em] text-zinc-950 sm:text-[3.6rem]" data-motion-title>{{ $service->name }}</h1>
                <p class="mt-5 max-w-2xl text-[15px] leading-8 text-zinc-500" data-motion-copy>
                    {{ \Illuminate\Support\Str::limit($service->description ?: 'Explore the service, compare options, and continue into booking with the same polished marketplace flow used across the rest of the user site.', 240) }}
                </p>
                @if($serviceIsUnavailable)
                    <div class="mt-6 inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700" data-motion-card>
                        Service Not Available
                    </div>
                @endif
                <div class="mt-8 flex flex-wrap items-center gap-3" data-motion-actions>
                    @if($serviceIsUnavailable)
                        <span class="inline-flex min-w-[170px] cursor-not-allowed items-center justify-center rounded-[14px] border border-amber-200 bg-amber-50 px-5 py-3.5 text-sm font-medium text-amber-700">
                            Service Not Available
                        </span>
                    @else
                        <a href="{{ route('site.booking', $bookingQuery) }}" class="inline-flex min-w-[170px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800" data-motion-action>Book This Service</a>
                    @endif
                    <a href="{{ route('site.services.index') }}" class="inline-flex min-w-[170px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" data-motion-action>Browse More</a>
                    <x-favorite-button :service="$service" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2" data-motion-group>
                <div class="rounded-[24px] bg-zinc-50 px-5 py-5" data-motion-item data-motion-card>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Starting Price</p>
                    <p class="mt-3 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-950">Rs. {{ number_format((float) $service->base_price, 0) }}</p>
                </div>
                <div class="rounded-[24px] bg-zinc-50 px-5 py-5" data-motion-item data-motion-card>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Rating</p>
                    <p class="mt-3 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-950">{{ $avgRating > 0 ? number_format($avgRating, 1) : 'New' }}</p>
                </div>
                <div class="rounded-[24px] bg-zinc-50 px-5 py-5" data-motion-item data-motion-card>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Duration</p>
                    <p class="mt-3 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-950">{{ (int) $service->duration_minutes }}m</p>
                </div>
                <div class="rounded-[24px] bg-zinc-50 px-5 py-5" data-motion-item data-motion-card>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Provider</p>
                    <p class="mt-3 text-[1.35rem] font-semibold tracking-[-0.04em] text-zinc-950">{{ $providerName }}</p>
                    <p class="mt-2 text-sm text-zinc-500">{{ $serviceLocation }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-[1280px] px-4 pb-20 sm:px-6 lg:px-8" data-motion-section>
    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <article class="overflow-hidden rounded-[30px] bg-white p-4 shadow-[0_18px_50px_rgba(15,23,42,0.08)] ring-1 ring-black/5 sm:p-5" data-motion-media data-motion-card>
                <div class="overflow-hidden rounded-[24px]">
                    <img id="service-main-image" src="{{ $primaryImage }}" alt="{{ $service->name }}" class="h-[18rem] w-full object-cover sm:h-[26rem]">
                </div>
                @if($galleryItems->count() > 1)
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4" data-motion-group>
                        @foreach($galleryItems as $image)
                            <button type="button" class="service-gallery-thumb overflow-hidden rounded-[18px] border {{ $loop->first ? 'border-zinc-950' : 'border-zinc-200' }} bg-zinc-50 transition hover:border-zinc-950" data-image="{{ $image }}" data-motion-item data-motion-action>
                                <img src="{{ $image }}" alt="{{ $service->name }} gallery" class="h-20 w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </article>

            <article class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.08)] ring-1 ring-black/5 sm:p-7" data-motion-card>
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">Service Overview</p>
                        <h2 class="mt-3 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-950">About this service</h2>
                    </div>
                    <span class="inline-flex rounded-full border border-zinc-200 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">{{ $serviceTypeLabel }}</span>
                </div>
                <p class="mt-5 whitespace-pre-line text-[15px] leading-8 text-zinc-500">{{ $service->description ?: 'Detailed description will be updated soon.' }}</p>

                @if($service->variants->isNotEmpty())
                    <div class="mt-8 border-t border-black/5 pt-6">
                        <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">Variants</p>
                        <div class="mt-5 grid gap-4 md:grid-cols-2" data-motion-group>
                            @foreach($service->variants as $variant)
                                <article class="rounded-[22px] border border-zinc-200 bg-zinc-50 px-5 py-5" data-motion-item data-motion-card>
                                    <p class="text-lg font-semibold tracking-[-0.03em] text-zinc-950">{{ $variant->name }}</p>
                                    <p class="mt-2 text-sm text-zinc-500">{{ (int) $variant->duration_minutes }} minutes</p>
                                    <p class="mt-3 text-sm font-semibold text-zinc-950">Rs. {{ number_format((float) $variant->price, 0) }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </article>

            <div class="grid gap-6 md:grid-cols-2">
                <article class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.08)] ring-1 ring-black/5 sm:p-7" data-motion-card>
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">Provider</p>
                    <h2 class="mt-3 text-xl font-semibold tracking-[-0.03em] text-zinc-950">Meet your provider</h2>
                    <div class="mt-5 flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100 text-lg font-semibold text-zinc-900">{{ strtoupper(substr($providerName, 0, 1)) }}</div>
                        <div>
                            <p class="text-base font-semibold text-zinc-950">{{ $providerName }}</p>
                            <p class="mt-1 text-sm text-zinc-500">{{ $provider?->email ?: 'Contact details available after booking.' }}</p>
                        </div>
                    </div>
                </article>

                <article class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.08)] ring-1 ring-black/5 sm:p-7" data-motion-card>
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">Location</p>
                    <h2 class="mt-3 text-xl font-semibold tracking-[-0.03em] text-zinc-950">Where it is offered</h2>
                    @if($service->branch)
                        <div class="mt-5 rounded-[22px] bg-zinc-50 px-5 py-5 text-sm text-zinc-500">
                            <p class="text-base font-semibold text-zinc-950">{{ $service->branch->name }}</p>
                            <p class="mt-3">{{ trim(($service->branch->address_line_1 ?? '').' '.($service->branch->address_line_2 ?? '')) }}</p>
                            <p class="mt-1">{{ $service->branch->city }}, {{ $service->branch->state }}, {{ $service->branch->country }}</p>
                        </div>
                    @else
                        <div class="mt-5 rounded-[22px] border border-dashed border-zinc-300 px-5 py-5 text-sm text-zinc-500">Branch information is not available yet for this service.</div>
                    @endif
                </article>
            </div>

            <article class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.08)] ring-1 ring-black/5 sm:p-7" data-motion-card>
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">Customer Reviews</p>
                        <h2 class="mt-3 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-950">What customers are saying</h2>
                    </div>
                    <p class="text-sm text-zinc-500">{{ $reviewsCount }} {{ \Illuminate\Support\Str::plural('review', $reviewsCount) }}</p>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2" data-motion-group>
                    @forelse($service->reviews as $review)
                        <div data-motion-item>
                            <x-review-card :review="$review" />
                        </div>
                    @empty
                        <div class="md:col-span-2 rounded-[22px] border border-dashed border-zinc-300 px-5 py-8 text-center text-sm text-zinc-500">No reviews yet for this service.</div>
                    @endforelse
                </div>
            </article>
        </div>

        <aside class="lg:sticky lg:top-24" data-motion-aside>
            <article class="rounded-[30px] bg-white p-5 shadow-[0_18px_50px_rgba(15,23,42,0.08)] ring-1 ring-black/5" data-motion-card>
                @if($serviceIsUnavailable)
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">Availability Status</p>
                    <h2 class="mt-3 text-xl font-semibold tracking-[-0.03em] text-zinc-950">Service not available</h2>
                    <div class="mt-5 rounded-[22px] border border-amber-200 bg-amber-50 px-5 py-5 text-sm leading-7 text-amber-800">
                        {{ $serviceAvailabilityMessage }}
                    </div>
                    <div class="mt-6 grid gap-3">
                        <a href="{{ route('site.services.index') }}" class="inline-flex w-full items-center justify-center rounded-[14px] bg-zinc-950 px-4 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800" data-motion-action>
                            Browse Other Services
                        </a>
                        <a href="{{ route('site.booking') }}" class="inline-flex w-full items-center justify-center rounded-[14px] border border-zinc-300 px-4 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" data-motion-action>
                            Explore Booking Page
                        </a>
                    </div>
                @else
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">Booking Panel</p>
                    <h2 class="mt-3 text-xl font-semibold tracking-[-0.03em] text-zinc-950">Check availability</h2>
                    <p class="mt-2 text-sm leading-7 text-zinc-500">Select a branch and date to see open slots before confirming your booking.</p>

                    <form id="serviceAvailabilityForm" method="GET" action="{{ route('site.services.show', $service->slug) }}" class="mt-5 space-y-4" data-boneyard-ignore>
                        <div>
                            <label for="detail-branch" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Branch</label>
                            <select id="detail-branch" name="branch_id" class="h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>{{ $branch->name }} ({{ $branch->city }}, {{ $branch->state }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="detail-date" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Date</label>
                            <input id="detail-date" type="date" name="date" value="{{ $selectedDate->toDateString() }}" class="h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-[14px] border border-zinc-300 px-4 py-3 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" data-motion-action>Refresh Availability</button>
                    </form>

                    <div class="mt-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Calendar</p>
                        <div id="availabilityCalendar" class="mt-3 grid grid-cols-2 gap-2">
                            @forelse($calendarDays as $day)
                                <button type="button" data-date="{{ $day['date']->toDateString() }}" class="js-calendar-day rounded-[14px] border px-3 py-2 text-center text-xs font-semibold transition {{ $day['date']->isSameDay($selectedDate) ? 'border-zinc-950 bg-zinc-950 text-white' : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:border-zinc-950 hover:bg-white hover:text-zinc-950' }}">
                                    <span class="block">{{ $day['date']->format('d M') }}</span>
                                    <span class="mt-0.5 block text-[10px] font-medium opacity-90">{{ $day['slot_count'] }} slots</span>
                                </button>
                            @empty
                                <p class="col-span-2 rounded-[16px] border border-dashed border-zinc-300 px-4 py-5 text-xs text-zinc-500">No schedule set for this provider.</p>
                            @endforelse
                        </div>
                    </div>

                    @auth
                        @if((int) auth()->user()->role === \App\Models\User::ROLE_CUSTOMER)
                            @if($provider)
                                <form method="POST" action="{{ route('customer.bookings.store') }}" class="mt-6 space-y-4">
                                    @csrf
                                    <input type="hidden" name="provider_id" value="{{ $provider->id }}">
                                    <input type="hidden" name="service_id" value="{{ $service->id }}">

                                    @if($service->variants->isNotEmpty())
                                        <div>
                                            <label for="detail-variant" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Variant</label>
                                            <select id="detail-variant" name="service_variant_id" class="h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                                                <option value="">Base service</option>
                                                @foreach($service->variants as $variant)
                                                    <option value="{{ $variant->id }}">{{ $variant->name }} (Rs. {{ number_format((float) $variant->price, 0) }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div>
                                        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Available Slots</p>
                                        <div id="availabilitySlots" class="grid max-h-48 grid-cols-1 gap-2 overflow-y-auto">
                                            @forelse($availableSlots as $slot)
                                                <label class="flex cursor-pointer items-center justify-between rounded-[14px] border border-zinc-200 bg-zinc-50 px-3 py-3 text-sm text-zinc-700 transition hover:border-zinc-950 hover:bg-white hover:text-zinc-950">
                                                    <span>{{ $slot['label'] }}</span>
                                                    <input type="radio" name="slot_id" value="{{ $slot['slot_id'] }}" class="h-4 w-4 border-zinc-300 text-zinc-900 focus:ring-zinc-300" @checked($loop->first)>
                                                </label>
                                            @empty
                                                <p class="rounded-[14px] border border-dashed border-zinc-300 px-3 py-4 text-xs text-zinc-500">No available slots for selected date.</p>
                                            @endforelse
                                        </div>
                                    </div>

                                    <button id="confirmBookingButton" type="submit" class="inline-flex w-full items-center justify-center rounded-[14px] bg-zinc-950 px-4 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800 {{ $availableSlots->isEmpty() ? 'cursor-not-allowed opacity-60' : '' }}" @disabled($availableSlots->isEmpty()) data-motion-action>Confirm Booking</button>
                                </form>
                            @else
                                <p class="mt-6 rounded-[18px] border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-700">Provider details are not available for booking right now.</p>
                            @endif
                        @else
                            <p class="mt-6 rounded-[18px] border border-zinc-200 bg-zinc-50 px-4 py-4 text-sm text-zinc-600">Booking is available for customer accounts.</p>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="mt-6 inline-flex w-full items-center justify-center rounded-[14px] bg-zinc-950 px-4 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800" data-motion-action>Login to Book</a>
                    @endauth
                @endif
            </article>
        </aside>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(function () {
        const mainImage = $('#service-main-image');
        const mainImageElement = mainImage.get(0);
        const galleryThumbs = $('.service-gallery-thumb');
        const gsap = window.gsap;
        const canAnimate = Boolean(gsap) && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const availabilityUrl = @json($availabilityUrl);
        const availabilityForm = $('#serviceAvailabilityForm');
        const dateInput = $('#detail-date');
        const branchInput = $('#detail-branch');
        const calendarContainer = $('#availabilityCalendar');
        const slotsContainer = $('#availabilitySlots');
        const confirmButton = $('#confirmBookingButton');

        const setActiveThumb = (image) => {
            galleryThumbs.removeClass('border-zinc-950').addClass('border-zinc-200');
            galleryThumbs.each(function () {
                if ($(this).data('image') === image) {
                    $(this).removeClass('border-zinc-200').addClass('border-zinc-950');
                }
            });
        };

        const animateAvailabilityItems = (container) => {
            if (!canAnimate || !container.length) return;
            const children = container.children().toArray();
            if (!children.length) return;
            gsap.from(children, { y: 18, autoAlpha: 0, duration: 0.34, stagger: 0.05, ease: 'power2.out', overwrite: 'auto' });
        };

        const escapeHtml = (value) => $('<div>').text(value ?? '').html();
        const setSubmitState = (enabled) => {
            if (!confirmButton.length) return;
            confirmButton.prop('disabled', !enabled);
            confirmButton.toggleClass('opacity-60 cursor-not-allowed', !enabled);
        };

        setActiveThumb(mainImage.attr('src'));

        galleryThumbs.on('click', function () {
            const image = $(this).data('image');
            if (!image || mainImage.attr('src') === image) return;
            setActiveThumb(image);
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
                    gsap.to(mainImageElement, { autoAlpha: 1, scale: 1, duration: 0.32, ease: 'power2.out', overwrite: 'auto' });
                }
            });
        });

        if (!availabilityForm.length || !dateInput.length || !branchInput.length || !calendarContainer.length) return;

        const renderSlots = (slots) => {
            if (!slotsContainer.length) return;
            slotsContainer.empty();

            if (!slots.length) {
                setSubmitState(false);
                slotsContainer.append('<p class="rounded-[14px] border border-dashed border-zinc-300 px-3 py-4 text-xs text-zinc-500">No available slots for selected date.</p>');
                animateAvailabilityItems(slotsContainer);
                window.ScrollTrigger?.refresh();
                return;
            }

            setSubmitState(true);
            slots.forEach(function (slot, index) {
                slotsContainer.append(`<label class="flex cursor-pointer items-center justify-between rounded-[14px] border border-zinc-200 bg-zinc-50 px-3 py-3 text-sm text-zinc-700 transition hover:border-zinc-950 hover:bg-white hover:text-zinc-950"><span>${escapeHtml(slot.label)}</span><input type="radio" name="slot_id" value="${escapeHtml(slot.slot_id)}" class="h-4 w-4 border-zinc-300 text-zinc-900 focus:ring-zinc-300" ${index === 0 ? 'checked' : ''}></label>`);
            });

            animateAvailabilityItems(slotsContainer);
            window.ScrollTrigger?.refresh();
        };

        const renderCalendar = (days) => {
            calendarContainer.empty();

            if (!days.length) {
                calendarContainer.append('<p class="col-span-2 rounded-[16px] border border-dashed border-zinc-300 px-4 py-5 text-xs text-zinc-500">No schedule set for this provider.</p>');
                animateAvailabilityItems(calendarContainer);
                window.ScrollTrigger?.refresh();
                return;
            }

            days.forEach(function (day) {
                const selectedClass = day.is_selected
                    ? 'border-zinc-950 bg-zinc-950 text-white'
                    : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:border-zinc-950 hover:bg-white hover:text-zinc-950';

                calendarContainer.append(`<button type="button" data-date="${day.date}" class="js-calendar-day rounded-[14px] border px-3 py-2 text-center text-xs font-semibold transition ${selectedClass}"><span class="block">${escapeHtml(day.label)}</span><span class="mt-0.5 block text-[10px] font-medium opacity-90">${day.slot_count} slots</span></button>`);
            });

            animateAvailabilityItems(calendarContainer);
            window.ScrollTrigger?.refresh();
        };

        const fetchAvailability = () => {
            const requestData = { date: dateInput.val() };
            const branchId = branchInput.val();
            if (branchId) requestData.branch_id = branchId;

            $.ajax({ url: availabilityUrl, method: 'GET', dataType: 'json', data: requestData })
                .done(function (response) {
                    const payload = response.data || {};
                    renderCalendar(payload.calendar_days || []);
                    renderSlots(payload.slots || []);
                })
                .fail(function () {
                    if (slotsContainer.length) {
                        slotsContainer.html('<p class="rounded-[14px] border border-rose-200 bg-rose-50 px-3 py-4 text-xs text-rose-700">Unable to load slots right now. Please try again.</p>');
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
            if (!date) return;
            dateInput.val(date);
            fetchAvailability();
        });
    });
</script>
@endpush
