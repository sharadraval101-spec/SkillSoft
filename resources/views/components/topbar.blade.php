@php
    $roleText = auth()->user()->role == 3 ? 'Provider' : (auth()->user()->role == 2 ? 'Admin' : 'Student');
    $unreadNotifications = auth()->user()->notificationsList()->whereNull('read_at')->count();
@endphp

<header class="h-[72px] border-b border-white/10 bg-zinc-950/70 backdrop-blur-xl flex items-center justify-between px-4 lg:px-6 z-20 relative">
    <div class="flex items-center gap-3 flex-1 min-w-0">
        <button type="button" data-sidebar-toggle aria-label="Toggle sidebar" aria-expanded="true"
            class="h-10 w-10 rounded-xl border border-white/15 bg-white/5 hover:bg-cyan-500/15 hover:border-cyan-400/40 text-zinc-200 transition flex items-center justify-center">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="hidden xl:block">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-zinc-500">Workspace</p>
            <p class="text-sm font-bold text-zinc-100 leading-tight">SkillSlot Panel</p>
        </div>

        {{-- <div class="relative w-full max-w-xl hidden md:block ml-2">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" placeholder="Search courses, users, activities..."
                class="block w-full bg-black/35 border border-white/10 py-2.5 pl-10 pr-3 rounded-xl text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-cyan-400/50 focus:border-cyan-400/40 transition-all">
        </div> --}}
    </div>

    <div class="flex items-center gap-3 ml-3">
        <a href="{{ route('notifications.index') }}" class="h-10 w-10 rounded-xl border border-white/10 bg-black/35 hover:bg-cyan-500/15 hover:border-cyan-400/35 text-zinc-300 transition relative flex items-center justify-center">
            @if($unreadNotifications > 0)
                <span class="absolute top-1.5 right-1.5 min-w-4 h-4 px-1 bg-cyan-400 text-black text-[10px] font-bold rounded-full flex items-center justify-center">
                    {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                </span>
            @endif
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1"/>
            </svg>
        </a>

        <a href="{{ route('profile.index') }}" class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/35 px-2.5 py-1.5 hover:bg-cyan-500/10 hover:border-cyan-400/35 transition">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-bold text-zinc-100 leading-none">{{ auth()->user()->name }}</p>
                <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mt-1">{{ $roleText }}</p>
            </div>

            @if(auth()->user()->profile_photo_url)
                <img src="{{ auth()->user()->profile_photo_url }}" alt="Profile photo"
                    class="w-10 h-10 rounded-xl object-cover ring-2 ring-cyan-300/40">
            @else
                <div class="w-10 h-10 bg-cyan-600 rounded-xl flex items-center justify-center text-white font-bold ring-2 ring-cyan-300/40">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            @endif
        </a>
    </div>
</header>
