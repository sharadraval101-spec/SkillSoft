@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <h1 class="text-2xl font-black text-white">Payment Checkout</h1>
        <p class="mt-2 text-sm text-zinc-400">Complete payment for your booking using Razorpay, Stripe, PayPal, or Cash.</p>
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <article class="dashboard-card lg:col-span-2">
            <p class="dashboard-label">Booking</p>
            <p class="mt-2 text-lg font-bold text-white">{{ $booking->booking_number }}</p>
            <p class="text-zinc-300 mt-1">{{ $booking->service?->name }}</p>
            @if($booking->serviceVariant)
                <p class="text-zinc-500 text-sm">Variant: {{ $booking->serviceVariant->name }}</p>
            @endif
            <p class="text-zinc-400 text-sm mt-3">Provider: {{ $booking->provider?->name ?? 'N/A' }}</p>
            <p class="text-zinc-400 text-sm">Scheduled: {{ $booking->scheduled_at?->format('d M Y, h:i A') }}</p>
        </article>
        <article class="dashboard-card">
            <p class="dashboard-label">Payable Amount</p>
            <p class="mt-2 text-3xl font-black text-cyan-300">{{ $currency }} {{ $amount }}</p>
        </article>
    </section>

    <section class="dashboard-panel">
        <h2 class="text-lg font-bold text-white">Online Payment</h2>
        <form method="POST" action="{{ route('customer.payments.online', $booking) }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Gateway</label>
                <select name="gateway" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100" required>
                    <option value="razorpay">Razorpay (India)</option>
                    <option value="stripe">Stripe</option>
                    <option value="paypal">PayPal</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Mode</label>
                <select name="payment_mode" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100" required>
                    <option value="prepaid">Prepaid</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-cyan-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-cyan-400">
                    Pay Online
                </button>
            </div>
        </form>
    </section>

    <section class="dashboard-panel">
        <h2 class="text-lg font-bold text-white">Cash Payment</h2>
        <form method="POST" action="{{ route('customer.payments.cash', $booking) }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Method</label>
                <input type="text" value="Cash" disabled class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-900/60 px-3 py-2 text-sm text-zinc-400">
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Mode</label>
                <select name="payment_mode" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100" required>
                    <option value="postpaid">Postpaid</option>
                    <option value="prepaid">Prepaid</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-amber-400">
                    Record Cash Payment
                </button>
            </div>
        </form>
    </section>

    @if($booking->payments->isNotEmpty())
        <section class="dashboard-panel">
            <h2 class="text-lg font-bold text-white">Existing Payment Attempts</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-zinc-400 border-b border-white/10">
                            <th class="py-3 pr-4 font-semibold">Gateway</th>
                            <th class="py-3 pr-4 font-semibold">Method</th>
                            <th class="py-3 pr-4 font-semibold">Mode</th>
                            <th class="py-3 pr-4 font-semibold">Amount</th>
                            <th class="py-3 pr-4 font-semibold">Status</th>
                            <th class="py-3 pr-4 font-semibold">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking->payments as $payment)
                            <tr class="border-b border-white/5">
                                <td class="py-3 pr-4 text-zinc-200">{{ strtoupper($payment->gateway) }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ ucfirst($payment->method) }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ ucfirst($payment->payment_mode) }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ ucfirst($payment->status) }}</td>
                                <td class="py-3 pr-4 text-zinc-400">{{ $payment->created_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
@endsection
