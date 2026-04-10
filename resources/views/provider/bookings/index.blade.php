@extends('layouts.app')


@section('content')
<div id="provider-bookings-page" class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <h1 class="text-2xl font-black text-white">Bookings</h1>
        <p class="mt-2 text-sm text-zinc-400">Review customer bookings assigned to your services, reschedule upcoming appointments, and mark completed appointments once the service is finished.</p>
    </section>

    <section class="dashboard-panel">
        @if($bookings->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 py-10 text-center">
                <p class="text-base font-semibold text-zinc-300">No bookings found.</p>
                <p class="mt-2 text-sm text-zinc-500">Once appointments are listed here, each upcoming row will show its own Reschedule button.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-left text-zinc-400">
                            <th class="py-3 pr-4 font-semibold">Booking #</th>
                            <th class="py-3 pr-4 font-semibold">Customer</th>
                            <th class="py-3 pr-4 font-semibold">Service</th>
                            <th class="py-3 pr-4 font-semibold">Scheduled</th>
                            <th class="py-3 pr-4 font-semibold">Status</th>
                            <th class="py-3 pr-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            @php
                                $statusClass = match ($booking->status) {
                                    \App\Models\Booking::STATUS_PENDING => 'border-amber-400/35 bg-amber-500/10 text-amber-200',
                                    \App\Models\Booking::STATUS_ACCEPTED, \App\Models\Booking::STATUS_CONFIRMED => 'border-emerald-400/35 bg-emerald-500/10 text-emerald-200',
                                    \App\Models\Booking::STATUS_COMPLETED => 'border-cyan-400/35 bg-cyan-500/10 text-cyan-200',
                                    \App\Models\Booking::STATUS_CANCELLED, \App\Models\Booking::STATUS_REJECTED => 'border-rose-400/35 bg-rose-500/10 text-rose-200',
                                    default => 'border-zinc-600/40 bg-zinc-800/60 text-zinc-200',
                                };
                            @endphp
                            <tr class="border-b border-white/5">
                                <td class="py-3 pr-4 font-semibold text-zinc-100">{{ $booking->booking_number }}</td>
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
                                <td class="py-3 pr-4">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-zinc-300">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if($booking->can_provider_accept)
                                            <form method="POST" action="{{ route('provider.bookings.accept', $booking) }}">
                                                @csrf
                                                @method('PUT')
                                                <button
                                                    type="submit"
                                                    class="smooth-action-btn rounded-lg border border-emerald-400/35 px-3 py-1.5 text-xs font-semibold text-emerald-200 hover:bg-emerald-500/10"
                                                >
                                                    Accept
                                                </button>
                                            </form>
                                        @endif

                                        @if($booking->can_provider_complete)
                                            <form method="POST" action="{{ route('provider.bookings.complete', $booking) }}">
                                                @csrf
                                                @method('PUT')
                                                <button
                                                    type="submit"
                                                    class="smooth-action-btn rounded-lg border border-sky-400/35 px-3 py-1.5 text-xs font-semibold text-sky-200 hover:bg-sky-500/10"
                                                >
                                                    Complete
                                                </button>
                                            </form>
                                        @endif

                                        @if($booking->can_provider_reschedule)
                                            <button
                                                type="button"
                                                data-modal-open="provider-booking-reschedule-{{ $booking->id }}"
                                                class="smooth-action-btn rounded-lg border border-cyan-400/35 px-3 py-1.5 text-xs font-semibold text-cyan-200 hover:bg-cyan-500/10"
                                            >
                                                Reschedule
                                            </button>
                                        @endif

                                        @if(!$booking->can_provider_accept && !$booking->can_provider_complete && !$booking->can_provider_reschedule)
                                            <span class="text-xs text-zinc-500">Unavailable</span>
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

    @foreach($bookings as $booking)
        @if($booking->can_provider_reschedule)
            @php
                $defaultRescheduleDate = $booking->scheduled_at?->copy()->addDay()->toDateString();
            @endphp
            <x-modal id="provider-booking-reschedule-{{ $booking->id }}" title="Reschedule Appointment" max-width="max-w-xl">
                <div class="space-y-4">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-zinc-300">
                        <p><span class="font-semibold text-zinc-100">Booking:</span> {{ $booking->booking_number }}</p>
                        <p class="mt-1"><span class="font-semibold text-zinc-100">Customer:</span> {{ $booking->customer?->name ?? 'N/A' }}</p>
                        <p class="mt-1"><span class="font-semibold text-zinc-100">Current Schedule:</span> {{ $booking->scheduled_at?->format('d M Y, h:i A') ?? '-' }}</p>
                        <p class="mt-2 text-xs text-zinc-500">The appointment will keep the same time range and move to the new date you choose.</p>
                    </div>

                    <form method="POST" action="{{ route('provider.bookings.reschedule', $booking) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">New Date</label>
                            <input
                                type="date"
                                name="reschedule_to_date"
                                value="{{ old('booking_id') === $booking->id ? old('reschedule_to_date') : $defaultRescheduleDate }}"
                                min="{{ now()->toDateString() }}"
                                required
                                class="w-full rounded-xl border border-white/15 bg-zinc-950/70 px-3 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 focus:border-cyan-400/50 focus:outline-none"
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Reason (Optional)</label>
                            <input
                                type="text"
                                name="reason"
                                value="{{ old('booking_id') === $booking->id ? old('reason') : '' }}"
                                maxlength="255"
                                placeholder="Provider requested date change"
                                class="w-full rounded-xl border border-white/15 bg-zinc-950/70 px-3 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 focus:border-cyan-400/50 focus:outline-none"
                            >
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">
                                Cancel
                            </button>
                            <button type="submit" class="smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">
                                Save Reschedule
                            </button>
                        </div>
                    </form>
                </div>
            </x-modal>
        @endif
    @endforeach
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const bookingId = @json(old('booking_id'));
            if (!bookingId || !window.SSModal || typeof window.SSModal.openById !== 'function') {
                return;
            }

            window.SSModal.openById(`provider-booking-reschedule-${bookingId}`);
        })();
    </script>
@endpush

