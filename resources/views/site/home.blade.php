@extends('layouts.customer')

@section('content')
<section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
    <div class="relative overflow-hidden rounded-3xl border border-sky-100">
        <img src="https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&w=1800&q=80" alt="Modern interior" class="h-[28rem] w-full object-cover sm:h-[34rem]">
        <div class="absolute inset-0 bg-gradient-to-r from-sky-950/75 via-sky-900/45 to-sky-950/25"></div>

        <div class="absolute inset-x-0 top-1/2 mx-auto w-full max-w-4xl -translate-y-1/2 px-4">
            <div class="text-center">
                <p class="inline-flex rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-sky-100">
                    Premium Service Marketplace
                </p>
                <h1 class="mt-4 text-4xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Search Modern Services Near You
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-sky-100 sm:text-base">
                    Thousands of verified professionals are ready for home service, in-shop appointments, and same-day slots.
                </p>
            </div>

            <form method="GET" action="{{ route('site.services.index') }}" class="mt-7 rounded-2xl border border-white/25 bg-white/95 p-3 shadow-2xl shadow-sky-900/20 backdrop-blur-sm">
                <div class="grid gap-2 md:grid-cols-[11rem,1fr,12rem,9rem]">
                    <div>
                        <label for="home-service-type" class="sr-only">Service Type</label>
                        <select id="home-service-type" name="type" class="h-11 w-full rounded-xl border border-sky-200 px-3 text-sm font-semibold text-sky-800 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                            <option value="">For All</option>
                            <option value="home">At Home</option>
                            <option value="branch">At Branch</option>
                        </select>
                    </div>
                    <div>
                        <label for="home-q" class="sr-only">Search</label>
                        <input id="home-q" type="text" name="q" placeholder="Service name, provider, or skill"
                            class="h-11 w-full rounded-xl border border-sky-200 px-3 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    </div>
                    <div>
                        <label for="home-location" class="sr-only">Location</label>
                        <input id="home-location" type="text" name="location" placeholder="Location"
                            class="h-11 w-full rounded-xl border border-sky-200 px-3 text-sm text-sky-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    </div>
                    <button type="submit" class="h-11 rounded-xl bg-sky-600 px-4 text-sm font-semibold text-white transition hover:bg-sky-500">
                        Search
                    </button>
                </div>
            </form>

            <div class="mt-5 flex flex-wrap justify-center gap-2">
                @foreach($categories->take(4) as $category)
                    <a href="{{ route('site.services.index', ['category' => $category->slug]) }}" class="rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-semibold text-white hover:bg-white/20">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center">
        <p class="text-xs font-bold uppercase tracking-[0.14em] text-sky-600">Try Searching For</p>
        <h2 class="mt-3 text-3xl font-black tracking-tight text-sky-950 sm:text-4xl">Popular Categories</h2>
        <p class="mx-auto mt-3 max-w-2xl text-sm text-sky-700">Start with the most booked service groups and compare top providers.</p>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
        @forelse($categories as $category)
            <a href="{{ route('site.services.index', ['category' => $category->slug]) }}" class="group rounded-2xl border border-sky-100 bg-white p-5 text-center shadow-sm shadow-sky-100/70 transition hover:-translate-y-1 hover:shadow-lg hover:shadow-sky-200/60">
                <span class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-sky-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h12M4 17h8"/>
                    </svg>
                </span>
                <h3 class="mt-4 text-sm font-bold text-sky-900 group-hover:text-sky-700">{{ $category->name }}</h3>
                <p class="mt-1 text-xs text-sky-600">{{ (int) ($category->services_count ?? 0) }} services</p>
            </a>
        @empty
            <p class="col-span-full rounded-2xl border border-dashed border-sky-200 py-8 text-center text-sm text-sky-700">No categories available right now.</p>
        @endforelse
    </div>
</section>

