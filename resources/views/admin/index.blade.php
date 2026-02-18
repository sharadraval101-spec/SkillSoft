@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center p-10">
    <div class="w-full max-w-4xl bg-zinc-900 border border-red-500/20 p-8 rounded-3xl shadow-2xl">
        <h2 class="text-2xl font-bold text-white mb-2">Admin Control Panel</h2>
        <p class="text-zinc-400 mb-6">Welcome back, Sharad. Managing SkillSoft System.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-black/50 p-6 rounded-xl border border-white/5">
                <div class="text-zinc-500 text-xs uppercase font-bold">Total Users</div>
                <div class="text-3xl font-bold text-white mt-1">1,240</div>
            </div>
            </div>
    </div>
</div>
@endsection
