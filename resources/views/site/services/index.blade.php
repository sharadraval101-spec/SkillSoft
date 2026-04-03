@extends('layouts.customer')

@php
    $activeFilterCount = collect([
        $filters['category'] ?? '',
        $filters['type'] ?? '',
        $filters['price_range'] ?? '',
        $filters['rating'] ?? '',
        $filters['availability'] ?? '',
        ($filters['sort'] ?? 'recommended') !== 'recommended' ? $filters['sort'] : '',
    ])->filter(fn ($value) => filled($value))->count();
@endphp

@section('content')
<section class="mx-auto max-w-[1280px] px-4 pb-8 pt-10 sm:px-6 lg:px-8" data-motion-section>
    <div class="overflow-hidden rounded-[36px] bg-white px-6 py-8 shadow-[0_20px_60px_rgba(15,23,42,0.08)] ring-1 ring-black/5 sm:px-8 lg:px-10 lg:py-10">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(20rem,0.95fr)] lg:items-end">
            <div class="max-w-3xl">
                <p class="text-sm font-medium uppercase tracking-[0.22em] text-zinc-400" data-motion-kicker>Service Marketplace</p>
                <h1 class="mt-4 text-[2.65rem] font-semibold leading-[1.08] tracking-[-0.05em] text-zinc-950 sm:text-[3.6rem]" data-motion-title>
                    Discover premium services built around your schedule
                </h1>
                <p class="mt-5 max-w-2xl text-[15px] leading-8 text-zinc-500" data-motion-copy>
                    Browse trusted providers, compare quality, filter by availability, and book the right service with a polished marketplace experience.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3" data-motion-group>
                @foreach($heroStats as $stat)
                    <div class="rounded-[24px] bg-zinc-50 px-5 py-5" data-motion-item data-motion-card>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">{{ $stat['label'] }}</p>
                        <p class="mt-3 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-950">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-[1280px] px-4 pb-20 sm:px-6 lg:px-8" data-motion-section>
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="max-w-3xl">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400" data-motion-kicker>Results</p>
            <h2 class="mt-3 text-[2.3rem] font-semibold tracking-[-0.04em] text-zinc-950" data-motion-title>Services ready to book</h2>
            <p class="mt-3 max-w-2xl text-[15px] leading-7 text-zinc-500" data-motion-copy>
                {{ number_format($resultCount) }} {{ \Illuminate\Support\Str::plural('service', $resultCount) }} matched your current filters.
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:items-end">
            <button type="button" class="inline-flex min-w-[170px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" data-motion-actions data-motion-action data-filter-open="services" aria-controls="servicesFilterDrawer">
                Filter &amp; Sort
                @if($activeFilterCount > 0)
                    <span class="ml-3 inline-flex min-h-[1.4rem] min-w-[1.4rem] items-center justify-center rounded-full bg-zinc-950 px-1 text-[11px] font-semibold text-white">
                        {{ $activeFilterCount }}
                    </span>
                @endif
            </button>

            @if($services->count())
                <p class="text-sm text-zinc-500">
                    Page {{ $services->currentPage() }} of {{ $services->lastPage() }}
                </p>
            @endif
        </div>
    </div>

    <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-motion-group>
        @forelse($services as $service)
            @php
                $providerName = $service->providerProfile?->user?->name ?? 'Service Provider';
                $serviceRating = round((float) ($service->avg_rating ?? 0), 1);
                $servicePrice = number_format((float) $service->base_price, 0);
                $serviceLocation = $service->branch?->city
                    ? trim(($service->branch->city ?? '').', '.($service->branch->state ?? ''))
                    : 'Multiple locations';
                $serviceTypeLabel = $service->type === 'group' ? 'Group' : '1-on-1';
                $bookingQuery = array_filter([
                    'provider_id' => $service->providerProfile?->user_id,
                    'service_id' => $service->id,
                    'branch_id' => $service->branch_id,
                ]);
            @endphp

            <article class="overflow-hidden rounded-[28px] bg-white shadow-[0_18px_50px_rgba(15,23,42,0.08)] ring-1 ring-black/5" data-motion-item data-motion-card>
                <a href="{{ route('site.services.show', $service->slug) }}" class="block">
                    <img
                        src="{{ $service->ui_image ?? 'https://picsum.photos/seed/'.urlencode((string) $service->id).'/900/620' }}"
                        alt="{{ $service->name }}"
                        class="h-56 w-full object-cover"
                    >
                </a>

                <div class="space-y-5 px-6 py-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">
                            {{ $service->category?->name ?? 'Service' }}
                        </span>
                        <span class="inline-flex rounded-full border border-zinc-200 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">
                            {{ $serviceTypeLabel }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-[1.7rem] font-medium tracking-[-0.03em] text-zinc-950">
                            {{ \Illuminate\Support\Str::limit($service->name, 34) }}
                        </h3>
                        <p class="mt-3 text-[15px] leading-7 text-zinc-500">
                            {{ \Illuminate\Support\Str::limit($service->description ?? 'Trusted provider with flexible scheduling and premium service quality.', 110) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4 rounded-[20px] bg-zinc-50 px-4 py-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-base font-semibold text-zinc-900 shadow-sm">
                            {{ strtoupper(substr($providerName, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-zinc-900">{{ $providerName }}</p>
                            <p class="truncate text-sm text-zinc-500">{{ $serviceLocation }}</p>
                        </div>
                    </div>

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
                            <p class="mt-2 text-lg font-semibold text-zinc-950">Rs. {{ $servicePrice }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm text-zinc-500">
                        <span>{{ (int) ($service->reviews_count ?? 0) }} {{ \Illuminate\Support\Str::plural('review', (int) ($service->reviews_count ?? 0)) }}</span>
                        <span>{{ $serviceTypeLabel }} booking</span>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('site.services.show', $service->slug) }}" class="inline-flex min-w-[140px] flex-1 items-center justify-center rounded-[12px] border border-zinc-300 px-4 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" data-motion-action>
                            View Details
                        </a>
                        <a href="{{ route('site.booking', $bookingQuery) }}" class="inline-flex min-w-[140px] flex-1 items-center justify-center rounded-[12px] bg-zinc-950 px-4 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800" data-motion-action>
                            Book Now
                        </a>
                        <x-favorite-button :service="$service" size="small" />
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-[32px] border border-dashed border-zinc-300 bg-white px-8 py-14 text-center shadow-[0_18px_50px_rgba(15,23,42,0.04)]" data-motion-card>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">No Results</p>
                <h3 class="mt-4 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-950">No services match your current filters</h3>
                <p class="mx-auto mt-4 max-w-2xl text-[15px] leading-7 text-zinc-500">
                    Try broadening the category, rating, or price range to discover more providers and open booking slots.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('site.services.index') }}" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800" data-motion-action>
                        Reset Filters
                    </a>
                    <a href="{{ route('site.home') }}" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" data-motion-action>
                        Back to Home
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    @if($services->hasPages())
        <div class="mt-12 rounded-[28px] bg-white px-4 py-5 shadow-[0_18px_50px_rgba(15,23,42,0.05)] ring-1 ring-black/5 sm:px-6" data-motion-card>
            {{ $services->onEachSide(1)->links() }}
        </div>
    @endif
</section>

<div class="pointer-events-none fixed inset-0 z-50 bg-black/35 opacity-0 transition duration-300" data-filter-overlay="services"></div>

<aside id="servicesFilterDrawer" class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md translate-x-full flex-col bg-white shadow-2xl transition duration-300" data-filter-drawer="services">
    <div class="flex items-center justify-between border-b border-black/5 px-6 py-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Filter Drawer</p>
            <h3 class="mt-2 text-xl font-semibold text-zinc-950">Refine your service search</h3>
        </div>
        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-zinc-200 text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" aria-label="Close service filters" data-motion-action data-filter-close="services">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/>
            </svg>
        </button>
    </div>

    <form method="GET" action="{{ route('site.services.index') }}" class="flex flex-1 flex-col overflow-y-auto px-6 py-6">
        <div class="space-y-6">
            <div>
                <label for="drawer-service-category" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Category</label>
                <select id="drawer-service-category" name="category" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="drawer-service-type" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Service Type</label>
                <select id="drawer-service-type" name="type" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                    <option value="">All types</option>
                    @foreach($serviceTypeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="drawer-service-price-range" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Price Range</label>
                <select id="drawer-service-price-range" name="price_range" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                    <option value="">Any price</option>
                    @foreach($priceRangeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['price_range'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="drawer-service-rating" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Rating</label>
                <select id="drawer-service-rating" name="rating" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                    <option value="">Any rating</option>
                    @foreach($ratingOptions as $value => $label)
                        <option value="{{ $value }}" @selected((string) $filters['rating'] === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="drawer-service-availability" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Availability</label>
                <input id="drawer-service-availability" type="date" name="availability" min="{{ now()->toDateString() }}" value="{{ $filters['availability'] }}" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
            </div>

            <div>
                <label for="drawer-service-sort" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Sort</label>
                <select id="drawer-service-sort" name="sort" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                    @foreach($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-3 border-t border-black/5 pt-6">
            <button type="submit" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800" data-motion-action>
                Apply Filters
            </button>
            <a href="{{ route('site.services.index') }}" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" data-motion-action>
                Reset
            </a>
        </div>
    </form>
</aside>
@endsection
