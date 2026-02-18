@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center p-6">
    <div class="w-full max-w-4xl bg-[#0f0f11] border border-white/5 rounded-3xl p-8 shadow-2xl relative overflow-hidden">

        <div class="flex justify-between items-start mb-8">
            <div>
                <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 text-xs font-bold rounded-full uppercase tracking-widest border border-indigo-500/20">
                    Service Provider Portal
                </span>
                <h1 class="text-4xl font-bold text-white mt-4">Welcome, {{ auth()->user()->name }}</h1>
                <p class="text-zinc-500 mt-2">Manage your services, track bookings, and view earnings.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white/5 border border-white/5 p-6 rounded-2xl">
                <p class="text-zinc-400 text-sm">Active Services</p>
                <p class="text-2xl font-bold text-white mt-1">12</p>
            </div>
            <div class="bg-white/5 border border-white/5 p-6 rounded-2xl">
                <p class="text-zinc-400 text-sm">Total Bookings</p>
                <p class="text-2xl font-bold text-white mt-1">48</p>
            </div>
            <div class="bg-white/5 border border-white/5 p-6 rounded-2xl">
                <p class="text-zinc-400 text-sm">Monthly Earnings</p>
                <p class="text-2xl font-bold text-green-400 mt-1">$2,450</p>
            </div>
        </div>

        <div class="mt-8 p-12 border border-dashed border-white/10 rounded-2xl text-center">
            <p class="text-zinc-600">You haven't added any services yet. Start by creating your first listing.</p>
            <button class="mt-4 bg-white text-black px-6 py-2 rounded-xl font-bold hover:bg-zinc-200 transition-all text-sm">
                + Create Service
            </button>
        </div>
    </div>
</div>
@endsection
