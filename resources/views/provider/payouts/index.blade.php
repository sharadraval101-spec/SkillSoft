@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <h1 class="text-2xl font-black text-white">Payout Tracking</h1>
        <p class="mt-2 text-sm text-zinc-400">View provider earnings, platform fees, and payout statuses.</p>
    </section>

    <section class="dashboard-panel">
        @if($payouts->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 py-10 text-center text-zinc-500">
                No payout records yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-zinc-400 border-b border-white/10">
                            <th class="py-3 pr-4 font-semibold">Booking</th>
                            <th class="py-3 pr-4 font-semibold">Gross</th>
                            <th class="py-3 pr-4 font-semibold">Platform Fee</th>
                            <th class="py-3 pr-4 font-semibold">Net</th>
                            <th class="py-3 pr-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payouts as $payout)
                            <tr class="border-b border-white/5">
                                <td class="py-3 pr-4 text-zinc-100">{{ $payout->booking?->booking_number ?? 'N/A' }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ number_format((float) $payout->gross_amount, 2) }} {{ $payout->currency }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ number_format((float) $payout->platform_fee_amount, 2) }} {{ $payout->currency }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ number_format((float) $payout->net_amount, 2) }} {{ $payout->currency }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ ucfirst($payout->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $payouts->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
