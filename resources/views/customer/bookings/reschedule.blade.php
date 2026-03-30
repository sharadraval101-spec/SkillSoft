@extends('layouts.customer')

@section('content')
<section class="mx-auto max-w-[1280px] px-4 pb-14 pt-10 sm:px-6 lg:px-8">
    <div class="max-w-3xl">
        <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">Reschedule Booking</p>
        <h1 class="mt-4 text-[2.5rem] font-semibold tracking-[-0.05em] text-zinc-900 sm:text-[3.1rem]">Update your appointment with the same booking flow</h1>
        <p class="mt-4 text-[15px] leading-8 text-zinc-500">
            Review the current appointment, pick a new date or branch if needed, and choose another available slot.
        </p>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(22rem,0.8fr)]">
        <div class="space-y-6">
            <section class="rounded-[32px] bg-white p-6 shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Current Slot</p>
                <h2 class="mt-3 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-900">{{ $booking->booking_number }}</h2>
                <p class="mt-3 text-sm text-zinc-500">{{ $booking->scheduled_at?->format('d M Y, h:i A') }}</p>
            </section>

            <section class="rounded-[32px] bg-white p-6 shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Step 1</p>
                        <h2 class="mt-2 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-900">Find new slots</h2>
                    </div>
                    <p class="text-sm text-zinc-500">Change the date or branch to refresh availability.</p>
                </div>

                <form method="GET" action="{{ route('customer.bookings.reschedule.form', $booking) }}" class="mt-8 grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="reschedule-date" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Date</label>
                        <input id="reschedule-date" type="date" name="date" value="{{ $selectedDate->toDateString() }}" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
                    </div>

                    <div>
                        <label for="reschedule-branch" class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Branch</label>
                        <select id="reschedule-branch" name="branch_id" class="mt-2 h-12 w-full rounded-[14px] border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:bg-white">
                            <option value="">Default / Any</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="inline-flex h-12 w-full items-center justify-center rounded-[14px] bg-zinc-950 px-4 text-sm font-medium text-white transition hover:bg-zinc-800">
                            Load Slots
                        </button>
                    </div>
                </form>
            </section>

            <section class="rounded-[32px] bg-white p-6 shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Step 2</p>
                        <h2 class="mt-2 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-900">Select a new time</h2>
                    </div>
                    <p class="text-sm text-zinc-500">Choose the slot that works best for you.</p>
                </div>

                @if($availableSlots->isEmpty())
                    <div class="mt-8 rounded-[24px] border border-dashed border-zinc-300 bg-zinc-50 px-6 py-12 text-center text-sm text-zinc-500">
                        No available slots for the selected date.
                    </div>
                @else
                    <form method="POST" action="{{ route('customer.bookings.reschedule', $booking) }}" class="mt-8">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($availableSlots as $slot)
                                <label class="group cursor-pointer">
                                    <input type="radio" name="slot_id" value="{{ $slot['slot_id'] }}" class="peer sr-only" required>
                                    <div class="rounded-[22px] border border-zinc-200 bg-zinc-50 px-5 py-4 transition peer-checked:border-zinc-950 peer-checked:bg-white peer-checked:shadow-[0_16px_35px_rgba(0,0,0,0.08)] group-hover:border-zinc-900">
                                        <p class="text-lg font-medium text-zinc-900">{{ $slot['label'] }}</p>
                                        <p class="mt-2 text-sm text-zinc-500">{{ \Illuminate\Support\Carbon::parse($slot['start_at'])->format('d M Y') }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex min-w-[170px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                                Confirm Reschedule
                            </button>
                            <a href="{{ route('customer.bookings.index') }}" class="inline-flex min-w-[150px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950">
                                Back to Bookings
                            </a>
                        </div>
                    </form>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-[32px] bg-white p-6 shadow-[0_18px_50px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-7">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Booking Details</p>
                <div class="mt-6 space-y-5">
                    <div class="rounded-[20px] bg-zinc-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Service</p>
                        <p class="mt-2 text-lg font-medium text-zinc-900">{{ $booking->service?->name ?? 'Service' }}</p>
                    </div>

                    <div class="rounded-[20px] bg-zinc-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Provider</p>
                        <p class="mt-2 text-lg font-medium text-zinc-900">{{ $booking->provider?->name ?? 'Provider' }}</p>
                    </div>

                    <div class="rounded-[20px] bg-zinc-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Branch</p>
                        <p class="mt-2 text-lg font-medium text-zinc-900">{{ $booking->branch?->name ?? 'Default / Any branch' }}</p>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</section>
@endsection
