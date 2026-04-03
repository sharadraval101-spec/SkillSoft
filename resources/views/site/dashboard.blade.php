@extends('layouts.customer')

@section('content')
@php
    $customerInitial = strtoupper(substr($customer->name ?? 'U', 0, 1));
    $firstName = trim(explode(' ', $customer->name)[0] ?? $customer->name);
    $focusProfile = $errors->any() || session('success') || session('code_sent') || session('password_reset_success');
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
@endphp

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" data-motion-section>
    @if($errors->any())
        <div class="mb-6 rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif
    @if(session('success'))
        <div class="mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('code_sent'))
        <div class="mb-6 rounded-3xl border border-sky-200 bg-sky-50 px-5 py-4 text-sm text-sky-700">{{ session('code_sent') }}</div>
    @endif
    @if(session('password_reset_success'))
        <div class="mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">{{ session('password_reset_success') }}</div>
    @endif

    <section class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-8" data-motion-card>
        <div class="flex flex-col gap-8 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                @if($customer->profile_photo_url)
                    <img src="{{ $customer->profile_photo_url }}" alt="{{ $customer->name }}" class="h-20 w-20 rounded-[1.6rem] object-cover">
                @else
                    <div class="flex h-20 w-20 items-center justify-center rounded-[1.6rem] bg-zinc-950 text-2xl font-semibold text-white">{{ $customerInitial }}</div>
                @endif
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-zinc-400" data-motion-kicker>Customer Dashboard</p>
                    <h1 class="mt-3 text-4xl font-semibold tracking-[-0.05em] text-zinc-950 sm:text-[3rem]" data-motion-title>Hello, {{ $firstName }}.</h1>
                    <p class="mt-4 text-[15px] leading-8 text-zinc-500" data-motion-copy>A simple modern dashboard for your profile, bookings, history, and payments in one place.</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:w-[24rem]" data-motion-actions>
                <a href="{{ route('customer.bookings.create') }}" class="inline-flex min-h-[4.25rem] items-center justify-center rounded-[1.35rem] bg-zinc-950 px-5 py-4 text-center text-sm font-semibold text-white transition hover:bg-zinc-800" data-motion-action>Book New Service</a>
                <a href="#profile-center" class="inline-flex min-h-[4.25rem] items-center justify-center rounded-[1.35rem] border border-zinc-200 bg-white px-5 py-4 text-center text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50" data-motion-action>Profile Settings</a>
                <a href="{{ route('customer.payments.index') }}" class="inline-flex min-h-[4.25rem] items-center justify-center rounded-[1.35rem] border border-zinc-200 bg-white px-5 py-4 text-center text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50" data-motion-action>Payment History</a>
                <a href="{{ route('site.favorites.index') }}" class="inline-flex min-h-[4.25rem] items-center justify-center rounded-[1.35rem] border border-zinc-200 bg-white px-5 py-4 text-center text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50" data-motion-action>Saved Favorites</a>
            </div>
        </div>

        @if($nextBooking)
            <div class="mt-7 rounded-[1.6rem] border border-zinc-200 bg-zinc-50 p-5" data-motion-card>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Next booking</p>
                <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold tracking-[-0.03em] text-zinc-950">{{ $nextBooking->service?->name ?? 'Upcoming service' }}</h2>
                        <p class="mt-2 text-sm text-zinc-600">{{ $nextBooking->scheduled_at?->format('D, d M Y - h:i A') }} @if($nextBooking->provider?->name)with {{ $nextBooking->provider->name }}@endif</p>
                        @if($nextBooking->location_label)<p class="mt-1 text-sm text-zinc-500">{{ $nextBooking->location_label }}</p>@endif
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @if($nextBooking->can_pay)
                            <a href="{{ route('customer.payments.checkout', $nextBooking) }}" class="inline-flex items-center justify-center rounded-xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">Complete Payment</a>
                        @endif
                        @if($nextBooking->can_reschedule)
                            <a href="{{ route('customer.bookings.reschedule.form', $nextBooking) }}" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50">Reschedule</a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </section>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-motion-group>
        @foreach($dashboardStats as $stat)
            <article class="rounded-[1.6rem] border border-zinc-200 bg-white p-5 shadow-[0_18px_48px_-38px_rgba(15,23,42,0.28)]" data-motion-item data-motion-card>
                <p class="text-sm font-medium text-zinc-500">{{ $stat['label'] }}</p>
                <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-zinc-950">{{ $stat['value'] }}</p>
                <p class="mt-2 text-sm text-zinc-400">{{ $stat['hint'] }}</p>
            </article>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
        <section class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-7" data-motion-card>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-400">Upcoming</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">Upcoming bookings</h2>
                </div>
                <a href="{{ route('customer.bookings.index') }}" class="text-sm font-semibold text-zinc-950 transition hover:text-zinc-600">View all</a>
            </div>

            @if($upcomingBookings->isEmpty())
                <div class="mt-6 rounded-[1.5rem] border border-dashed border-zinc-300 bg-zinc-50 px-5 py-10 text-center">
                    <p class="text-lg font-semibold text-zinc-950">No upcoming bookings yet</p>
                    <p class="mt-2 text-sm leading-7 text-zinc-500">Book a service and your next appointment will appear here.</p>
                </div>
            @else
                <div class="mt-6 space-y-4" data-motion-group>
                    @foreach($upcomingBookings as $booking)
                        <article class="rounded-[1.5rem] border border-zinc-200 bg-zinc-50 p-5" data-motion-item data-motion-card>
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-semibold tracking-[-0.03em] text-zinc-950">{{ $booking->service?->name ?? 'Service' }}</h3>
                                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$booking->status] ?? 'border-zinc-200 bg-zinc-100 text-zinc-700' }}">{{ $booking->status_label }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-zinc-600">{{ $booking->scheduled_at?->format('D, d M Y - h:i A') }} @if($booking->provider?->name)with {{ $booking->provider->name }}@endif</p>
                                    @if($booking->location_label)<p class="mt-1 text-sm text-zinc-500">{{ $booking->location_label }}</p>@endif
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    @if($booking->can_pay)<a href="{{ route('customer.payments.checkout', $booking) }}" class="inline-flex items-center justify-center rounded-xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">Pay now</a>@endif
                                    @if($booking->can_reschedule)<a href="{{ route('customer.bookings.reschedule.form', $booking) }}" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50">Reschedule</a>@endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section id="profile-center" class="scroll-mt-28 rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-7" data-motion-card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-400">Profile</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">Profile and security</h2>
                    <p class="mt-2 text-sm leading-7 text-zinc-500">Customer profile stays inside this dashboard now.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-right">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Profile completion</p>
                    <p class="mt-2 text-lg font-semibold text-zinc-950">{{ $profileCompletion }}%</p>
                </div>
            </div>

            <form id="customerProfileForm" data-has-photo="{{ $customer->profile_photo_path ? '1' : '0' }}" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div class="flex items-center gap-4">
                    @if($customer->profile_photo_url)
                        <img id="customerProfilePhotoPreview" src="{{ $customer->profile_photo_url }}" alt="{{ $customer->name }}" class="h-16 w-16 rounded-2xl object-cover">
                    @else
                        <img id="customerProfilePhotoPreview" src="" alt="{{ $customer->name }}" class="hidden h-16 w-16 rounded-2xl object-cover">
                    @endif
                    <div id="customerProfileInitial" class="{{ $customer->profile_photo_url ? 'hidden ' : '' }}flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-950 text-xl font-semibold text-white">{{ $customerInitial }}</div>
                    <div>
                        <p class="text-sm font-medium text-zinc-900">Profile photo</p>
                        <p class="mt-1 text-xs text-zinc-500">Upload JPG, PNG, or WEBP up to 2MB.</p>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="dashboard-name" class="block text-sm font-medium text-zinc-700">Full name</label>
                        <input id="dashboard-name" type="text" name="name" value="{{ old('name', $customer->name) }}" required class="mt-2 h-12 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-400 focus:bg-white">
                    </div>
                    <div>
                        <label for="dashboard-email" class="block text-sm font-medium text-zinc-700">Email address</label>
                        <input id="dashboard-email" type="email" name="email" value="{{ old('email', $customer->email) }}" required class="mt-2 h-12 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-400 focus:bg-white">
                    </div>
                </div>
                <div>
                    <label for="customerProfilePhotoInput" class="block text-sm font-medium text-zinc-700">Upload photo</label>
                    <input id="customerProfilePhotoInput" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp" @if(!$customer->profile_photo_path) required @endif class="mt-2 block w-full rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-4 py-3 text-sm text-zinc-600 file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-950 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">Save profile</button>
                </div>
            </form>

            <div class="mt-6 rounded-[1.5rem] border border-zinc-200 bg-zinc-50 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-zinc-900">Reset password with email code</p>
                        <p class="mt-1 text-sm text-zinc-500">Send a code to {{ $customer->email }} and update your password here.</p>
                    </div>
                    <form action="{{ route('profile.password.send_code') }}" method="POST">@csrf<button type="submit" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-100">Send Code</button></form>
                </div>
                <form action="{{ route('profile.password.reset_by_code') }}" method="POST" class="mt-5 grid gap-4">
                    @csrf
                    <input type="text" name="code" value="{{ old('code') }}" required maxlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="Verification code" class="h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm tracking-[0.2em] text-zinc-900 outline-none transition focus:border-zinc-400">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <input type="password" name="password" required minlength="8" placeholder="New password"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                            title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                            class="h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-400">
                        <input type="password" name="password_confirmation" required minlength="8" placeholder="Confirm password"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                            title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                            class="h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm text-zinc-900 outline-none transition focus:border-zinc-400">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">Update password</button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
        <section class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-7" data-motion-card>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-400">History</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">Past booking history</h2>
                </div>
                <a href="{{ route('customer.bookings.index') }}" class="text-sm font-semibold text-zinc-950 transition hover:text-zinc-600">Booking center</a>
            </div>
            @if($bookingHistory->isEmpty())
                <div class="mt-6 rounded-[1.5rem] border border-dashed border-zinc-300 bg-zinc-50 px-5 py-10 text-center">
                    <p class="text-lg font-semibold text-zinc-950">No booking history yet</p>
                    <p class="mt-2 text-sm leading-7 text-zinc-500">Completed and older bookings will appear here.</p>
                </div>
            @else
                <div class="mt-6 space-y-4" data-motion-group>
                    @foreach($bookingHistory as $booking)
                        <article class="rounded-[1.5rem] border border-zinc-200 bg-zinc-50 p-5" data-motion-item data-motion-card>
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-lg font-semibold tracking-[-0.03em] text-zinc-950">{{ $booking->service?->name ?? 'Service' }}</h3>
                                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$booking->status] ?? 'border-zinc-200 bg-zinc-100 text-zinc-700' }}">{{ $booking->status_label }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-zinc-600">{{ $booking->scheduled_at?->format('D, d M Y - h:i A') }} @if($booking->provider?->name)with {{ $booking->provider->name }}@endif</p>
                                    @if($booking->location_label)<p class="mt-1 text-sm text-zinc-500">{{ $booking->location_label }}</p>@endif
                                </div>
                                <a href="{{ $booking->book_again_url }}" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50">Book Again</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-7" data-motion-card>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-400">Payments</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-zinc-950">Recent payment activity</h2>
                </div>
                <a href="{{ route('customer.payments.index') }}" class="text-sm font-semibold text-zinc-950 transition hover:text-zinc-600">All payments</a>
            </div>
            @if($recentPayments->isEmpty())
                <div class="mt-6 rounded-[1.5rem] border border-dashed border-zinc-300 bg-zinc-50 px-5 py-10 text-center">
                    <p class="text-lg font-semibold text-zinc-950">No payments yet</p>
                    <p class="mt-2 text-sm leading-7 text-zinc-500">Payment updates and refund records will appear here.</p>
                </div>
            @else
                <div class="mt-6 space-y-3" data-motion-group>
                    @foreach($recentPayments as $payment)
                        <article class="rounded-[1.35rem] border border-zinc-200 bg-zinc-50 px-4 py-4" data-motion-item data-motion-card>
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-zinc-950">{{ $payment->booking?->service?->name ?? 'Booking payment' }}</p>
                                    <p class="mt-1 text-xs text-zinc-500">{{ strtoupper($payment->gateway) }} | {{ ucfirst($payment->method) }} @if($payment->booking?->booking_number)| {{ $payment->booking->booking_number }}@endif</p>
                                </div>
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $paymentStatusClasses[$payment->status] ?? 'border-zinc-200 bg-zinc-100 text-zinc-700' }}">{{ \Illuminate\Support\Str::headline($payment->status) }}</span>
                            </div>
                            <div class="mt-4 flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-lg font-semibold text-zinc-950">Rs. {{ number_format((float) $payment->amount, 0) }}</p>
                                    <p class="mt-1 text-xs text-zinc-500">{{ optional($payment->paid_at ?? $payment->created_at)->format('d M Y, h:i A') }}</p>
                                </div>
                                <a href="{{ route('customer.payments.index') }}" class="text-xs font-semibold text-zinc-950 transition hover:text-zinc-600">Payment history</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="mt-6 border-t border-zinc-200 pt-5">
                <p class="text-sm font-semibold text-zinc-900">Account summary</p>
                <div class="mt-4 space-y-3">
                    @foreach($accountHighlights as $highlight)
                        <div class="flex items-center justify-between rounded-2xl border border-zinc-200 px-4 py-3">
                            <span class="text-sm text-zinc-500">{{ $highlight['label'] }}</span>
                            <span class="text-sm font-semibold text-zinc-950">{{ $highlight['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (() => {
        const focusProfile = @json($focusProfile);
        const profileSection = document.getElementById('profile-center');
        const photoInput = document.getElementById('customerProfilePhotoInput');
        const photoPreview = document.getElementById('customerProfilePhotoPreview');
        const photoInitial = document.getElementById('customerProfileInitial');

        if (focusProfile && profileSection) {
            requestAnimationFrame(() => profileSection.scrollIntoView({ behavior: 'smooth', block: 'start' }));
        }

        if (photoInput && photoPreview && photoInitial) {
            photoInput.addEventListener('change', () => {
                const file = photoInput.files && photoInput.files[0] ? photoInput.files[0] : null;
                if (!file || !file.type.startsWith('image/')) {
                    return;
                }

                photoPreview.src = URL.createObjectURL(file);
                photoPreview.classList.remove('hidden');
                photoInitial.classList.add('hidden');
            });
        }
    })();
</script>
@endpush
