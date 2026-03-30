@php
    $homeHref = route('site.home');
    $servicesHref = route('site.services.index');
    $bookingHref = route('site.booking');
    $categoriesHref = route('site.categories.index');
    $howItWorksHref = request()->routeIs('site.home') ? '#how-it-works' : route('site.home') . '#how-it-works';
    $becomeProviderHref = route('register.provider');
@endphp

<footer class="mt-16 border-t border-black/5 bg-white">
    <div class="mx-auto max-w-[1280px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <a href="{{ $homeHref }}" class="inline-flex items-center gap-3 text-zinc-950">
                <span class="flex h-11 w-11 items-center justify-center">
                    <svg viewBox="0 0 56 52" class="h-10 w-10" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M8 42V10l16 8 16-8v32l-16-8-16 8Z" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M24 16c0-3.866 3.134-7 7-7s7 3.134 7 7c0 5.044-7 11-7 11s-7-5.956-7-11Z" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="31" cy="16" r="2.5" fill="currentColor"/>
                    </svg>
                </span>
                <span class="text-lg font-semibold tracking-[-0.03em]">ServiceBook</span>
            </a>

            <div class="flex flex-wrap gap-x-6 gap-y-3 text-sm font-medium text-zinc-600">
                <a href="{{ $homeHref }}" class="transition hover:text-zinc-950">Home</a>
                <a href="{{ $servicesHref }}" class="transition hover:text-zinc-950">Services</a>
                <a href="{{ $categoriesHref }}" class="transition hover:text-zinc-950">Categories</a>
                <a href="{{ $bookingHref }}" class="transition hover:text-zinc-950">Booking</a>
                <a href="{{ $howItWorksHref }}" class="transition hover:text-zinc-950">How It Works</a>
                <a href="{{ $becomeProviderHref }}" class="transition hover:text-zinc-950">Become a Provider</a>
            </div>

            <div class="flex items-center gap-3">
                <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 text-zinc-500 transition hover:border-zinc-950 hover:text-zinc-950" aria-label="Instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3.5" y="3.5" width="17" height="17" rx="4.5"></rect>
                        <circle cx="12" cy="12" r="3.75"></circle>
                        <circle cx="17.3" cy="6.7" r="1"></circle>
                    </svg>
                </a>
                <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 text-zinc-500 transition hover:border-zinc-950 hover:text-zinc-950" aria-label="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13.5 21v-7h2.6l.4-3h-3V9.1c0-.9.3-1.6 1.7-1.6h1.5V4.8c-.7-.1-1.5-.2-2.2-.2-2.2 0-3.8 1.4-3.8 4v2.3H8v3h2.7v7h2.8Z"/>
                    </svg>
                </a>
                <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 text-zinc-500 transition hover:border-zinc-950 hover:text-zinc-950" aria-label="X">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.9 3H21l-4.6 5.3L22 21h-4.4l-3.5-4.8L9.9 21H7.8l4.9-5.7L2 3h4.5l3.2 4.5L13.6 3h2.1Zm-1.5 16h1.2L5.9 5H4.6l12.8 14Z"/>
                    </svg>
                </a>
                <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 text-zinc-500 transition hover:border-zinc-950 hover:text-zinc-950" aria-label="LinkedIn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6.94 8.5H3.56V20h3.38V8.5Zm.23-3.56c0-1.02-.78-1.81-1.92-1.81-1.12 0-1.9.79-1.9 1.81 0 1 .76 1.81 1.86 1.81h.02c1.16 0 1.94-.81 1.94-1.81ZM20 13.08C20 9.62 18.16 8 15.7 8c-1.98 0-2.86 1.09-3.35 1.85V8.5H8.97c.04.9 0 11.5 0 11.5h3.38v-6.42c0-.34.03-.68.13-.92.27-.68.88-1.39 1.91-1.39 1.34 0 1.88 1.03 1.88 2.54V20H20v-6.92Z"/>
                    </svg>
                </a>
            </div>
        </div>

        <div class="mt-6 border-t border-black/5 pt-5 text-sm text-zinc-500">
            <p>&copy; {{ now()->year }} ServiceBook. All rights reserved.</p>
        </div>
    </div>
</footer>
