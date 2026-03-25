<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SkillSlot' }}</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-white text-sky-950 antialiased">
    <div class="relative overflow-x-clip">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[28rem] bg-gradient-to-b from-sky-100 via-sky-50 to-white"></div>
        <div class="pointer-events-none absolute -left-32 top-32 -z-10 h-80 w-80 rounded-full bg-sky-200/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-28 top-16 -z-10 h-72 w-72 rounded-full bg-cyan-200/45 blur-3xl"></div>

        <header class="sticky top-0 z-40 border-b border-sky-100/90 bg-white/90 backdrop-blur-md">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('site.home') }}" class="inline-flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-sky-500 text-sm font-black text-white">SS</span>
                    <span class="text-lg font-bold tracking-tight text-sky-900">SkillSlot</span>
                </a>

                <div class="hidden items-center gap-1 md:flex">
                    <a href="{{ route('site.home') }}" class="customer-nav-link {{ request()->routeIs('site.home') ? 'customer-nav-link-active' : '' }}">Home</a>
                    <a href="{{ route('site.services.index') }}" class="customer-nav-link {{ request()->routeIs('site.services.*') ? 'customer-nav-link-active' : '' }}">Services</a>
                    @auth
                        @if((int) auth()->user()->role === \App\Models\User::ROLE_CUSTOMER)
                            <a href="{{ route('customer.dashboard') }}" class="customer-nav-link {{ request()->routeIs('customer.dashboard') ? 'customer-nav-link-active' : '' }}">Dashboard</a>
                        @endif
                    @endauth
                </div>

                <div class="hidden items-center gap-2 md:flex">
                    @auth
                        <a href="{{ route('notifications.index') }}" class="rounded-xl border border-sky-200 px-3 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-50">
                            Notifications
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-500">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl border border-sky-200 px-3 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-50">Login</a>
                        <a href="{{ route('register') }}" class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-500">Join Now</a>
                    @endauth
                </div>

                <button type="button" id="customer-menu-button" class="inline-flex items-center justify-center rounded-xl border border-sky-200 p-2 text-sky-700 md:hidden">
                    <span class="sr-only">Open menu</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </nav>

            <div id="customer-mobile-menu" class="hidden border-t border-sky-100 bg-white px-4 pb-4 md:hidden">
                <div class="mt-3 space-y-1">
                    <a href="{{ route('site.home') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-sky-800 hover:bg-sky-50">Home</a>
                    <a href="{{ route('site.services.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-sky-800 hover:bg-sky-50">Services</a>
                    @auth
                        @if((int) auth()->user()->role === \App\Models\User::ROLE_CUSTOMER)
                            <a href="{{ route('customer.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-sky-800 hover:bg-sky-50">Dashboard</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="pt-2">
                            @csrf
                            <button type="submit" class="w-full rounded-lg bg-sky-600 px-3 py-2 text-sm font-semibold text-white">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-sky-800 hover:bg-sky-50">Login</a>
                        <a href="{{ route('register') }}" class="block rounded-lg bg-sky-600 px-3 py-2 text-sm font-semibold text-white">Join Now</a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="min-h-[calc(100vh-15rem)]">
            @yield('content')
        </main>

        <footer class="mt-16 border-t border-sky-100 bg-sky-50/75">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-3 lg:px-8">
                <div>
                    <h3 class="text-base font-bold text-sky-900">SkillSlot</h3>
                    <p class="mt-3 max-w-sm text-sm text-sky-700">
                        Discover skilled professionals, book trusted services, and manage everything from one simple customer space.
                    </p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-sky-800">Explore</h4>
                    <ul class="mt-3 space-y-2 text-sm text-sky-700">
                        <li><a href="{{ route('site.home') }}" class="hover:text-sky-900">Home</a></li>
                        <li><a href="{{ route('site.services.index') }}" class="hover:text-sky-900">All Services</a></li>
                        @auth
                            @if((int) auth()->user()->role === \App\Models\User::ROLE_CUSTOMER)
                                <li><a href="{{ route('customer.dashboard') }}" class="hover:text-sky-900">My Dashboard</a></li>
                            @endif
                        @endauth
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-sky-800">Contact</h4>
                    <ul class="mt-3 space-y-2 text-sm text-sky-700">
                        <li>support@skillslot.local</li>
                        <li>+91 00000 00000</li>
                        <li>Mon - Sat, 9:00 AM - 7:00 PM</li>
                    </ul>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('customer-menu-button');
            const menu = document.getElementById('customer-mobile-menu');

            if (!button || !menu) {
                return;
            }

            button.addEventListener('click', function () {
                menu.classList.toggle('hidden');
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
