@extends('layouts.customer')

@section('content')
<section class="mx-auto max-w-[1280px] px-4 pb-8 pt-10 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[36px] bg-white px-6 py-8 shadow-[0_20px_60px_rgba(15,23,42,0.08)] ring-1 ring-black/5 sm:px-8 lg:px-10 lg:py-10">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.45fr)_minmax(18rem,0.9fr)] lg:items-end">
            <div class="max-w-3xl">
                <p class="text-sm font-medium uppercase tracking-[0.22em] text-zinc-400">Liked Services</p>
                <h1 class="mt-4 text-[2.65rem] font-semibold leading-[1.08] tracking-[-0.05em] text-zinc-950 sm:text-[3.5rem]">
                    Your saved services, ready whenever you are
                </h1>
                <p class="mt-5 max-w-2xl text-[15px] leading-8 text-zinc-500">
                    Keep your favorite services in one place so you can come back later, compare options, and book faster.
                </p>
            </div>

            <div class="rounded-[24px] bg-zinc-50 px-5 py-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Saved Items</p>
                <p class="mt-3 text-[2.2rem] font-semibold tracking-[-0.04em] text-zinc-950">{{ $likedCount }}</p>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-[1280px] px-4 pb-20 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">Collection</p>
            <h2 class="mt-3 text-[2.25rem] font-semibold tracking-[-0.04em] text-zinc-950">All your liked services</h2>
        </div>
        <a href="{{ route('site.services.index') }}" class="inline-flex min-w-[170px] items-center justify-center rounded-[12px] border border-zinc-300 px-4 py-3 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
            Explore More Services
        </a>
    </div>

    <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($services as $service)
            @php
                $providerName = $service->providerProfile?->user?->name ?? 'Service Provider';
                $serviceLocation = $service->branch?->city
                    ? trim(($service->branch->city ?? '').', '.($service->branch->state ?? ''))
                    : 'Multiple locations';
                $serviceRating = round((float) ($service->avg_rating ?? 0), 1);
                $bookingQuery = array_filter([
                    'provider_id' => $service->providerProfile?->user_id,
                    'service_id' => $service->id,
                    'branch_id' => $service->branch_id,
                ]);
            @endphp

            <article class="overflow-hidden rounded-[28px] bg-white shadow-[0_18px_50px_rgba(15,23,42,0.08)] ring-1 ring-black/5">
                <a href="{{ route('site.services.show', $service->slug) }}" class="block">
                    <img
                        src="{{ $service->ui_image ?? 'https://picsum.photos/seed/'.urlencode((string) $service->id).'/900/620' }}"
                        alt="{{ $service->name }}"
                        class="h-56 w-full object-cover"
                    >
                </a>

                <div class="space-y-5 px-6 py-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">{{ $service->category?->name ?? 'Service' }}</p>
                            <h3 class="mt-2 text-[1.6rem] font-medium tracking-[-0.03em] text-zinc-950">{{ \Illuminate\Support\Str::limit($service->name, 30) }}</h3>
                            <p class="mt-2 text-sm text-zinc-500">{{ $providerName }} - {{ $serviceLocation }}</p>
                        </div>
                        <x-favorite-button :service="$service" size="small" />
                    </div>

                    <p class="text-[15px] leading-7 text-zinc-500">
                        {{ \Illuminate\Support\Str::limit($service->description ?? 'A saved service ready for your next booking.', 110) }}
                    </p>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-[16px] bg-zinc-50 px-3 py-3 text-center">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-400">Rating</p>
                            <p class="mt-2 text-lg font-semibold text-zinc-950">{{ $serviceRating > 0 ? number_format($serviceRating, 1) : 'New' }}</p>
                        </div>
                        <div class="rounded-[16px] bg-zinc-50 px-3 py-3 text-center">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-400">Duration</p>
                            <p class="mt-2 text-lg font-semibold text-zinc-950">{{ (int) $service->duration_minutes }}m</p>
                        </div>
                        <div class="rounded-[16px] bg-zinc-50 px-3 py-3 text-center">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-400">Price</p>
                            <p class="mt-2 text-lg font-semibold text-zinc-950">Rs. {{ number_format((float) $service->base_price, 0) }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('site.services.show', $service->slug) }}" class="inline-flex min-w-[140px] flex-1 items-center justify-center rounded-[12px] border border-zinc-300 px-4 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
                            View Details
                        </a>
                        <a href="{{ route('site.booking', $bookingQuery) }}" class="inline-flex min-w-[140px] flex-1 items-center justify-center rounded-[12px] bg-zinc-950 px-4 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                            Book Now
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-[32px] border border-dashed border-zinc-300 bg-white px-8 py-14 text-center shadow-[0_18px_50px_rgba(15,23,42,0.04)]">
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">No Likes Yet</p>
                <h3 class="mt-4 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-950">You have not saved any services</h3>
                <p class="mx-auto mt-4 max-w-2xl text-[15px] leading-7 text-zinc-500">
                    Tap the heart icon on any service card to add it here. Your liked collection will stay ready for future browsing and booking.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('site.services.index') }}" class="inline-flex min-w-[160px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                        Browse Services
                    </a>
                    <a href="{{ route('site.home') }}" class="inline-flex min-w-[160px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
                        Back to Home
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</section>
@endsection
