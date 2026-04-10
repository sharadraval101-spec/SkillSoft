<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SkillSlot' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-white text-sky-950 antialiased" data-user-motion-root="customer-site">
    @include('components.boneyard-loader')
    @php
        $usesMinimalCustomerChrome = request()->routeIs('site.home')
            || request()->routeIs('site.booking')
            || request()->routeIs('customer.bookings.*');
        $usesCustomerNotificationChrome = request()->routeIs('notifications.index')
            && auth()->check()
            && (int) auth()->user()->role === \App\Models\User::ROLE_CUSTOMER;
        $usesNeutralCustomerChrome = request()->routeIs('site.services.index')
            || request()->routeIs('site.categories.index')
            || request()->routeIs('site.favorites.index')
            || request()->routeIs('site.services.show')
            || request()->routeIs('customer.dashboard')
            || request()->routeIs('customer.feedback.*')
            || $usesCustomerNotificationChrome;
    @endphp

    <div class="relative overflow-x-clip">
        @unless($usesMinimalCustomerChrome)
            @if($usesNeutralCustomerChrome)
                <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[26rem] bg-gradient-to-b from-zinc-50 via-white to-white"></div>
                <div class="pointer-events-none absolute -left-28 top-28 -z-10 h-72 w-72 rounded-full bg-zinc-100 blur-3xl"></div>
                <div class="pointer-events-none absolute right-0 top-16 -z-10 h-64 w-64 rounded-full bg-stone-100 blur-3xl"></div>
            @else
                <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[28rem] bg-gradient-to-b from-sky-100 via-sky-50 to-white"></div>
                <div class="pointer-events-none absolute -left-32 top-32 -z-10 h-80 w-80 rounded-full bg-sky-200/50 blur-3xl"></div>
                <div class="pointer-events-none absolute -right-28 top-16 -z-10 h-72 w-72 rounded-full bg-cyan-200/45 blur-3xl"></div>
            @endif
        @endunless

        <x-navbar />

        <main class="min-h-[calc(100vh-15rem)]" data-user-main data-boneyard-target>
            @yield('content')
        </main>

        <x-footer />
    </div>

    @include('components.flash-toasts')

    @if(request()->routeIs('site.services.show'))
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    @endif

    @stack('scripts')
</body>
</html>
