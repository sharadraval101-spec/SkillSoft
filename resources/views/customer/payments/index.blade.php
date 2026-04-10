@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <h1 class="text-2xl font-black text-white">Payment History</h1>
        <p class="mt-2 text-sm text-zinc-400">Track online payments, refunds, and booking-linked transactions.</p>
    </section>

    <section class="dashboard-panel">
        @if($payments->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 py-10 text-center text-zinc-500">
                No payment records found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-zinc-400 border-b border-white/10">
                            <th class="py-3 pr-4 font-semibold">Booking</th>
                            <th class="py-3 pr-4 font-semibold">Gateway</th>
                            <th class="py-3 pr-4 font-semibold">Method</th>
                            <th class="py-3 pr-4 font-semibold">Mode</th>
                            <th class="py-3 pr-4 font-semibold">Amount</th>
                            <th class="py-3 pr-4 font-semibold">Refunded</th>
                            <th class="py-3 pr-4 font-semibold">Status</th>
                            <th class="py-3 pr-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            @php
                                $statusClass = match($payment->status) {
                                    \App\Models\Payment::STATUS_PAID => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
                                    \App\Models\Payment::STATUS_PENDING => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
                                    \App\Models\Payment::STATUS_REFUNDED => 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30',
                                    default => 'bg-rose-500/15 text-rose-300 border-rose-500/30',
                                };
                            @endphp
                            <tr class="border-b border-white/5">
                                <td class="py-3 pr-4 text-zinc-100">
                                    <span class="font-semibold">{{ $payment->booking?->booking_number ?? 'N/A' }}</span>
                                    <span class="block text-xs text-zinc-500">{{ $payment->booking?->service?->name }}</span>
                                </td>
                                <td class="py-3 pr-4 text-zinc-300">{{ strtoupper($payment->gateway) }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ ucfirst($payment->method) }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ ucfirst($payment->payment_mode) }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ number_format((float) $payment->refunded_amount, 2) }} {{ $payment->currency }}</td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4">
                                    <div class="flex flex-wrap gap-2">
                                        @if($payment->booking && in_array($payment->booking->status, [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_ACCEPTED], true))
                                            <a href="{{ route('customer.payments.checkout', $payment->booking) }}" class="rounded-lg border border-indigo-400/35 px-3 py-1.5 text-xs font-semibold text-indigo-200 hover:bg-indigo-500/10">
                                                Checkout
                                            </a>
                                        @endif
                                        @if($payment->can_refund)
                                            <form method="POST" action="{{ route('customer.payments.refund', $payment) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-rose-400/35 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">
                                                    Refund
                                                </button>
                                            </form>
                                        @endif
                                        @if(!$payment->can_refund)
                                            <span class="text-xs text-zinc-500">No refund action</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
