@extends('layouts.customer')

@section('content')
<section class="mx-auto max-w-[1280px] px-4 pb-14 pt-10 sm:px-6 lg:px-8" data-motion-section>
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="max-w-2xl">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400" data-motion-kicker>My Bookings</p>
            <h1 class="mt-4 text-[2.5rem] font-semibold tracking-[-0.05em] text-zinc-900 sm:text-[3.1rem]" data-motion-title>Track, manage, and update your bookings</h1>
            <p class="mt-4 text-[15px] leading-8 text-zinc-500" data-motion-copy>Review upcoming bookings, pay pending items, and reschedule or cancel within the allowed booking window.</p>
        </div>
        <a href="{{ route('site.booking') }}" class="inline-flex items-center justify-center rounded-[14px] bg-zinc-950 px-6 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800" data-motion-actions data-motion-action>
            New Booking
        </a>
    </div>

    <section class="mt-10 overflow-hidden rounded-[32px] bg-white shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5" data-motion-card>
        @if($bookings->isEmpty())
            <div class="px-6 py-16 text-center sm:px-8">
                <div class="mx-auto max-w-xl rounded-[24px] border border-dashed border-zinc-300 bg-zinc-50 px-6 py-10 text-sm text-zinc-500">
                    No bookings yet. Start your first appointment from the booking page.
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-zinc-500">
                        <tr>
                            <th class="px-6 py-4 font-semibold sm:px-8">Booking</th>
                            <th class="px-6 py-4 font-semibold">Provider</th>
                            <th class="px-6 py-4 font-semibold">Service</th>
                            <th class="px-6 py-4 font-semibold">Scheduled</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold sm:px-8">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100" data-motion-group>
                        @foreach($bookings as $booking)
                            @php
                                $statusClass = match($booking->status) {
                                    \App\Models\Booking::STATUS_PENDING => 'bg-amber-50 text-amber-700 border-amber-200',
                                    \App\Models\Booking::STATUS_ACCEPTED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    \App\Models\Booking::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-200',
                                    \App\Models\Booking::STATUS_COMPLETED => 'bg-sky-50 text-sky-700 border-sky-200',
                                    default => 'bg-zinc-100 text-zinc-700 border-zinc-200',
                                };
                            @endphp
                            <tr class="align-top" data-motion-item>
                                <td class="px-6 py-5 sm:px-8">
                                    <p class="font-medium text-zinc-900">{{ $booking->booking_number }}</p>
                                    @if($booking->serviceVariant)
                                        <p class="mt-1 text-xs text-zinc-500">Variant: {{ $booking->serviceVariant->name }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-zinc-700">{{ $booking->provider?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-5 text-zinc-700">{{ $booking->service?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-5 text-zinc-700">{{ $booking->scheduled_at?->format('d M Y, h:i A') }}</td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 sm:px-8">
                                    <div class="flex flex-wrap gap-2">
                                        @if(!$booking->has_paid_payment && in_array($booking->status, [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_ACCEPTED], true))
                                            <a href="{{ route('customer.payments.checkout', $booking) }}" class="inline-flex rounded-[10px] border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
                                                Pay Now
                                            </a>
                                        @endif

                                        @if($booking->can_reschedule)
                                            <a href="{{ route('customer.bookings.reschedule.form', $booking) }}" class="inline-flex rounded-[10px] border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
                                                Reschedule
                                            </a>
                                        @endif

                                        @if($booking->can_cancel)
                                            <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex rounded-[10px] border border-rose-200 px-3 py-2 text-xs font-medium text-rose-600 transition hover:bg-rose-50">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif

                                        @if(!$booking->can_reschedule && !$booking->can_cancel && !(!$booking->has_paid_payment && in_array($booking->status, [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_ACCEPTED], true)))
                                            <span class="text-xs text-zinc-400">No actions</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-zinc-100 px-6 py-4 sm:px-8">
                {{ $bookings->links() }}
            </div>
        @endif
    </section>
</section>
@endsection
