<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SkillSlot' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-white text-zinc-950 antialiased" data-user-motion-root="auth-user">
    @include('components.boneyard-loader')
    <div class="relative min-h-screen overflow-x-clip">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[26rem] bg-gradient-to-b from-zinc-50 via-white to-white"></div>
        <div class="pointer-events-none absolute -left-28 top-20 -z-10 h-72 w-72 rounded-full bg-zinc-100 blur-3xl"></div>
        <div class="pointer-events-none absolute right-0 top-10 -z-10 h-64 w-64 rounded-full bg-stone-100 blur-3xl"></div>

        <main class="mx-auto flex min-h-screen w-full max-w-[1280px] items-center justify-center px-4 py-10 sm:px-6 lg:px-8" data-user-main data-boneyard-target>
            <div class="w-full">
                @yield('content')
            </div>
        </main>
    </div>

    @include('components.flash-toasts')
    @stack('scripts')
</body>
</html>
