<aside class="w-64 bg-[#0a0a0c] border-r border-white/5 flex flex-col">
    <div class="p-6">
        <div class="text-white text-xl font-bold tracking-tighter">SkillSoft</div>
    </div>

    <nav class="flex-1 px-4 space-y-2 py-4">
        <a href="#" class="block px-4 py-2 text-sm font-medium text-white bg-white/5 rounded-xl">Dashboard</a>

        @if(auth()->user()->role == \App\Models\User::ROLE_ADMIN)
            <a href="#" class="block px-4 py-2 text-sm font-medium hover:bg-white/5 rounded-xl">User Management</a>
            <a href="#" class="block px-4 py-2 text-sm font-medium hover:bg-white/5 rounded-xl">System Logs</a>
        @elseif(auth()->user()->role == \App\Models\User::ROLE_PROVIDER)
            <a href="#" class="block px-4 py-2 text-sm font-medium hover:bg-white/5 rounded-xl">My Services</a>
            <a href="#" class="block px-4 py-2 text-sm font-medium hover:bg-white/5 rounded-xl">Booking Requests</a>
        @else
            <a href="#" class="block px-4 py-2 text-sm font-medium hover:bg-white/5 rounded-xl">Browse Courses</a>
            <a href="#" class="block px-4 py-2 text-sm font-medium hover:bg-white/5 rounded-xl">My Learning</a>
        @endif
    </nav>

    <div class="p-4 border-t border-white/5">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="w-full flex items-center gap-3 px-4 py-2 text-sm text-zinc-500 hover:text-white transition">
                <span>Sign Out</span>
            </button>
        </form>
    </div>
</aside>
