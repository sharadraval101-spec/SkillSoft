@extends('layouts.customer')

@php
    $activeFilterCount = collect([
        $filters['q'] ?? '',
        ($filters['service_scope'] ?? 'any') !== 'any' ? $filters['service_scope'] : '',
        ($filters['sort'] ?? 'featured') !== 'featured' ? $filters['sort'] : '',
    ])->filter(fn ($value) => filled($value))->count();

    $categoryFallbackImages = [
        'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80',
    ];
@endphp

@section('content')
<section class="mx-auto max-w-[1280px] px-4 pb-8 pt-10 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[36px] bg-white px-6 py-8 shadow-[0_20px_60px_rgba(15,23,42,0.08)] ring-1 ring-black/5 sm:px-8 lg:px-10 lg:py-10">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(19rem,0.92fr)] lg:items-end">
            <div class="max-w-3xl">
                <p class="text-sm font-medium uppercase tracking-[0.22em] text-zinc-400">Categories Directory</p>
                <h1 class="mt-4 text-[2.65rem] font-semibold leading-[1.08] tracking-[-0.05em] text-zinc-950 sm:text-[3.6rem]">
                    Explore every service category from one dedicated page
                </h1>
                <p class="mt-5 max-w-2xl text-[15px] leading-8 text-zinc-500">
                    Browse all categories, preview the services inside each one, and use the filter drawer to quickly narrow down what you want to explore next.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                @foreach($heroStats as $stat)
                    <div class="rounded-[24px] bg-zinc-50 px-5 py-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">{{ $stat['label'] }}</p>
                        <p class="mt-3 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-950">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-[1280px] px-4 pb-20 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">All Categories</p>
            <h2 class="mt-3 text-[2.25rem] font-semibold tracking-[-0.04em] text-zinc-950">Browse all categories and their services</h2>
            <p class="mt-3 max-w-2xl text-[15px] leading-7 text-zinc-500">
                {{ $categories->count() }} {{ \Illuminate\Support\Str::plural('category', $categories->count()) }} matched your current category filters.
            </p>
        </div>

        <button id="categoryDrawerOpen" type="button" class="inline-flex min-w-[170px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
            Filter &amp; Sort
            @if($activeFilterCount > 0)
                <span class="ml-3 inline-flex min-h-[1.4rem] min-w-[1.4rem] items-center justify-center rounded-full bg-zinc-950 px-1 text-[11px] font-semibold text-white">
                    {{ $activeFilterCount }}
                </span>
            @endif
        </button>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        @forelse($categories as $index => $category)
            @php
                $categoryImage = $category->image_url
                    ?? $category->preview_services->first()?->ui_image
                    ?? $categoryFallbackImages[$index % count($categoryFallbackImages)];
            @endphp

            <article class="overflow-hidden rounded-[30px] bg-white shadow-[0_18px_50px_rgba(15,23,42,0.08)] ring-1 ring-black/5">
                <div class="grid gap-0 md:grid-cols-[15rem_minmax(0,1fr)]">
                    <img src="{{ $categoryImage }}" alt="{{ $category->name }}" class="h-60 w-full object-cover md:h-full">

                    <div class="space-y-6 px-6 py-6">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">
                                {{ (int) $category->active_services_count }} {{ \Illuminate\Support\Str::plural('service', (int) $category->active_services_count) }}
                            </span>
                            <span class="inline-flex rounded-full border border-zinc-200 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">
                                {{ $category->slug }}
                            </span>
                        </div>

                        <div>
                            <h3 class="text-[2rem] font-semibold tracking-[-0.04em] text-zinc-950">{{ $category->name }}</h3>
                            <p class="mt-3 text-[15px] leading-7 text-zinc-500">
                                {{ \Illuminate\Support\Str::limit($category->description ?: 'Discover trusted services inside this category and compare providers before booking.', 130) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Category Services</p>
                            <div class="mt-4 space-y-3">
                                @forelse($category->preview_services as $service)
                                    <div class="flex items-center gap-3 rounded-[18px] bg-zinc-50 px-4 py-3">
                                        <img
                                            src="{{ $service->ui_image ?? 'https://picsum.photos/seed/'.urlencode((string) $service->id).'/200/200' }}"
                                            alt="{{ $service->name }}"
                                            class="h-14 w-14 rounded-[14px] object-cover"
                                        >
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-zinc-900">{{ $service->name }}</p>
                                            <p class="truncate text-sm text-zinc-500">{{ $service->providerProfile?->user?->name ?? 'Provider' }}</p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-semibold text-zinc-900">Rs. {{ number_format((float) $service->base_price, 0) }}</span>
                                            <x-favorite-button :service="$service" size="small" />
                                        </div>
                                    </div>
                                @empty
                                    <p class="rounded-[18px] border border-dashed border-zinc-300 px-4 py-4 text-sm text-zinc-500">
                                        No active services are available in this category yet.
                                    </p>
                                @endforelse
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('site.services.index', ['category' => $category->slug]) }}" class="inline-flex min-w-[160px] items-center justify-center rounded-[12px] bg-zinc-950 px-4 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                                Explore Services
                            </a>
                            <a href="{{ route('site.booking') }}" class="inline-flex min-w-[160px] items-center justify-center rounded-[12px] border border-zinc-300 px-4 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
                                Start Booking
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-[32px] border border-dashed border-zinc-300 bg-white px-8 py-14 text-center shadow-[0_18px_50px_rgba(15,23,42,0.04)]">
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">No Categories</p>
                <h3 class="mt-4 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-950">No categories match your current filters</h3>
                <p class="mx-auto mt-4 max-w-2xl text-[15px] leading-7 text-zinc-500">
                    Try clearing the search or changing the filter drawer options to explore more categories.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('site.categories.index') }}" class="inline-flex min-w-[160px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                        Reset Filters
                    </a>
                    <a href="{{ route('site.home') }}" class="inline-flex min-w-[160px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
                        Back to Home
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</section>

<div id="categoryDrawerOverlay" class="pointer-events-none fixed inset-0 z-50 bg-black/35 opacity-0 transition duration-300"></div>

<aside id="categoryFilterDrawer" class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md translate-x-full flex-col bg-white shadow-2xl transition duration-300">
    <div class="flex items-center justify-between border-b border-black/5 px-6 py-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Filter Drawer</p>
            <h3 class="mt-2 text-xl font-semibold text-zinc-950">Filter &amp; sort categories</h3>
        </div>
        <button id="categoryDrawerClose" type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-zinc-200 text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" aria-label="Close category filters">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/>
            </svg>
        </button>
    </div>

    <form method="GET" action="{{ route('site.categories.index') }}" class="flex flex-1 flex-col overflow-y-auto px-6 py-6">
        <div class="space-y-6">
            <div>
                <label for="category-search" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Category Search</label>
                <input id="category-search" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search category name" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
            </div>

            <div>
                <label for="category-scope" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Service Coverage</label>
                <select id="category-scope" name="service_scope" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                    @foreach($serviceScopeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['service_scope'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="category-sort" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Sorting</label>
                <select id="category-sort" name="sort" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                    @foreach($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-3 border-t border-black/5 pt-6">
            <button type="submit" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                Apply Filters
            </button>
            <a href="{{ route('site.categories.index') }}" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
                Reset
            </a>
        </div>
    </form>
</aside>
@endsection

@push('scripts')
<script>
    (() => {
        const openButton = document.getElementById('categoryDrawerOpen');
        const closeButton = document.getElementById('categoryDrawerClose');
        const drawer = document.getElementById('categoryFilterDrawer');
        const overlay = document.getElementById('categoryDrawerOverlay');

        if (!openButton || !closeButton || !drawer || !overlay) {
            return;
        }

        const openDrawer = () => {
            drawer.classList.remove('translate-x-full');
            overlay.classList.remove('pointer-events-none', 'opacity-0');
        };

        const closeDrawer = () => {
            drawer.classList.add('translate-x-full');
            overlay.classList.add('pointer-events-none', 'opacity-0');
        };

        openButton.addEventListener('click', openDrawer);
        closeButton.addEventListener('click', closeDrawer);
        overlay.addEventListener('click', closeDrawer);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDrawer();
            }
        });
    })();
</script>
@endpush
