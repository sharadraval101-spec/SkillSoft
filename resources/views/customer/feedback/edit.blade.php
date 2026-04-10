@extends('layouts.customer', ['title' => 'Rate Service | SkillSlot'])

@section('content')
@php
    $price = $booking->serviceVariant?->price ?? $booking->service?->base_price;
    $location = collect([
        $booking->branch?->name,
        trim(implode(', ', array_filter([$booking->branch?->city, $booking->branch?->state]))),
    ])->filter()->implode(' - ');
@endphp

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" id="customer-feedback-edit-page" data-motion-section>
    <section class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-8" data-motion-card>
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-zinc-400">Feedback Form</p>
                <h1 class="mt-3 text-4xl font-semibold tracking-[-0.05em] text-zinc-950 sm:text-[3rem]">
                    {{ $review ? 'Update your service review' : 'Rate your completed service' }}
                </h1>
                <p class="mt-4 text-[15px] leading-8 text-zinc-500">
                    Share a rating for the finished booking, add a short headline if you want, and describe the experience in your own words.
                </p>
            </div>

            <a href="{{ route('customer.feedback.index') }}" class="inline-flex min-h-[3.25rem] items-center justify-center rounded-xl border border-zinc-200 bg-white px-5 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50">
                Back to Feedback
            </a>
        </div>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.15fr)]">
        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)]" data-motion-card>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-400">Booking Snapshot</p>
                <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">{{ $booking->service?->name ?? 'Service' }}</h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-[1.35rem] border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Provider</p>
                        <p class="mt-2 text-sm font-semibold text-zinc-950">{{ $booking->provider?->name ?? 'Provider' }}</p>
                        <p class="mt-1 text-sm text-zinc-500">{{ $booking->provider?->email ?? 'Contact details unavailable' }}</p>
                    </div>
                    <div class="rounded-[1.35rem] border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Scheduled</p>
                        <p class="mt-2 text-sm font-semibold text-zinc-950">{{ $booking->scheduled_at?->format('d M Y') }}</p>
                        <p class="mt-1 text-sm text-zinc-500">{{ $booking->scheduled_at?->format('h:i A') }}</p>
                    </div>
                    <div class="rounded-[1.35rem] border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Variant</p>
                        <p class="mt-2 text-sm font-semibold text-zinc-950">{{ $booking->serviceVariant?->name ?? 'Standard service' }}</p>
                        <p class="mt-1 text-sm text-zinc-500">{{ $booking->service?->duration_minutes ?? 0 }} minutes</p>
                    </div>
                    <div class="rounded-[1.35rem] border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Starting price</p>
                        <p class="mt-2 text-sm font-semibold text-zinc-950">Rs. {{ number_format((float) $price, 0) }}</p>
                        <p class="mt-1 text-sm text-zinc-500">{{ $location !== '' ? $location : 'Location not listed' }}</p>
                    </div>
                </div>

                @if($booking->service?->description)
                    <div class="mt-5 rounded-[1.35rem] border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Service summary</p>
                        <p class="mt-2 text-sm leading-7 text-zinc-600">{{ \Illuminate\Support\Str::limit($booking->service->description, 220) }}</p>
                    </div>
                @endif
            </section>

            @if($review)
                <section class="rounded-[2rem] border border-emerald-200 bg-emerald-50 p-6 shadow-[0_20px_60px_-45px_rgba(15,23,42,0.22)]" data-motion-card>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Existing Feedback</p>
                    <div class="mt-3 flex items-center gap-2 text-amber-400">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="{{ $i <= (int) $review->rating ? 'opacity-100' : 'opacity-25' }}">&#9733;</span>
                        @endfor
                    </div>
                    @if($review->title)
                        <h3 class="mt-4 text-lg font-semibold text-zinc-950">{{ $review->title }}</h3>
                    @endif
                    <p class="mt-3 text-sm leading-7 text-zinc-600">
                        {{ $review->comment ?: 'A rating was already saved for this booking.' }}
                    </p>
                    <p class="mt-4 text-xs text-zinc-500">Last updated {{ $review->updated_at?->format('d M Y, h:i A') }}</p>
                </section>
            @endif
        </aside>

        <section class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] lg:p-8" data-motion-card>
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-400">Review Form</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">{{ $review ? 'Refine your rating' : 'Tell us how it went' }}</h2>
                <p class="mt-2 text-sm leading-7 text-zinc-500">Ratings update the service score and help future customers compare providers with more confidence.</p>
            </div>

            <form method="POST" action="{{ route('customer.feedback.update', $booking) }}" class="mt-8 space-y-7">
                @csrf
                @method('PUT')

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <label class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Rating</label>
                        @error('rating')
                            <span class="text-xs font-semibold text-rose-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mt-3 grid grid-cols-5 gap-3">
                        @for($rating = 1; $rating <= 5; $rating++)
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="{{ $rating }}" class="peer sr-only" @checked($selectedRating === $rating)>
                                <span class="flex h-[4.5rem] flex-col items-center justify-center rounded-[1.35rem] border border-zinc-200 bg-zinc-50 text-zinc-500 transition peer-checked:border-amber-300 peer-checked:bg-amber-50 peer-checked:text-amber-600 hover:border-zinc-300 hover:text-zinc-700">
                                    <span class="text-xl leading-none">&#9733;</span>
                                    <span class="mt-2 text-sm font-semibold">{{ $rating }}</span>
                                </span>
                            </label>
                        @endfor
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-zinc-500">
                        <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1">1 = Needs work</span>
                        <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1">3 = Good</span>
                        <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1">5 = Excellent</span>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <label for="feedback-title" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Headline</label>
                        @error('title')
                            <span class="text-xs font-semibold text-rose-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <input
                        id="feedback-title"
                        type="text"
                        name="title"
                        value="{{ old('title', $review?->title) }}"
                        maxlength="120"
                        placeholder="Summarize the experience in one line"
                        class="mt-3 h-[3.25rem] w-full rounded-[1.35rem] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-400 focus:bg-white"
                    >
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <label for="feedback-comment" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Written feedback</label>
                        @error('comment')
                            <span class="text-xs font-semibold text-rose-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <textarea
                        id="feedback-comment"
                        name="comment"
                        rows="7"
                        maxlength="1500"
                        placeholder="What stood out about the service, professionalism, punctuality, quality, or overall experience?"
                        class="mt-3 w-full rounded-[1.35rem] border border-zinc-200 bg-zinc-50 px-4 py-4 text-sm leading-7 text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-400 focus:bg-white"
                    >{{ old('comment', $review?->comment) }}</textarea>
                </div>

                <div class="rounded-[1.35rem] border border-zinc-200 bg-zinc-50 px-4 py-4 text-sm leading-7 text-zinc-600">
                    Your feedback is tied to booking <span class="font-semibold text-zinc-950">{{ $booking->booking_number }}</span> and helps keep service ratings trustworthy and useful.
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex min-w-[170px] items-center justify-center rounded-xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">
                        {{ $review ? 'Update Feedback' : 'Submit Feedback' }}
                    </button>
                    <a href="{{ route('customer.feedback.index') }}" class="inline-flex min-w-[170px] items-center justify-center rounded-xl border border-zinc-200 bg-white px-5 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-100">
                        Cancel
                    </a>
                </div>
            </form>
        </section>
    </div>
</section>
@endsection
