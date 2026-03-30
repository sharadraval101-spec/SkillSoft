@extends('layouts.customer')

@section('content')
@php
    $customerInitial = strtoupper(substr($customer->name ?? 'U', 0, 1));
    $statusClasses = [
        \App\Models\Booking::STATUS_PENDING => 'border-amber-200 bg-amber-50 text-amber-700',
        \App\Models\Booking::STATUS_ACCEPTED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        \App\Models\Booking::STATUS_CONFIRMED => 'border-sky-200 bg-sky-50 text-sky-700',
        \App\Models\Booking::STATUS_IN_PROGRESS => 'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-700',
        \App\Models\Booking::STATUS_COMPLETED => 'border-zinc-200 bg-zinc-100 text-zinc-700',
        \App\Models\Booking::STATUS_CANCELLED => 'border-rose-200 bg-rose-50 text-rose-700',
        \App\Models\Booking::STATUS_REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
    ];
    $paymentStatusClasses = [
        \App\Models\Payment::STATUS_PAID => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        \App\Models\Payment::STATUS_PENDING => 'border-amber-200 bg-amber-50 text-amber-700',
        \App\Models\Payment::STATUS_REFUNDED => 'border-sky-200 bg-sky-50 text-sky-700',
        \App\Models\Payment::STATUS_FAILED => 'border-rose-200 bg-rose-50 text-rose-700',
    ];
    $firstName = trim(explode(' ', $customer->name)[0] ?? $customer->name);
