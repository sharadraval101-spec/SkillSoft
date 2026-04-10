@php
    $isCustomer = auth()->check() && (int) auth()->user()->role === \App\Models\User::ROLE_CUSTOMER;
    $homeHref = route('site.home');
    $servicesHref = route('site.services.index');
    $bookingHref = route('site.booking');
    $favoritesHref = route('site.favorites.index');
    $categoriesHref = route('site.categories.index');
    $howItWorksHref = request()->routeIs('site.home') ? '#how-it-works' : route('site.home') . '#how-it-works';
    $becomeProviderHref = route('register.provider');
    $isBookingActive = request()->routeIs('site.booking') || request()->routeIs('customer.bookings.*');
    $isServicesActive = request()->routeIs('site.services.*');
    $isCategoriesActive = request()->routeIs('site.categories.*');
    $isFavoritesActive = request()->routeIs('site.favorites.*');
    $isNotificationsActive = request()->routeIs('notifications.*');
    $likedCount = \App\Support\SiteFavorites::count();
    $unreadNotificationCount = auth()->check()
        ? \App\Models\Notification::query()
            ->where('user_id', auth()->id())
            ->unread()
            ->count()
        : 0;
    $desktopNavLinkClasses = "relative inline-flex items-center pb-1 text-[15px] font-medium text-zinc-700 transition-colors duration-200 hover:text-zinc-950 after:pointer-events-none after:absolute after:-bottom-1 after:left-0 after:h-[2px] after:w-full after:origin-left after:scale-x-0 after:bg-zinc-950 after:transition-transform after:duration-300 after:ease-out after:content-[''] hover:after:scale-x-100 focus-visible:after:scale-x-100";
@endphp

