@extends('layouts.customer')

@php
    $topCategories = $categories->take(4)->values();
    $highlightedServices = $featuredServices->take(4)->values();
@endphp

@section('content')
<section class="mx-auto flex min-h-[calc(100vh-7rem)] max-w-[1280px] items-center  pb-16 pt-8 lg:pb-24" data-motion-section>
   <div class="grid w-full items-center gap-x-6 lg:grid-cols-[minmax(0,36rem)_minmax(0,1fr)]">

    <!-- LEFT -->
    <div class="max-w-[38rem] pt-6 lg:pt-0">
        <h1 class="text-[2.45rem] font-semibold leading-[1.18] tracking-[-0.05em] text-zinc-900 sm:text-[3.35rem] lg:text-[4.15rem]">
            Find and book trusted rgservices near you anytime anywhere
        </h1>

        <p class="mt-6 max-w-[35rem] text-[15px] leading-8 text-zinc-500">
            Explore professional services across beauty, fitness, healthcare, legal consultation, pet care, and many more categories.
            Find verified service providers, check availability in real time, select a convenient time slot, and book your appointment instantly with safe and secure payment options.
        </p>

        <div class="mt-10 flex flex-wrap items-center gap-4">
            <a href="{{ route('site.services.index') }}"
               class="inline-flex min-w-[150px] items-center justify-center rounded-[10px] bg-zinc-950 px-7 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                Explore Services
            </a>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="relative flex justify-center lg:justify-end">
        <div class="relative w-full max-w-[39rem]">

            <div class="absolute right-[10%] top-[12%] h-56 w-56 rounded-full bg-orange-100/75 blur-3xl"></div>
            <div class="absolute inset-x-[14%] bottom-4 h-20 rounded-full bg-black/15 blur-3xl"></div>
            <div class="absolute inset-x-[18%] bottom-0 h-28 bg-gradient-to-t from-white via-white/85 to-transparent"></div>

            <img
                src="{{ asset('images/shararad.png') }}"
                alt="Happy customer exploring services on a phone"
                class="relative z-10 ml-auto h-[24rem] w-auto max-w-none object-contain sm:h-[31rem] lg:h-[39rem] [mask-image:linear-gradient(to_bottom,black_82%,transparent_100%)]"
            >
        </div>
    </div>

</div>
</section>

<section id="categories" class="mx-auto max-w-[1280px] scroll-mt-24 px-4 pb-24 pt-10 sm:px-6 lg:px-8" data-motion-section>
    <div class="mx-auto max-w-2xl text-center">
        <h2 class="text-[2.4rem] font-semibold tracking-[-0.04em] text-zinc-900 sm:text-[2.8rem]" data-motion-title>
            Explore Services by Category
        </h2>
        <p class="mt-4 text-[15px] leading-7 text-zinc-500" data-motion-copy>
            Find the perfect service tailored to your needs. Browse by category and book instantly.
        </p>
    </div>

    <div class="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-4" data-motion-group>
        @forelse($topCategories as $category)
            @php
                $categoryImage = $category->image_url;
                $categoryInitials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($category->name ?? 'Category', 0, 2));
            @endphp

            <article class="overflow-hidden rounded-[28px] bg-white shadow-[0_16px_40px_rgba(0,0,0,0.07)] ring-1 ring-black/5" data-motion-item data-motion-card>
                @if($categoryImage)
                    <img src="{{ $categoryImage }}" alt="{{ $category->name }}" class="h-52 w-full object-cover">
                @else
                    <div class="flex h-52 w-full items-center justify-center bg-gradient-to-br from-zinc-100 via-white to-zinc-200">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white text-2xl font-semibold text-zinc-900 shadow-sm">
                            {{ $categoryInitials }}
                        </div>
                    </div>
                @endif

                <div class="space-y-4 px-6 py-5">
                    <div>
                        <h3 class="text-[1.7rem] font-medium tracking-[-0.03em] text-zinc-900">{{ $category->name }}</h3>
                        @if(filled($category->description))
                            <p class="mt-3 text-[15px] leading-7 text-zinc-500">{{ \Illuminate\Support\Str::limit($category->description, 110) }}</p>
                        @endif
                    </div>

                    <a href="{{ route('site.services.index', ['category' => $category->slug]) }}" class="inline-flex w-full items-center justify-center rounded-[10px] bg-zinc-950 px-4 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800" data-motion-action>
                        View Services
                    </a>
                </div>
            </article>
        @empty
            <p class="rounded-[28px] border border-dashed border-zinc-300 px-6 py-10 text-center text-sm text-zinc-500 md:col-span-2 xl:col-span-4" data-motion-card>
                No categories available right now.
            </p>
        @endforelse
    </div>
