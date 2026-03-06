@props(['service'])

@php
    $image = $service->ui_image ?? 'https://picsum.photos/seed/'.urlencode((string) ($service->id ?? $service->name)).'/900/620';
    $rating = (float) ($service->avg_rating ?? 0);
    $reviewsCount = (int) ($service->reviews_count ?? 0);
    $serviceUrl = route('site.services.show', $service->slug);
@endphp

<article class="group customer-surface overflow-hidden transition duration-200 hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-200/70">
    <a href="{{ $serviceUrl }}" class="block">
        <div class="relative aspect-[4/3] overflow-hidden">
            <img src="{{ $image }}" alt="{{ $service->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-sky-900/20 via-sky-900/5 to-transparent"></div>
        </div>
    </a>

    <div class="space-y-4 p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-sky-500">{{ $service->category?->name ?? 'Service' }}</p>
                <h3 class="mt-1 text-lg font-bold text-sky-950">
                    <a href="{{ $serviceUrl }}" class="hover:text-sky-700">{{ $service->name }}</a>
                </h3>
            </div>
            <span class="customer-chip">
                {{ number_format((float) ($service->base_price ?? 0), 2) }}
            </span>
        </div>

        <div class="flex items-center justify-between text-sm text-sky-700">
            <p>{{ (int) ($service->duration_minutes ?? 0) }} min</p>
            <p class="inline-flex items-center gap-1 font-semibold text-sky-900">
                <span>★</span>
                <span>{{ $rating > 0 ? number_format($rating, 1) : 'New' }}</span>
                <span class="font-normal text-sky-600">({{ $reviewsCount }})</span>
            </p>
        </div>

        <p class="line-clamp-2 text-sm leading-6 text-sky-700">
            {{ $service->description ?: 'Professional service delivered with verified quality and flexible booking slots.' }}
        </p>

        <div class="flex items-center justify-between gap-3 border-t border-sky-100 pt-4">
            <p class="truncate text-sm text-sky-600">
                {{ $service->branch?->city ? $service->branch->city.', '.$service->branch->state : 'Multiple locations' }}
            </p>
            <a href="{{ $serviceUrl }}" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-500">
                View Details
            </a>
        </div>
    </div>
</article>
