<header class="h-16 border-b border-white/10 bg-white/90 backdrop-blur-xl flex items-center justify-between px-8 z-20 relative shadow-lg shadow-white/5">

    <div class="flex items-center gap-4 flex-1">
        <div class="relative w-full max-w-md hidden md:block">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" placeholder="Search courses, lessons..."
                class="block w-full bg-black/5 border border-black/5 py-2 pl-10 pr-3 rounded-xl text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-indigo-500/50 transition-all">
        </div>
    </div>

    <div class="flex items-center gap-6">

        <button class="text-zinc-600 hover:text-indigo-600 transition-colors relative group">
            <span class="absolute top-0.5 right-0.5 h-2 w-2 bg-indigo-500 rounded-full border-2 border-white group-hover:animate-ping"></span>
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </button>

        <div class="h-6 w-px bg-black/10"></div>

        <a href="{{ route('profile.index') }}" class="flex items-center gap-3 group">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-bold text-zinc-900 leading-none group-hover:text-indigo-600 transition-colors">{{ auth()->user()->name }}</p>
                <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mt-1">
                    {{ auth()->user()->role == 3 ? 'Provider' : (auth()->user()->role == 2 ? 'Admin' : 'Student') }}
                </p>
            </div>
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold shadow-md shadow-indigo-500/30 group-hover:scale-105 transition-transform ring-2 ring-white">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
        </a>
    </div>
</header>