<header class="sticky top-0 z-40 border-b border-white/40 bg-white/70 shadow-[0_10px_30px_-24px_rgba(15,23,42,0.35)] backdrop-blur-xl supports-[backdrop-filter]:bg-white/40" data-motion-header>
    <div class="mx-auto flex h-[72px] w-full max-w-[1280px] items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('site.home') }}" class="inline-flex shrink-0 items-center gap-3 text-zinc-900 leading-none" data-motion-brand data-motion-action>
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl">
                <svg viewBox="0 0 56 52" class="block h-9 w-9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M8 42V10l16 8 16-8v32l-16-8-16 8Z" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M24 16c0-3.866 3.134-7 7-7s7 3.134 7 7c0 5.044-7 11-7 11s-7-5.956-7-11Z" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="31" cy="16" r="2.5" fill="currentColor"/>
                </svg>
            </span>
            <span class="self-center text-lg font-semibold tracking-[-0.03em] leading-none">SkillSlot</span>
        </a>

        <nav class="hidden flex-1 items-center justify-center gap-10 lg:flex">
            <a href="{{ $homeHref }}" class="{{ $desktopNavLinkClasses }} {{ request()->routeIs('site.home') ? 'text-zinc-950 after:scale-x-100' : '' }}" data-motion-nav-item data-motion-action>
               Home
            </a>
            <a href="{{ $servicesHref }}" class="{{ $desktopNavLinkClasses }} {{ $isServicesActive ? 'text-zinc-950 after:scale-x-100' : '' }}" data-motion-nav-item data-motion-action>
                Services
            </a>
            <a href="{{ $categoriesHref }}" class="{{ $desktopNavLinkClasses }} {{ $isCategoriesActive ? 'text-zinc-950 after:scale-x-100' : '' }}" data-motion-nav-item data-motion-action>
                Categories
            </a>
            <a href="{{ $bookingHref }}" class="{{ $desktopNavLinkClasses }} {{ $isBookingActive ? 'text-zinc-950 after:scale-x-100' : '' }}" data-motion-nav-item data-motion-action>
                Booking
            </a>
        </nav>

        <div class="hidden items-center gap-4 lg:flex">
            {{-- <a href="{{ route('site.services.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-zinc-500 transition hover:bg-white/70 hover:text-zinc-950" aria-label="Search services">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                </svg>
            </a> --}}
            <a href="{{ $favoritesHref }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full bg-rose-50 text-rose-500 transition hover:bg-rose-100" aria-label="Liked services" data-motion-utility data-motion-action>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m12 21-1.45-1.32C5.4 15.01 2 11.93 2 8.15 2 5.07 4.42 2.7 7.5 2.7c1.74 0 3.41.81 4.5 2.09A6 6 0 0 1 16.5 2.7C19.58 2.7 22 5.07 22 8.15c0 3.78-3.4 6.86-8.55 11.54L12 21Z"/>
                </svg>
                <span class="absolute -right-1 -top-1 inline-flex min-h-[1.2rem] min-w-[1.2rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white" data-favorites-count>
                    {{ $likedCount }}
                </span>
            </a>
            @auth
                <a
                    href="{{ route('notifications.index') }}"
                    class="relative inline-flex h-10 w-10 items-center justify-center rounded-full transition {{ $isNotificationsActive ? 'bg-sky-100 text-sky-700' : 'bg-sky-50 text-sky-500 hover:bg-sky-100' }}"
                    aria-label="Notifications"
                    data-motion-utility
                    data-motion-action
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1" />
                    </svg>
                    @if($unreadNotificationCount > 0)
                        <span class="absolute -right-1 -top-1 inline-flex min-h-[1.2rem] min-w-[1.2rem] items-center justify-center rounded-full bg-sky-500 px-1 text-[10px] font-semibold text-white">
                            {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                        </span>
                    @endif
                </a>
            @endauth
            <span class="h-7 w-px bg-zinc-200"></span>

            @auth
                @if($isCustomer)
                    <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center rounded-lg bg-zinc-950 px-6 py-3 text-[15px] font-medium text-white transition hover:bg-zinc-800" data-motion-utility data-motion-action>
                        My Account
                    </a>
                @else
                    <a href="{{ route('profile.index') }}" class="inline-flex items-center rounded-lg bg-zinc-950 px-6 py-3 text-[15px] font-medium text-white transition hover:bg-zinc-800" data-motion-utility data-motion-action>
                        Profile
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-[15px] font-semibold text-zinc-950 transition hover:text-zinc-600" data-motion-utility data-motion-action>
                        Log out
                    </button>
                </form>
            @else
                <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-zinc-950 px-6 py-3 text-[15px] font-medium text-white transition hover:bg-zinc-800" data-motion-utility data-motion-action>
                    Sign up
                </a>
                <a href="{{ route('login') }}" class="text-[15px] font-semibold text-zinc-950 transition hover:text-zinc-600" data-motion-utility data-motion-action>
                    Log in
                </a>
            @endauth
        </div>

        <details class="group relative lg:hidden" data-motion-menu>
            <summary class="flex h-11 w-11 list-none items-center justify-center rounded-full border border-zinc-200 text-zinc-900 transition hover:border-zinc-300 hover:bg-zinc-50 [&::-webkit-details-marker]:hidden" data-motion-menu-trigger>
                <span class="sr-only">Open navigation menu</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </summary>

            <div class="absolute right-0 top-[calc(100%+0.75rem)] w-[min(21rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-white/60 bg-white/85 p-4 shadow-xl shadow-zinc-200/60 backdrop-blur-2xl supports-[backdrop-filter]:bg-white/75" data-motion-menu-panel>
                <nav class="space-y-1">
                    <a href="{{ $homeHref }}" class="block rounded-xl px-3 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-950" data-motion-action>
                        Home
                    </a>
                    <a href="{{ $bookingHref }}" class="block rounded-xl px-3 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-950" data-motion-action>
                        Booking
                    </a>
                    <a href="{{ $servicesHref }}" class="block rounded-xl px-3 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-950" data-motion-action>
                        Services
                    </a>
                    <a href="{{ $favoritesHref }}" class="flex items-center justify-between rounded-xl px-3 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-950" data-motion-action>
                        <span>Liked Services</span>
                        <span class="inline-flex min-h-[1.25rem] min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white" data-favorites-count>
                            {{ $likedCount }}
                        </span>
                    </a>
                    <a href="{{ $categoriesHref }}" class="block rounded-xl px-3 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-950" data-motion-action>
                        Categories
                    </a>
                    <a href="{{ $howItWorksHref }}" class="block rounded-xl px-3 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-950" data-motion-action>
                        How It Works
                    </a>
                    <a href="{{ $becomeProviderHref }}" class="block rounded-xl px-3 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-950" data-motion-action>
                        Become a Provider
                    </a>
                    <a href="{{ $servicesHref }}" class="block rounded-xl px-3 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-950" data-motion-action>
                        Search Services
                    </a>
                </nav>

                <div class="mt-4 border-t border-zinc-200 pt-4">
                    @auth
                        <a href="{{ route('notifications.index') }}" class="inline-flex w-full items-center justify-between rounded-xl border border-zinc-200 px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50" data-motion-action>
                            <span>Notifications</span>
                            @if($unreadNotificationCount > 0)
                                <span class="inline-flex min-h-[1.35rem] min-w-[1.35rem] items-center justify-center rounded-full bg-sky-500 px-1.5 text-[10px] font-semibold text-white">
                                    {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                                </span>
                            @endif
                        </a>

                        @if($isCustomer)
                            <a href="{{ route('customer.dashboard') }}" class="mt-3 inline-flex w-full items-center justify-center rounded-xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800" data-motion-action>
                                My Account
                            </a>
                        @else
                            <a href="{{ route('profile.index') }}" class="mt-3 inline-flex w-full items-center justify-center rounded-xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800" data-motion-action>
                                Profile
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" class="mt-3">
                            @csrf
                            <button type="submit" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50" data-motion-action>
                                Log out
                            </button>
                        </form>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800" data-motion-action>
                            Sign up
                        </a>
                        <a href="{{ route('login') }}" class="mt-3 inline-flex w-full items-center justify-center rounded-xl border border-zinc-200 px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50" data-motion-action>
                            Log in
                        </a>
                    @endauth
                </div>
            </div>
        </details>
    </div>
</header>
