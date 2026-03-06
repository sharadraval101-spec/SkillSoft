@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-white">My Bookings</h1>
                <p class="mt-2 text-sm text-zinc-400">Track booking statuses, reschedule, and cancel within allowed windows.</p>
            </div>
            <a href="{{ route('customer.bookings.create') }}" class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-cyan-400">
                + New Booking
            </a>
        </div>
    </section>

    <section class="dashboard-panel">
        @if($bookings->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 py-10 text-center text-zinc-500">
                No bookings yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-zinc-400 border-b border-white/10">
                            <th class="py-3 pr-4 font-semibold">Booking</th>
                            <th class="py-3 pr-4 font-semibold">Provider</th>
                            <th class="py-3 pr-4 font-semibold">Service</th>
                            <th class="py-3 pr-4 font-semibold">Scheduled</th>
                            <th class="py-3 pr-4 font-semibold">Status</th>
                            <th class="py-3 pr-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            @php
                                $statusClass = match($booking->status) {
                                    \App\Models\Booking::STATUS_PENDING => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
                                    \App\Models\Booking::STATUS_ACCEPTED => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
                                    \App\Models\Booking::STATUS_REJECTED => 'bg-rose-500/15 text-rose-300 border-rose-500/30',
                                    \App\Models\Booking::STATUS_COMPLETED => 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30',
                                    default => 'bg-zinc-500/15 text-zinc-300 border-zinc-500/30',
                                };
                            @endphp
                            <tr class="border-b border-white/5">
                                <td class="py-3 pr-4 text-zinc-100">
                                    <span class="font-semibold">{{ $booking->booking_number }}</span>
                                </td>
                                <td class="py-3 pr-4 text-zinc-300">{{ $booking->provider?->name ?? 'N/A' }}</td>
                                <td class="py-3 pr-4 text-zinc-300">
                                    {{ $booking->service?->name ?? 'N/A' }}
                                    @if($booking->serviceVariant)
                                        <span class="block text-xs text-zinc-500">Variant: {{ $booking->serviceVariant->name }}</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-zinc-300">
                                    {{ $booking->scheduled_at?->format('d M Y, h:i A') }}
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4">
                                    <div class="flex flex-wrap gap-2">
                                        @if(!$booking->has_paid_payment && in_array($booking->status, [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_ACCEPTED], true))
                                            <a href="{{ route('customer.payments.checkout', $booking) }}" class="rounded-lg border border-indigo-400/35 px-3 py-1.5 text-xs font-semibold text-indigo-200 hover:bg-indigo-500/10">
                                                Pay Now
                                            </a>
                                        @endif
                                        @if($booking->can_reschedule)
                                            <a href="{{ route('customer.bookings.reschedule.form', $booking) }}" class="rounded-lg border border-cyan-400/35 px-3 py-1.5 text-xs font-semibold text-cyan-200 hover:bg-cyan-500/10">
                                                Reschedule
                                            </a>
                                        @endif
                                        @if($booking->can_cancel)
                                            <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-rose-400/35 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                        @if(!$booking->can_reschedule && !$booking->can_cancel)
                                            <span class="text-xs text-zinc-500">No actions</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $bookings->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
