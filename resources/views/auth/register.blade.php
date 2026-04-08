@extends('layouts.auth-user', ['title' => 'Register | SkillSlot'])

@section('content')
<section class="mx-auto max-w-[1120px]" data-motion-section>
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.05fr)_minmax(28rem,0.95fr)] lg:items-center">
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

            <p class="mt-8 text-sm font-medium uppercase tracking-[0.22em] text-zinc-400" data-motion-kicker>Create Account</p>
            <h1 class="mt-4 text-[2.8rem] font-semibold leading-[1.08] tracking-[-0.05em] text-zinc-950 sm:text-[3.6rem]" data-motion-title>
                Choose the registration path that fits your role
            </h1>
            <p class="mt-5 max-w-xl text-[15px] leading-8 text-zinc-500" data-motion-copy>
                Create a customer account to save services and book appointments, or register as a provider to offer services on the platform.
            </p>
        </div>

        <div class="rounded-[32px] bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] ring-1 ring-black/5 sm:p-8" data-motion-panel data-motion-card>
            <div class="grid gap-4" data-motion-group>
                <a href="{{ route('register.customer') }}" class="rounded-[24px] border border-zinc-200 bg-zinc-50 p-6 transition hover:border-zinc-950 hover:bg-white hover:shadow-[0_18px_40px_rgba(15,23,42,0.06)]" data-motion-item data-motion-card data-motion-action>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Customer</p>
                    <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">Book trusted services</h2>
                    <p class="mt-3 text-sm leading-7 text-zinc-500">
                        Create a customer account to browse categories, save favorite services, and continue with future booking features.
                    </p>
                    <span class="mt-5 inline-flex items-center text-sm font-semibold text-zinc-950">Register as Customer</span>
                </a>

                <a href="{{ route('register.provider') }}" class="rounded-[24px] border border-zinc-200 bg-zinc-50 p-6 transition hover:border-zinc-950 hover:bg-white hover:shadow-[0_18px_40px_rgba(15,23,42,0.06)]" data-motion-item data-motion-card data-motion-action>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Provider</p>
                    <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">List and manage services</h2>
                    <p class="mt-3 text-sm leading-7 text-zinc-500">
                        Register as a provider to publish services, manage schedules, and access the provider dashboard after approval.
                    </p>
                    <span class="mt-5 inline-flex items-center text-sm font-semibold text-zinc-950">Register as Provider</span>
                </a>
            </div>

            <div class="mt-8 border-t border-black/5 pt-6 text-sm text-zinc-500">
                Already have an account?
                <a href="{{ route('login') }}" class="ml-1 font-semibold text-zinc-950 transition hover:text-zinc-700" data-motion-action>Sign In</a>
            </div>
        </div>
    </div>
</section>
@endsection
