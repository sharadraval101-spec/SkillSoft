@extends('layouts.customer', ['title' => 'Feedback & Ratings | SkillSlot'])

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" id="customer-feedback-page" data-motion-section>
    <section class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-8" data-motion-card>
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-zinc-400">Feedback Center</p>
                <h1 class="mt-3 text-4xl font-semibold tracking-[-0.05em] text-zinc-950 sm:text-[3rem]">Rate completed services and share what customers should know.</h1>
                <p class="mt-4 text-[15px] leading-8 text-zinc-500">
                    Every completed booking can carry a rating and short review. Use this space to capture what went well, what could improve, and which services deserve another booking.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('customer.dashboard') }}" class="inline-flex min-h-[3.25rem] items-center justify-center rounded-xl border border-zinc-200 bg-white px-5 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50">
                    My Account
                </a>
                <a href="{{ route('customer.bookings.index') }}" class="inline-flex min-h-[3.25rem] items-center justify-center rounded-xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">
                    My Bookings
                </a>
            </div>
        </div>
    </section>

    <div class="mt-6 grid gap-4 md:grid-cols-3" data-motion-group>
        @foreach($feedbackStats as $stat)
            <article class="rounded-[1.6rem] border border-zinc-200 bg-white p-5 shadow-[0_18px_48px_-38px_rgba(15,23,42,0.28)]" data-motion-item data-motion-card>
                <p class="text-sm font-medium text-zinc-500">{{ $stat['label'] }}</p>
                <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-zinc-950">{{ $stat['value'] }}</p>
                <p class="mt-2 text-sm text-zinc-400">{{ $stat['hint'] }}</p>
            </article>
        @endforeach
    </div>

    <section class="mt-6 rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-7" data-motion-card>
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-400">Completed Bookings</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">Bookings ready for feedback</h2>
                <p class="mt-2 text-sm leading-7 text-zinc-500">Open any completed service below to leave a first rating or refine one you already wrote.</p>
            </div>
            @if($completedBookings->total() > 0)
                <p class="text-sm text-zinc-500">
                    Showing {{ $completedBookings->firstItem() }}-{{ $completedBookings->lastItem() }} of {{ $completedBookings->total() }}
                </p>
            @endif
        </div>

        @if($completedBookings->isEmpty())
            <div class="mt-6 rounded-[1.5rem] border border-dashed border-zinc-300 bg-zinc-50 px-6 py-12 text-center">
                <p class="text-lg font-semibold text-zinc-950">No completed bookings are ready for feedback</p>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-zinc-500">
                    Once a service is marked completed, it will appear here so you can rate the experience and leave notes for future customers.
                </p>
                <a href="{{ route('customer.bookings.index') }}" class="mt-6 inline-flex items-center justify-center rounded-xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">
                    View My Bookings
                </a>
            </div>
        @else
            <div class="mt-6 grid gap-4 xl:grid-cols-2" data-motion-group>
                @foreach($completedBookings as $booking)
                    @php
                        $review = $booking->review;
                        $location = collect([
                            $booking->branch?->name,
                            trim(implode(', ', array_filter([$booking->branch?->city, $booking->branch?->state]))),
                        ])->filter()->implode(' - ');
                    @endphp

                    <article class="rounded-[1.6rem] border border-zinc-200 bg-zinc-50 p-5 shadow-[0_18px_48px_-38px_rgba(15,23,42,0.22)]" data-motion-item data-motion-card>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">{{ $booking->booking_number }}</p>
                                <h3 class="mt-2 text-2xl font-semibold tracking-[-0.03em] text-zinc-950">{{ $booking->service?->name ?? 'Service' }}</h3>
                                <p class="mt-2 text-sm text-zinc-600">
                                    {{ $booking->provider?->name ?? 'Provider' }}
                                    @if($booking->serviceVariant)
                                        <span class="text-zinc-300">|</span> {{ $booking->serviceVariant->name }}
                                    @endif
                                </p>
                                <p class="mt-2 text-sm text-zinc-500">
                                    {{ $booking->scheduled_at?->format('d M Y, h:i A') }}
                                    @if($location !== '')
                                        <span class="text-zinc-300">|</span> {{ $location }}
                                    @endif
                                </p>
                            </div>

                            <span class="inline-flex shrink-0 rounded-full border px-3 py-1 text-xs font-semibold {{ $review ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                                {{ $review ? 'Feedback saved' : 'Needs feedback' }}
                            </span>
                        </div>

                        <div class="mt-5 rounded-[1.35rem] border border-zinc-200 bg-white p-4">
                            @if($review)
                                <div class="flex items-center gap-2 text-amber-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= (int) $review->rating ? 'opacity-100' : 'opacity-25' }}">&#9733;</span>
                                    @endfor
                                    <span class="ml-2 text-sm font-semibold text-zinc-950">{{ number_format((float) $review->rating, 1) }}/5</span>
                                </div>

                                @if($review->title)
                                    <h4 class="mt-3 text-sm font-semibold text-zinc-950">{{ $review->title }}</h4>
                                @endif

                                <p class="mt-2 text-sm leading-7 text-zinc-600">
                                    {{ $review->comment ?: 'A rating was submitted for this service.' }}
                                </p>

                                <p class="mt-3 text-xs text-zinc-500">
                                    Last updated {{ $review->updated_at?->format('d M Y, h:i A') }}
                                </p>
                            @else
                                <p class="text-sm leading-7 text-zinc-600">
                                    Capture the quality of the service, how the provider handled the booking, and whether you would recommend it again.
                                </p>
                            @endif
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('customer.feedback.edit', $booking) }}" class="inline-flex min-w-[160px] items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition {{ $review ? 'bg-zinc-950 text-white hover:bg-zinc-800' : 'bg-amber-400 text-zinc-950 hover:bg-amber-300' }}">
                                {{ $review ? 'Edit Feedback' : 'Rate This Service' }}
                            </a>
                            @if($booking->service?->slug)
                                <a href="{{ route('site.services.show', $booking->service->slug) }}" class="inline-flex min-w-[160px] items-center justify-center rounded-xl border border-zinc-200 bg-white px-5 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-100">
                                    View Service
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if($completedBookings->hasPages())
                <div class="mt-6 rounded-[1.5rem] border border-zinc-200 bg-zinc-50 p-4">
                    {{ $completedBookings->onEachSide(1)->links() }}
                </div>
            @endif
        @endif
    </section>
</section>
@endsection
