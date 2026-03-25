<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SkillSlot Dashboard' }}</title>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#050505] text-zinc-300 h-full flex overflow-hidden">

    @include('components.sidebar')

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        @include('components.topbar')

        <main class="flex-1 relative overflow-y-auto focus:outline-none p-6 lg:p-10">
            <div class="absolute top-0 right-0 -z-10 w-96 h-96 bg-indigo-500/5 blur-[120px] rounded-full pointer-events-none"></div>

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
