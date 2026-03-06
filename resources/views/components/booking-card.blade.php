@props(['booking'])

@php
    $statusClass = match ($booking->status) {
        \App\Models\Booking::STATUS_PENDING => 'bg-amber-100 text-amber-700 border-amber-200',
        \App\Models\Booking::STATUS_ACCEPTED => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        \App\Models\Booking::STATUS_REJECTED => 'bg-rose-100 text-rose-700 border-rose-200',
        \App\Models\Booking::STATUS_COMPLETED => 'bg-sky-100 text-sky-700 border-sky-200',
        default => 'bg-zinc-100 text-zinc-700 border-zinc-200',
    };
@endphp

<article class="customer-surface p-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-sky-600">Booking #{{ $booking->booking_number }}</p>
            <h3 class="mt-1 text-lg font-bold text-sky-950">{{ $booking->service?->name ?? 'Service' }}</h3>
            <p class="mt-1 text-sm text-sky-700">Provider: {{ $booking->provider?->name ?? 'N/A' }}</p>
        </div>
        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">
            {{ ucfirst($booking->status) }}
        </span>
    </div>

    <div class="mt-4 grid gap-2 text-sm text-sky-700 sm:grid-cols-2">
        <p><span class="font-semibold text-sky-800">Scheduled:</span> {{ $booking->scheduled_at?->format('d M Y, h:i A') }}</p>
        <p><span class="font-semibold text-sky-800">Location:</span> {{ $booking->branch?->name ?? 'N/A' }}</p>
        @if($booking->serviceVariant)
            <p><span class="font-semibold text-sky-800">Variant:</span> {{ $booking->serviceVariant->name }}</p>
        @endif
    </div>

    <div class="mt-5 flex flex-wrap gap-2 border-t border-sky-100 pt-4">
        @if(!$booking->has_paid_payment && in_array($booking->status, [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_ACCEPTED], true))
            <a href="{{ route('customer.payments.checkout', $booking) }}" class="rounded-lg border border-sky-300 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-50">
                Pay Now
            </a>
        @endif
        @if($booking->can_reschedule)
            <a href="{{ route('customer.bookings.reschedule.form', $booking) }}" class="rounded-lg border border-sky-300 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-50">
                Reschedule
            </a>
        @endif
        @if($booking->can_cancel)
            <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}">
                @csrf
                <button type="submit" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                    Cancel
                </button>
            </form>
        @endif
    </div>
</article>
