@extends('layouts.customer', ['title' => 'Notifications | SkillSlot'])

@section('content')
@php
    $latestNotification = $notifications->first();
@endphp

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" data-motion-section>
    <section class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-8" data-motion-card>
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-zinc-400">Notification Center</p>
                <h1 class="mt-3 text-4xl font-semibold tracking-[-0.05em] text-zinc-950 sm:text-[3rem]">Keep up with bookings, payments, and account activity.</h1>
                <p class="mt-4 text-[15px] leading-8 text-zinc-500">
                    Important updates for your bookings and payments live here inside the same customer theme, so you no longer need the old dashboard view.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read') }}">
                        @csrf
                        <input type="hidden" name="all" value="1">
                        <button type="submit" class="inline-flex min-h-[3.25rem] items-center justify-center rounded-xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">
                            Mark All Read
                        </button>
                    </form>
                @endif

                <a href="{{ route('customer.dashboard') }}" class="inline-flex min-h-[3.25rem] items-center justify-center rounded-xl border border-zinc-200 bg-white px-5 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50">
                    My Account
                </a>
            </div>
        </div>
    </section>

    <div class="mt-6 grid gap-4 md:grid-cols-3" data-motion-group>
        <article class="rounded-[1.6rem] border border-zinc-200 bg-white p-5 shadow-[0_18px_48px_-38px_rgba(15,23,42,0.28)]" data-motion-item data-motion-card>
            <p class="text-sm font-medium text-zinc-500">Unread</p>
            <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-zinc-950">{{ number_format($unreadCount) }}</p>
            <p class="mt-2 text-sm text-zinc-400">Notifications waiting for your attention.</p>
        </article>
        <article class="rounded-[1.6rem] border border-zinc-200 bg-white p-5 shadow-[0_18px_48px_-38px_rgba(15,23,42,0.28)]" data-motion-item data-motion-card>
            <p class="text-sm font-medium text-zinc-500">Total Notifications</p>
            <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-zinc-950">{{ number_format($notifications->total()) }}</p>
            <p class="mt-2 text-sm text-zinc-400">Across your booking and payment history.</p>
        </article>
        <article class="rounded-[1.6rem] border border-zinc-200 bg-white p-5 shadow-[0_18px_48px_-38px_rgba(15,23,42,0.28)]" data-motion-item data-motion-card>
            <p class="text-sm font-medium text-zinc-500">Latest Activity</p>
            <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-zinc-950">{{ $latestNotification?->created_at?->diffForHumans() ?? 'None' }}</p>
            <p class="mt-2 text-sm text-zinc-400">The newest update delivered to your account.</p>
        </article>
    </div>

    <section class="mt-6 rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-7" data-motion-card>
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-400">Updates</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">Recent notifications</h2>
                <p class="mt-2 text-sm leading-7 text-zinc-500">Read the latest account messages and mark them complete when you are done.</p>
            </div>
            @if($notifications->total() > 0)
                <p class="text-sm text-zinc-500">
                    Showing {{ $notifications->firstItem() }}-{{ $notifications->lastItem() }} of {{ $notifications->total() }}
                </p>
            @endif
        </div>

        @if($notifications->isEmpty())
            <div class="mt-6 rounded-[1.5rem] border border-dashed border-zinc-300 bg-zinc-50 px-5 py-12 text-center">
                <p class="text-lg font-semibold text-zinc-950">No notifications yet</p>
                <p class="mt-2 text-sm leading-7 text-zinc-500">When bookings, payments, or account updates happen, they will show up here.</p>
            </div>
        @else
            <div class="mt-6 space-y-4" data-motion-group>
                @foreach($notifications as $notification)
                    <article class="rounded-[1.5rem] border p-5 transition {{ $notification->read_at ? 'border-zinc-200 bg-zinc-50' : 'border-sky-200 bg-sky-50/70' }}" data-motion-item data-motion-card>
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-semibold tracking-[-0.03em] text-zinc-950">{{ $notification->title }}</h3>
                                    @if(!$notification->read_at)
                                        <span class="inline-flex rounded-full border border-sky-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-sky-700">
                                            New
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-3 text-sm leading-7 text-zinc-600">{{ $notification->message ?: 'No message body.' }}</p>

                                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs font-medium text-zinc-500">
                                    <span>{{ $notification->created_at?->diffForHumans() ?? 'Just now' }}</span>
                                    <span class="h-1 w-1 rounded-full bg-zinc-300"></span>
                                    <span>{{ \Illuminate\Support\Str::headline(str_replace('.', ' ', $notification->type)) }}</span>
                                </div>
                            </div>

                            @if(!$notification->read_at)
                                <form method="POST" action="{{ route('notifications.read') }}">
                                    @csrf
                                    <input type="hidden" name="notification_ids[]" value="{{ $notification->id }}">
                                    <button type="submit" class="inline-flex min-w-[9rem] items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-100">
                                        Mark Read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if($notifications->hasPages())
                <div class="mt-6 rounded-[1.5rem] border border-zinc-200 bg-zinc-50 p-4">
                    {{ $notifications->onEachSide(1)->links() }}
                </div>
            @endif
        @endif
    </section>
</section>
@endsection
