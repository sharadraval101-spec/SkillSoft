@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <h1 class="text-2xl font-black text-white">Bookings</h1>
        <p class="mt-2 text-sm text-zinc-400">Review customer bookings assigned to your services.</p>
    </section>

    <section class="dashboard-panel">
        @if($bookings->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 py-10 text-center text-zinc-500">
                No bookings found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-zinc-400 border-b border-white/10">
                            <th class="py-3 pr-4 font-semibold">Booking #</th>
                            <th class="py-3 pr-4 font-semibold">Customer</th>
                            <th class="py-3 pr-4 font-semibold">Service</th>
                            <th class="py-3 pr-4 font-semibold">Scheduled</th>
                            <th class="py-3 pr-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            <tr class="border-b border-white/5">
                                <td class="py-3 pr-4 text-zinc-100 font-semibold">{{ $booking->booking_number }}</td>
                                <td class="py-3 pr-4 text-zinc-300">
                                    {{ $booking->customer?->name ?? 'N/A' }}
                                    <span class="block text-xs text-zinc-500">{{ $booking->customer?->email ?? 'N/A' }}</span>
                                </td>
                                <td class="py-3 pr-4 text-zinc-300">
                                    {{ $booking->service?->name ?? 'N/A' }}
                                    @if($booking->serviceVariant)
                                        <span class="block text-xs text-zinc-500">Variant: {{ $booking->serviceVariant->name }}</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-zinc-300">{{ $booking->scheduled_at?->format('d M Y, h:i A') ?? '-' }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ ucfirst($booking->status) }}</td>
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

