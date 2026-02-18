@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 lg:p-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white tracking-tight">Account Settings</h1>
        <p class="text-zinc-500 mt-2">Manage your profile information and security preferences.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-2xl text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <div class="lg:col-span-1">
            <div class="p-6 bg-zinc-900/50 border border-white/5 rounded-3xl text-center">
                <div class="w-24 h-24 bg-indigo-600 rounded-full mx-auto flex items-center justify-center text-3xl font-bold text-white mb-4">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <h2 class="text-xl font-bold text-white">{{ auth()->user()->name }}</h2>
                <p class="text-zinc-500 text-sm mt-1 capitalize">{{ auth()->user()->getRoleName() }}</p>

                <div class="mt-6 pt-6 border-t border-white/5">
                    <p class="text-xs text-zinc-600 uppercase font-bold tracking-widest">Joined</p>
                    <p class="text-white text-sm mt-1">{{ auth()->user()->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="bg-zinc-900 border border-white/5 rounded-3xl p-8 shadow-xl">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-zinc-500 uppercase mb-2 ml-1">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                                class="w-full bg-black/40 border border-white/10 py-3 px-4 rounded-xl text-white focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-500 uppercase mb-2 ml-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                                class="w-full bg-black/40 border border-white/10 py-3 px-4 rounded-xl text-white focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-white/5">
                        <h3 class="text-white font-bold mb-4">Update Password</h3>
                        <p class="text-zinc-500 text-sm mb-6">Leave blank if you don't want to change it.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <input type="password" name="password" placeholder="New Password"
                                class="w-full bg-black/40 border border-white/10 py-3 px-4 rounded-xl text-white focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
                            <input type="password" name="password_confirmation" placeholder="Confirm Password"
                                class="w-full bg-black/40 border border-white/10 py-3 px-4 rounded-xl text-white focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                    </div>

                    <div class="mt-10 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-8 py-3 rounded-xl transition-all active:scale-95 shadow-lg shadow-indigo-600/20">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
