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
    @php
        $usesMinimalCustomerChrome = request()->routeIs('site.home')
            || request()->routeIs('site.booking')
            || request()->routeIs('customer.bookings.*');
    @endphp

    <div class="relative overflow-x-clip">
        @unless($usesMinimalCustomerChrome)
            <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[28rem] bg-gradient-to-b from-sky-100 via-sky-50 to-white"></div>
            <div class="pointer-events-none absolute -left-32 top-32 -z-10 h-80 w-80 rounded-full bg-sky-200/50 blur-3xl"></div>
            <div class="pointer-events-none absolute -right-28 top-16 -z-10 h-72 w-72 rounded-full bg-cyan-200/45 blur-3xl"></div>
        @endunless

        <x-navbar />

        <main class="min-h-[calc(100vh-15rem)]">
            @yield('content')
        </main>

        @unless($usesMinimalCustomerChrome)
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
        @endunless
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    @stack('scripts')
</body>
</html>