@endphp

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_22rem]">
        <section class="overflow-hidden rounded-[2rem] border border-zinc-200 bg-white p-7 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-8">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-zinc-400">Customer Dashboard</p>

            <div class="mt-4 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <h1 class="text-4xl font-semibold tracking-[-0.05em] text-zinc-950 sm:text-[3.2rem]">
                        Welcome back, {{ $firstName }}.
                    </h1>
                    <p class="mt-4 max-w-2xl text-[15px] leading-8 text-zinc-500">
                        Manage upcoming appointments, review past bookings, keep an eye on payments, and stay close to your
                        saved services from one polished account view.
                    </p>

                    @if($nextBooking)
                        <div class="mt-6 rounded-[1.75rem] border border-zinc-200 bg-zinc-50 p-5">
                            <span class="inline-flex rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                Next booking
                            </span>

                            <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold tracking-[-0.03em] text-zinc-950">
                                        {{ $nextBooking->service?->name ?? 'Upcoming service' }}
                                    </h2>
                                    <p class="mt-2 text-sm text-zinc-600">
                                        {{ $nextBooking->scheduled_at?->format('D, d M Y - h:i A') }}
                                        @if($nextBooking->provider?->name)
                                            with {{ $nextBooking->provider->name }}
                                        @endif
                                    </p>
                                    @if($nextBooking->location_label)
                                        <p class="mt-1 text-sm text-zinc-500">{{ $nextBooking->location_label }}</p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    @if($nextBooking->can_pay)
                                        <a href="{{ route('customer.payments.checkout', $nextBooking) }}" class="inline-flex items-center justify-center rounded-xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">
                                            Complete Payment
                                        </a>
                                    @endif
                                    @if($nextBooking->can_reschedule)
                                        <a href="{{ route('customer.bookings.reschedule.form', $nextBooking) }}" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-white">
                                            Reschedule
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 rounded-[1.75rem] border border-dashed border-zinc-300 bg-zinc-50 p-5">
                            <h2 class="text-lg font-semibold text-zinc-950">No upcoming bookings yet</h2>
                            <p class="mt-2 text-sm leading-7 text-zinc-500">
                                Explore services, save your favorites, and book your next appointment when you are ready.
                            </p>
                        </div>
                    @endif
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:max-w-sm">
                    <a href="{{ route('customer.bookings.create') }}" class="inline-flex min-h-[4.5rem] items-center justify-center rounded-[1.4rem] bg-zinc-950 px-5 py-4 text-center text-sm font-semibold text-white transition hover:bg-zinc-800">
                        Book New Service
                    </a>
                    <a href="{{ route('customer.payments.index') }}" class="inline-flex min-h-[4.5rem] items-center justify-center rounded-[1.4rem] border border-zinc-200 bg-white px-5 py-4 text-center text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50">
                        Payment History
                    </a>
                    <a href="{{ route('site.favorites.index') }}" class="inline-flex min-h-[4.5rem] items-center justify-center rounded-[1.4rem] border border-zinc-200 bg-white px-5 py-4 text-center text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50">
                        Saved Favorites
                    </a>
                    <a href="{{ route('profile.index') }}" class="inline-flex min-h-[4.5rem] items-center justify-center rounded-[1.4rem] border border-zinc-200 bg-white px-5 py-4 text-center text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50">
                        Manage Profile
                    </a>
                </div>
            </div>
        </section>

        <aside class="overflow-hidden rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)]">
            <div class="flex items-center gap-4">
                @if($customer->profile_photo_url)
                    <img src="{{ $customer->profile_photo_url }}" alt="{{ $customer->name }}" class="h-16 w-16 rounded-2xl object-cover">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-950 text-xl font-semibold text-white">
                        {{ $customerInitial }}
                    </div>
                @endif

                <div class="min-w-0">
                    <p class="truncate text-lg font-semibold tracking-[-0.03em] text-zinc-950">{{ $customer->name }}</p>
                    <p class="truncate text-sm text-zinc-500">{{ $customer->email }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Customer account</p>
                </div>
            </div>

            <div class="mt-6 rounded-[1.5rem] border border-zinc-200 bg-zinc-50 p-4">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-zinc-700">Profile completion</p>
                    <span class="text-sm font-semibold text-zinc-950">{{ $profileCompletion }}%</span>
                </div>
                <div class="mt-3 h-2 rounded-full bg-zinc-200">
                    <div class="h-2 rounded-full bg-zinc-950" style="width: {{ $profileCompletion }}%"></div>
                </div>
                <p class="mt-3 text-xs leading-6 text-zinc-500">
                    Add a profile photo and keep your account details updated for a stronger account presence.
                </p>
            </div>

            <div class="mt-6 space-y-3">
                @foreach($accountHighlights as $highlight)
                    <div class="flex items-center justify-between rounded-[1.2rem] border border-zinc-200 px-4 py-3">
                        <span class="text-sm text-zinc-500">{{ $highlight['label'] }}</span>
                        <span class="text-sm font-semibold text-zinc-950">{{ $highlight['value'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 border-t border-zinc-200 pt-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Member since</p>
                <p class="mt-2 text-sm font-medium text-zinc-900">{{ $customer->created_at?->format('d M Y') }}</p>
            </div>
        </aside>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($dashboardStats as $stat)
            <article class="rounded-[1.7rem] border border-zinc-200 bg-white p-5 shadow-[0_18px_48px_-38px_rgba(15,23,42,0.3)]">
                <p class="text-sm font-medium text-zinc-500">{{ $stat['label'] }}</p>
                <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-zinc-950">{{ $stat['value'] }}</p>
                <p class="mt-2 text-sm text-zinc-400">{{ $stat['hint'] }}</p>
            </article>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_22rem]">
        <section class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-7">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-400">Upcoming</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">Upcoming bookings</h2>
                </div>
                <a href="{{ route('customer.bookings.index') }}" class="text-sm font-semibold text-zinc-950 transition hover:text-zinc-600">
                    View all
                </a>
            </div>

            @if($upcomingBookings->isEmpty())
                <div class="mt-6 rounded-[1.5rem] border border-dashed border-zinc-300 bg-zinc-50 px-5 py-10 text-center">
                    <p class="text-lg font-semibold text-zinc-950">Nothing scheduled right now</p>
                    <p class="mt-2 text-sm leading-7 text-zinc-500">Start a new booking and your next appointment will appear here.</p>
                    <a href="{{ route('customer.bookings.create') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">
                        Start Booking
                    </a>
                </div>
            @else
                <div class="mt-6 space-y-4">
                    @foreach($upcomingBookings as $booking)
                        <article class="rounded-[1.6rem] border border-zinc-200 bg-zinc-50 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex gap-4">
                                    <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-2xl bg-white text-center shadow-sm">
                                        <span class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-400">{{ $booking->scheduled_at?->format('M') }}</span>
                                        <span class="text-2xl font-semibold text-zinc-950">{{ $booking->scheduled_at?->format('d') }}</span>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-lg font-semibold tracking-[-0.03em] text-zinc-950">{{ $booking->service?->name ?? 'Service' }}</h3>
                                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$booking->status] ?? 'border-zinc-200 bg-zinc-100 text-zinc-700' }}">
                                                {{ $booking->status_label }}
                                            </span>
                                        </div>
                                        <p class="mt-2 text-sm text-zinc-600">
                                            {{ $booking->scheduled_at?->format('D, d M Y - h:i A') }}
                                            @if($booking->provider?->name)
                                                with {{ $booking->provider->name }}
                                            @endif
                                        </p>
                                        @if($booking->serviceVariant?->name)
                                            <p class="mt-1 text-sm text-zinc-500">Variant: {{ $booking->serviceVariant->name }}</p>
                                        @endif
                                        @if($booking->location_label)
                                            <p class="mt-1 text-sm text-zinc-500">{{ $booking->location_label }}</p>
                                        @endif
                                        <p class="mt-2 text-xs font-medium uppercase tracking-[0.16em] text-zinc-400">
                                            Booking #{{ $booking->booking_number }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    @if($booking->can_pay)
                                        <a href="{{ route('customer.payments.checkout', $booking) }}" class="inline-flex items-center justify-center rounded-xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">
                                            Pay now
                                        </a>
                                    @endif
                                    @if($booking->can_reschedule)
                                        <a href="{{ route('customer.bookings.reschedule.form', $booking) }}" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50">
                                            Reschedule
                                        </a>
                                    @endif
                                    @if($booking->can_cancel)
                                        <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-7">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-400">Payments</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">Recent payment activity</h2>
                </div>
                <a href="{{ route('customer.payments.index') }}" class="text-sm font-semibold text-zinc-950 transition hover:text-zinc-600">
                    All payments
                </a>
            </div>

            @if($recentPayments->isEmpty())
                <div class="mt-6 rounded-[1.5rem] border border-dashed border-zinc-300 bg-zinc-50 px-5 py-10 text-center">
                    <p class="text-lg font-semibold text-zinc-950">No payments yet</p>
                    <p class="mt-2 text-sm leading-7 text-zinc-500">Payment updates and refund records will show here after your bookings move forward.</p>
                </div>
            @else
                <div class="mt-6 space-y-3">
                    @foreach($recentPayments as $payment)
                        <article class="rounded-[1.35rem] border border-zinc-200 bg-zinc-50 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-zinc-950">
                                        {{ $payment->booking?->service?->name ?? 'Booking payment' }}
                                    </p>
                                    <p class="mt-1 text-xs text-zinc-500">
                                        {{ strtoupper($payment->gateway) }} | {{ ucfirst($payment->method) }}
                                        @if($payment->booking?->booking_number)
                                            | {{ $payment->booking->booking_number }}
                                        @endif
                                    </p>
                                </div>
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $paymentStatusClasses[$payment->status] ?? 'border-zinc-200 bg-zinc-100 text-zinc-700' }}">
                                    {{ \Illuminate\Support\Str::headline($payment->status) }}
                                </span>
                            </div>

                            <div class="mt-4 flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-lg font-semibold text-zinc-950">
                                        Rs. {{ number_format((float) $payment->amount, 0) }}
                                    </p>
                                    <p class="mt-1 text-xs text-zinc-500">
                                        {{ optional($payment->paid_at ?? $payment->created_at)->format('d M Y, h:i A') }}
                                    </p>
                                </div>

                                <a href="{{ route('customer.payments.index') }}" class="text-xs font-semibold text-zinc-950 transition hover:text-zinc-600">
                                    Payment history
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="mt-8 rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-7">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-400">History</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">Past booking history</h2>
                <p class="mt-2 text-sm leading-7 text-zinc-500">
                    Review completed, cancelled, or older appointments and quickly book similar services again.
                </p>
            </div>
            <a href="{{ route('customer.bookings.index') }}" class="text-sm font-semibold text-zinc-950 transition hover:text-zinc-600">
                Open booking center
            </a>
        </div>

        @if($bookingHistory->isEmpty())
            <div class="mt-6 rounded-[1.5rem] border border-dashed border-zinc-300 bg-zinc-50 px-5 py-10 text-center">
                <p class="text-lg font-semibold text-zinc-950">No booking history yet</p>
                <p class="mt-2 text-sm leading-7 text-zinc-500">Once you complete or update bookings, your history will be visible here.</p>
            </div>
        @else
            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                @foreach($bookingHistory as $booking)
                    <article class="rounded-[1.6rem] border border-zinc-200 bg-zinc-50 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="truncate text-lg font-semibold tracking-[-0.03em] text-zinc-950">{{ $booking->service?->name ?? 'Service' }}</h3>
                                <p class="mt-2 text-sm text-zinc-600">
                                    {{ $booking->scheduled_at?->format('D, d M Y - h:i A') }}
                                    @if($booking->provider?->name)
                                        with {{ $booking->provider->name }}
                                    @endif
                                </p>
                                @if($booking->location_label)
                                    <p class="mt-1 text-sm text-zinc-500">{{ $booking->location_label }}</p>
                                @endif
                                @if($booking->serviceVariant?->name)
                                    <p class="mt-1 text-sm text-zinc-500">Variant: {{ $booking->serviceVariant->name }}</p>
                                @endif
                            </div>

                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$booking->status] ?? 'border-zinc-200 bg-zinc-100 text-zinc-700' }}">
                                {{ $booking->status_label }}
                            </span>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3 border-t border-zinc-200 pt-4">
                            <p class="text-xs font-medium uppercase tracking-[0.16em] text-zinc-400">
                                Booking #{{ $booking->booking_number }}
                            </p>

                            <div class="flex flex-wrap gap-3">
                                @if($booking->can_pay)
                                    <a href="{{ route('customer.payments.checkout', $booking) }}" class="text-sm font-semibold text-zinc-950 transition hover:text-zinc-600">
                                        Pay
                                    </a>
                                @endif
                                <a href="{{ $booking->book_again_url }}" class="text-sm font-semibold text-zinc-950 transition hover:text-zinc-600">
                                    Book again
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</section>
@endsection
