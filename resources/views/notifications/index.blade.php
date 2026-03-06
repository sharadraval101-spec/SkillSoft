@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-white">Notification Center</h1>
                <p class="mt-2 text-sm text-zinc-400">
                    Stay updated on bookings, payments, and account activity.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex rounded-lg border border-cyan-400/40 px-3 py-1 text-xs font-semibold text-cyan-200">
                    Unread: {{ $unreadCount }}
                </span>
                <form method="POST" action="{{ route('notifications.read') }}">
                    @csrf
                    <input type="hidden" name="all" value="1">
                    <button type="submit" class="rounded-xl border border-white/15 px-3 py-2 text-xs font-semibold text-zinc-200 hover:bg-white/5">
                        Mark All Read
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="dashboard-panel">
        @if($notifications->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 py-10 text-center text-zinc-500">
                No notifications yet.
            </div>
        @else
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    <article class="rounded-2xl border {{ $notification->read_at ? 'border-white/10 bg-zinc-900/50' : 'border-cyan-400/35 bg-cyan-500/5' }} p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-semibold text-zinc-100">{{ $notification->title }}</h3>
                                    @if(!$notification->read_at)
                                        <span class="inline-flex rounded-md bg-cyan-500/20 px-2 py-0.5 text-[10px] font-semibold text-cyan-200">New</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-zinc-300">{{ $notification->message ?: 'No message body.' }}</p>
                                <p class="mt-2 text-xs text-zinc-500">
                                    {{ $notification->created_at?->diffForHumans() }} • Type: {{ $notification->type }}
                                </p>
                            </div>
                            @if(!$notification->read_at)
                                <form method="POST" action="{{ route('notifications.read') }}">
                                    @csrf
                                    <input type="hidden" name="notification_ids[]" value="{{ $notification->id }}">
                                    <button type="submit" class="rounded-lg border border-emerald-400/35 px-3 py-1.5 text-xs font-semibold text-emerald-200 hover:bg-emerald-500/10">
                                        Mark Read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