<section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center">
        <p class="text-xs font-bold uppercase tracking-[0.14em] text-sky-600">Today Listings</p>
        <h2 class="mt-3 text-3xl font-black tracking-tight text-sky-950 sm:text-4xl">Today's Luxury Listings</h2>
        <p class="mx-auto mt-3 max-w-2xl text-sm text-sky-700">Premium providers selected by rating, reliability, and booking demand.</p>
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse($featuredServices as $service)
            <article class="overflow-hidden rounded-2xl border border-sky-100 bg-white shadow-sm shadow-sky-100/70 transition hover:-translate-y-1 hover:shadow-lg hover:shadow-sky-200/60">
                <div class="relative">
                    <img src="{{ $service->ui_image ?? 'https://picsum.photos/seed/'.urlencode((string) $service->id).'/900/620' }}" alt="{{ $service->name }}" class="h-48 w-full object-cover">
                    <div class="absolute left-3 top-3 flex gap-2">
                        <span class="rounded-full bg-amber-500 px-2.5 py-1 text-[11px] font-bold text-white">Featured</span>
                        <span class="rounded-full bg-sky-900/75 px-2.5 py-1 text-[11px] font-semibold text-white">Top Rated</span>
                    </div>
                </div>
                <div class="p-5">
                    <h3 class="text-lg font-bold text-sky-950">{{ $service->name }}</h3>
                    <p class="mt-1 text-sm text-sky-700">{{ $service->branch?->city ? $service->branch->city.', '.$service->branch->state : 'Multiple locations' }}</p>
                    <div class="mt-4 grid grid-cols-3 gap-2 rounded-xl bg-sky-50 p-3 text-xs text-sky-700">
                        <div>
                            <p class="font-bold text-sky-900">{{ (int) $service->duration_minutes }}m</p>
                            <p>Duration</p>
                        </div>
                        <div>
                            <p class="font-bold text-sky-900">{{ number_format((float) ($service->avg_rating ?? 0), 1) }}</p>
                            <p>Rating</p>
                        </div>
                        <div>
                            <p class="font-bold text-sky-900">{{ (int) ($service->reviews_count ?? 0) }}</p>
                            <p>Reviews</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-lg font-black text-sky-900">INR {{ number_format((float) $service->base_price, 2) }}</p>
                        <a href="{{ route('site.services.show', $service->slug) }}" class="rounded-lg border border-sky-200 px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-50">
                            Details
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <p class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-sky-200 py-10 text-center text-sm text-sky-700">
                No featured services found.
            </p>
        @endforelse
    </div>
</section>

<section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="rounded-3xl border border-sky-100 bg-gradient-to-b from-sky-50 to-white p-6 sm:p-8">
        <div class="text-center">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-sky-600">Support Hub</p>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-sky-950 sm:text-4xl">Discover How We Can Help</h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-sky-700">Find the right package, plan your monthly budget, and get help for urgent bookings.</p>
            <div class="mt-5 inline-flex rounded-full border border-sky-200 bg-white p-1">
                <span class="rounded-full bg-sky-600 px-4 py-1 text-xs font-semibold text-white">Booking</span>
                <span class="px-4 py-1 text-xs font-semibold text-sky-700">Pricing</span>
                <span class="px-4 py-1 text-xs font-semibold text-sky-700">Support</span>
            </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-sky-100 bg-white p-5 text-center">
                <h3 class="text-lg font-bold text-sky-900">Find Service Cost</h3>
                <p class="mt-2 text-sm text-sky-700">Get price ranges from verified providers before you book.</p>
            </div>
            <div class="rounded-2xl border border-sky-100 bg-white p-5 text-center">
                <h3 class="text-lg font-bold text-sky-900">Plan Monthly Slots</h3>
                <p class="mt-2 text-sm text-sky-700">Schedule recurring appointments and avoid last-minute rush.</p>
            </div>
            <div class="rounded-2xl border border-sky-100 bg-white p-5 text-center">
                <h3 class="text-lg font-bold text-sky-900">Quick Assistance</h3>
                <p class="mt-2 text-sm text-sky-700">Get direct help for booking changes, refunds, and support cases.</p>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center">
        <p class="text-xs font-bold uppercase tracking-[0.14em] text-sky-600">Explore Areas</p>
        <h2 class="mt-3 text-3xl font-black tracking-tight text-sky-950 sm:text-4xl">Explore The Neighborhoods</h2>
    </div>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($locations->take(8) as $location)
            <a href="{{ route('site.services.index', ['location' => $location]) }}" class="group relative overflow-hidden rounded-2xl border border-sky-100">
                <img src="https://picsum.photos/seed/{{ urlencode($location) }}/720/480" alt="{{ $location }}" class="h-36 w-full object-cover transition duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-sky-950/70 via-sky-900/20 to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 rounded-lg bg-white/85 px-3 py-2 text-sm font-semibold text-sky-900 backdrop-blur-sm">
                    {{ $location }}
                </div>
            </a>
        @endforeach
    </div>
</section>

<section class="mx-auto mt-16 max-w-7xl px-4 pb-10 sm:px-6 lg:px-8">
    <div class="text-center">
        <p class="text-xs font-bold uppercase tracking-[0.14em] text-sky-600">Testimonials</p>
        <h2 class="mt-3 text-3xl font-black tracking-tight text-sky-950 sm:text-4xl">Client Testimonials</h2>
    </div>
    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($testimonials as $review)
            <x-review-card :review="$review" />
        @empty
            <p class="xl:col-span-3 rounded-2xl border border-dashed border-sky-200 py-10 text-center text-sm text-sky-700">
                Testimonials will appear once reviews are submitted.
            </p>
        @endforelse
    </div>
</section>
@endsection