</section>

<div id="how-it-works" class="scroll-mt-24"></div>

<section class="mx-auto max-w-[1280px] px-4 pb-20 pt-4 sm:px-6 lg:px-8" data-motion-section>
    <div class="max-w-2xl">
        <h2 class="text-[2.35rem] font-semibold tracking-[-0.04em] text-zinc-900 sm:text-[2.8rem]" data-motion-title>
            Popular Services Near You
        </h2>
        <p class="mt-4 text-[15px] leading-7 text-zinc-500" data-motion-copy>
            Discover highly rated services trusted by customers in your area. Book top professionals quickly and easily.
        </p>
    </div>

    <div class="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-4" data-motion-group>
        @forelse($highlightedServices as $service)
            @php
                $providerName = $service->providerProfile?->user?->name ?? 'Service Provider';
                $serviceRating = round((float) ($service->avg_rating ?? 0), 1);
                $servicePrice = number_format((float) $service->base_price, 0);
                $serviceImage = $service->ui_image ?? $service->image_url;
                $serviceInitials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($service->name ?? 'Service', 0, 2));
                $bookingQuery = array_filter([
                    'provider_id' => $service->providerProfile?->user_id,
                    'service_id' => $service->id,
                    'branch_id' => $service->branch_id,
                ]);
            @endphp

            <article class="overflow-hidden rounded-[28px] bg-white shadow-[0_16px_40px_rgba(0,0,0,0.07)] ring-1 ring-black/5" data-motion-item data-motion-card>
                @if($serviceImage)
                    <img
                        src="{{ $serviceImage }}"
                        alt="{{ $service->name }}"
                        class="h-52 w-full object-cover"
                    >
                @else
                    <div class="flex h-52 w-full items-center justify-center bg-gradient-to-br from-zinc-100 via-white to-zinc-200">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white text-2xl font-semibold text-zinc-900 shadow-sm">
                            {{ $serviceInitials }}
                        </div>
                    </div>
                @endif

                <div class="space-y-5 px-6 py-5">
                    <div>
                        <h3 class="text-[1.55rem] font-medium tracking-[-0.03em] text-zinc-900">
                            {{ \Illuminate\Support\Str::limit($service->name, 24) }}
                        </h3>
                        <p class="mt-1 text-[15px] text-zinc-500">{{ $providerName }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-[14px] bg-zinc-50 px-4 py-3 text-center">
                            <p class="text-xs font-medium uppercase tracking-[0.16em] text-zinc-400">Rating</p>
                            <p class="mt-2 text-[1.55rem] font-semibold text-zinc-900">{{ $serviceRating > 0 ? number_format($serviceRating, 1) : 'New' }}</p>
                        </div>
                        <div class="rounded-[14px] bg-zinc-50 px-4 py-3 text-center">
                            <p class="text-xs font-medium uppercase tracking-[0.16em] text-zinc-400">Starting</p>
                            <p class="mt-2 text-[1.55rem] font-semibold text-zinc-900">&#8377;{{ $servicePrice }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('site.booking', $bookingQuery) }}" class="inline-flex flex-1 items-center justify-center rounded-[10px] bg-zinc-950 px-4 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800" data-motion-action>
                            Book Now
                        </a>
                        <x-favorite-button :service="$service" />
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-[28px] border border-dashed border-zinc-300 px-6 py-10 text-center text-sm text-zinc-500 md:col-span-2 xl:col-span-4" data-motion-card>
                No popular services available right now.
            </p>
        @endforelse
    </div>
</section>
@endsection
