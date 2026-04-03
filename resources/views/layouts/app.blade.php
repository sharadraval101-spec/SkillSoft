@php
    $dashboardRole = auth()->check() ? (string) auth()->user()->role : 'guest';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SkillSlot Dashboard' }}</title>
    <script>
        (() => {
            const storageKey = 'skillslot-theme';
            const storedTheme = window.localStorage.getItem(storageKey);
            const preferredTheme = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            const activeTheme = storedTheme === 'light' || storedTheme === 'dark' ? storedTheme : preferredTheme;

            document.documentElement.dataset.theme = activeTheme;
        })();
    </script>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="dashboard-shell h-full flex overflow-hidden"
    data-dashboard-role="{{ $dashboardRole }}"
    @if($dashboardRole === '1') data-user-motion-root="customer-dashboard" @endif
>

    @include('components.sidebar')

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        @include('components.topbar')

        <main class="flex-1 relative overflow-y-auto focus:outline-none p-6 lg:p-10" data-user-main>
            <div class="app-shell-glow absolute top-0 right-0 -z-10 h-96 w-96 rounded-full pointer-events-none blur-[120px]"></div>

            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>

    @include('components.flash-toasts')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
