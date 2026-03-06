@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <h1 class="text-2xl font-black text-white">Reschedule Booking</h1>
        <p class="mt-2 text-sm text-zinc-400">
            Booking: <span class="font-semibold text-zinc-200">{{ $booking->booking_number }}</span>
        </p>
    </section>

    <section class="dashboard-panel">
        <h2 class="text-lg font-bold text-white">Current Slot</h2>
        <p class="mt-2 text-zinc-300">
            {{ $booking->scheduled_at?->format('d M Y, h:i A') }}
        </p>
    </section>

    <section class="dashboard-panel">
        <h2 class="text-lg font-bold text-white">Find New Slot</h2>
        <form method="GET" action="{{ route('customer.bookings.reschedule.form', $booking) }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Date</label>
                <input type="date" name="date" value="{{ $selectedDate->toDateString() }}" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100">
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Branch</label>
                <select name="branch_id" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100">
                    <option value="">Default / Any</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-400">
                    Load Slots
                </button>
            </div>
        </form>
    </section>

    <section class="dashboard-panel">
        <h2 class="text-lg font-bold text-white">Select New Slot</h2>
        @if($availableSlots->isEmpty())
            <div class="mt-4 rounded-2xl border border-dashed border-white/15 py-8 text-center text-zinc-500">
                No available slots for selected date.
            </div>
        @else
            <form method="POST" action="{{ route('customer.bookings.reschedule', $booking) }}" class="mt-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach($availableSlots as $slot)
                        <label class="rounded-xl border border-white/10 bg-zinc-950/40 px-4 py-3 hover:border-cyan-400/40 cursor-pointer">
                            <input type="radio" name="slot_id" value="{{ $slot['slot_id'] }}" class="mr-2 align-middle" required>
                            <span class="text-sm font-semibold text-zinc-100">{{ $slot['label'] }}</span>
                            <span class="block text-xs text-zinc-500 mt-1">{{ \Illuminate\Support\Carbon::parse($slot['start_at'])->format('d M Y') }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-5 flex gap-3">
                    <button type="submit" class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-cyan-400">
                        Confirm Reschedule
                    </button>
                    <a href="{{ route('customer.bookings.index') }}" class="rounded-xl border border-white/15 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/5">
                        Back
                    </a>
                </div>
            </form>
        @endif
    </section>
</div>
@endsection
