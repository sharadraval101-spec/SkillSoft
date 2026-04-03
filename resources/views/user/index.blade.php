@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10 gap-6">
        <div>
            <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 text-xs font-bold rounded-full uppercase tracking-widest border border-indigo-500/20">
                Student Portal
            </span>
            <h1 class="text-4xl font-bold text-white mt-4">{{ auth()->user()->name }}!</h1>
            <p class="text-zinc-500 mt-2">Ready to continue your learning journey on SkillSlot?</p>
        </div>

        <div class="flex gap-4">
            <a href="{{ route('customer.bookings.create') }}" class="bg-cyan-500 hover:bg-cyan-400 text-zinc-950 px-6 py-3 rounded-2xl font-semibold transition-all flex items-center gap-2">
                Create Booking
            </a>
            <a href="{{ route('customer.payments.index') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-semibold transition-all flex items-center gap-2">
                Payment History
            </a>
            <a href="{{ route('profile.index') }}" class="bg-zinc-900 hover:bg-zinc-800 text-white px-6 py-3 rounded-2xl font-semibold border border-white/5 transition-all flex items-center gap-2">
                View Profile
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="bg-white text-black px-6 py-3 rounded-2xl font-bold hover:bg-zinc-200 transition-all active:scale-95 shadow-lg">
                    Log out
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-zinc-900 border border-white/5 p-6 rounded-3xl shadow-xl">
            <p class="text-zinc-500 text-xs font-bold uppercase tracking-wider">Courses In Progress</p>
            <p class="text-3xl font-bold text-white mt-2">04</p>
        </div>
        <div class="bg-zinc-900 border border-white/5 p-6 rounded-3xl shadow-xl">
            <p class="text-zinc-500 text-xs font-bold uppercase tracking-wider">Completed Lessons</p>
            <p class="text-3xl font-bold text-white mt-2">128</p>
        </div>
        <div class="bg-zinc-900 border border-white/5 p-6 rounded-3xl shadow-xl">
            <p class="text-zinc-500 text-xs font-bold uppercase tracking-wider">Learning Hours</p>
            <p class="text-3xl font-bold text-indigo-400 mt-2">42.5h</p>
        </div>
    </div>

    <div class="bg-zinc-900 border border-white/5 rounded-3xl p-1 relative overflow-hidden">
        <div class="p-8">
            <h2 class="text-xl font-bold text-white mb-6">Continue Learning</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="group bg-black/40 border border-white/5 p-5 rounded-2xl hover:border-indigo-500/50 transition-all">
                    <div class="h-32 bg-zinc-800 rounded-xl mb-4 overflow-hidden">
                        <div class="w-full h-full bg-gradient-to-br from-indigo-600/20 to-purple-600/20 group-hover:scale-110 transition-transform duration-500"></div>
                    </div>
                    <h3 class="font-bold text-white group-hover:text-indigo-400 transition-colors">Advanced Web Development</h3>
                    <p class="text-zinc-500 text-sm mt-2">65% Completed</p>
                    <div class="w-full bg-zinc-800 h-1.5 mt-4 rounded-full overflow-hidden">
                        <div class="bg-indigo-500 h-full" style="width: 65%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
