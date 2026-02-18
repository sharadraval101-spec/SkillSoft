@php
    $role = (int) auth()->user()->role;
    $user = auth()->user();

    $dashboardRoute = match ($role) {
        \App\Models\User::ROLE_ADMIN => route('admin.dashboard'),
        \App\Models\User::ROLE_PROVIDER => route('provider.dashboard'),
        default => route('user.dashboard'),
    };

    $isDashboardActive = request()->routeIs(match ($role) {
        \App\Models\User::ROLE_ADMIN => 'admin.dashboard',
        \App\Models\User::ROLE_PROVIDER => 'provider.dashboard',
        default => 'user.dashboard',
    });

    $isProfileActive = request()->routeIs('profile.index');
@endphp

<aside id="appSidebar" data-collapsed="0" class="sidebar-shell w-64 transition-[width] duration-300 ease-in-out bg-gradient-to-b from-[#060a12] via-[#080d16] to-[#060913] border-r border-white/10 flex flex-col shrink-0">
    <div class="p-5 border-b border-white/10">
        <div class="sidebar-brand-wrap flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-cyan-500/20 border border-cyan-400/30 text-cyan-200 font-black text-lg flex items-center justify-center">
                S
            </div>
            <div class="min-w-0 sidebar-brand-text">
                <p class="text-white text-lg font-black tracking-tight leading-tight">SkillSoft</p>
                <p class="sidebar-brand-sub text-[11px] text-zinc-500 uppercase tracking-[0.18em]">Control Center</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-3 py-5 overflow-y-auto">
        <p class="sidebar-section-title px-3 pb-2 text-[10px] uppercase tracking-[0.2em] text-zinc-500">Main Navigation</p>

        <a href="{{ $dashboardRoute }}" title="Dashboard" class="sidebar-link group {{ $isDashboardActive ? 'sidebar-link-active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l9-8 9 8M5 10v10h14V10"/>
            </svg>
            <span class="sidebar-label">Dashboard</span>
        </a>

        @if($role == \App\Models\User::ROLE_ADMIN)
            <a href="#" title="User Management" class="sidebar-link group">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M16 3.5a3.5 3.5 0 110 7 3.5 3.5 0 010-7zM21 21v-2a4 4 0 00-3-3.87"/>
                </svg>
                <span class="sidebar-label">User Management</span>
            </a>
            <a href="#" title="System Logs" class="sidebar-link group">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6M9 16h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                </svg>
                <span class="sidebar-label">System Logs</span>
            </a>
        @elseif($role == \App\Models\User::ROLE_PROVIDER)
            <a href="#" title="My Services" class="sidebar-link group">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M7 3h10l2 4v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7l2-4z"/>
                </svg>
                <span class="sidebar-label">My Services</span>
            </a>
            <a href="#" title="Booking Requests" class="sidebar-link group">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10m-12 9h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z"/>
                </svg>
                <span class="sidebar-label">Booking Requests</span>
            </a>
        @else
            <a href="#" title="Browse Courses" class="sidebar-link group">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19.5A2.5 2.5 0 016.5 17H20M6.5 17A2.5 2.5 0 014 14.5V6a2 2 0 012-2h14v13"/>
                </svg>
                <span class="sidebar-label">Browse Courses</span>
            </a>
            <a href="#" title="My Learning" class="sidebar-link group">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.42a12.08 12.08 0 01.84 4.92c0 3.31-3.13 6-7 6s-7-2.69-7-6c0-1.74.31-3.41.84-4.92L12 14z"/>
                </svg>
                <span class="sidebar-label">My Learning</span>
            </a>
        @endif

        <div class="pt-3 mt-4 border-t border-white/10">
            <p class="sidebar-section-title px-3 pb-2 text-[10px] uppercase tracking-[0.2em] text-zinc-500">Account</p>
            <a href="{{ route('profile.index') }}" title="Profile" class="sidebar-link group {{ $isProfileActive ? 'sidebar-link-active' : '' }}">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 21a8 8 0 10-16 0M12 11a4 4 0 100-8 4 4 0 000 8z"/>
                </svg>
                <span class="sidebar-label">Profile</span>
            </a>
        </div>
    </nav>

    <div class="p-3 border-t border-white/10">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button title="Sign Out" class="sidebar-link w-full text-rose-300/90 hover:text-rose-100 hover:bg-rose-500/15">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-7.5A2.25 2.25 0 003.75 5.25v13.5A2.25 2.25 0 006 21h7.5a2.25 2.25 0 002.25-2.25V15m-6-3h11.25m0 0l-3-3m3 3l-3 3"/>
                </svg>
                <span class="sidebar-label sidebar-footer-label">Sign Out</span>
            </button>
        </form>
    </div>
</aside>

@once
    @push('scripts')
        <script>
            (() => {
                const sidebar = document.getElementById('appSidebar');
                if (!sidebar) return;

                const applyState = (isCollapsed) => {
                    sidebar.dataset.collapsed = isCollapsed ? '1' : '0';
                    sidebar.classList.toggle('w-20', isCollapsed);
                    sidebar.classList.toggle('w-64', !isCollapsed);
                    document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
                        btn.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
                    });
                };

                applyState(false);

                document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const next = sidebar.dataset.collapsed !== '1';
                        applyState(next);
                    });
                });
            })();
        </script>
    @endpush
@endonce
