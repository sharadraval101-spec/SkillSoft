@extends('layouts.auth-user', ['title' => 'Provider Registration | SkillSlot'])

@section('content')
<section class="mx-auto max-w-[1120px]" data-motion-section>
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(28rem,0.95fr)] lg:items-center">
        <div class="max-w-2xl">
            <a href="{{ route('site.home') }}" class="inline-flex shrink-0 items-center gap-3 text-zinc-950 leading-none" data-motion-kicker data-motion-action>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl">
                    <svg viewBox="0 0 56 52" class="block h-9 w-9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M8 42V10l16 8 16-8v32l-16-8-16 8Z" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M24 16c0-3.866 3.134-7 7-7s7 3.134 7 7c0 5.044-7 11-7 11s-7-5.956-7-11Z" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="31" cy="16" r="2.5" fill="currentColor"/>
                    </svg>
                </span>
                <span class="self-center text-lg font-semibold tracking-[-0.03em] leading-none">SkillSlot</span>
            </a>

            <p class="mt-8 text-sm font-medium uppercase tracking-[0.22em] text-zinc-400" data-motion-kicker>Provider Sign Up</p>
            <h1 class="mt-4 text-[2.8rem] font-semibold leading-[1.08] tracking-[-0.05em] text-zinc-950 sm:text-[3.5rem]" data-motion-title>
                Join as a provider and list services with the same clean system styling
            </h1>
            <p class="mt-5 max-w-xl text-[15px] leading-8 text-zinc-500" data-motion-copy>
                Create your provider account, submit your business details, and continue into the approval flow without changing the current provider system rules.
            </p>
        </div>

        <div class="rounded-[32px] bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] ring-1 ring-black/5 sm:p-8" data-motion-panel data-motion-card>
            @if($errors->any())
                <div class="mb-6 rounded-[18px] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" data-motion-card>
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('register.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_PROVIDER }}">

                <div>
                    <label for="provider-name" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Full Name</label>
                    <input id="provider-name" type="text" name="name" value="{{ old('name') }}" placeholder="Enter your full name" required class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                </div>

                <div>
                    <label for="provider-business-name" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Business Name</label>
                    <input id="provider-business-name" type="text" name="business_name" value="{{ old('business_name') }}" placeholder="Enter your business name" required class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                </div>

                <div>
                    <label for="provider-email" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Email</label>
                    <input id="provider-email" type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                </div>

                <div>
                    <label for="provider-password" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Password</label>
                    <input id="provider-password" type="password" name="password" placeholder="Create a strong password" required minlength="8"
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                        title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                        class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                </div>

                <div>
                    <label for="provider-password-confirmation" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Confirm Password</label>
                    <input id="provider-password-confirmation" type="password" name="password_confirmation" placeholder="Confirm your password" required minlength="8"
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                        title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                        class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:bg-white">
                </div>

                <p class="text-xs leading-6 text-zinc-500">Provider accounts still require admin approval before dashboard access.</p>

                <button class="inline-flex h-12 w-full items-center justify-center rounded-[14px] bg-zinc-950 px-4 text-sm font-semibold text-white transition hover:bg-zinc-800" data-motion-action>
                    Submit Provider Registration
                </button>
            </form>

            <div class="mt-6 border-t border-black/5 pt-6 text-sm text-zinc-500">
                Already have an account?
                <a href="{{ route('login') }}" class="ml-1 font-semibold text-zinc-950 transition hover:text-zinc-700" data-motion-action>Sign In</a>
            </div>
        </div>
    </div>
</section>
@endsection
