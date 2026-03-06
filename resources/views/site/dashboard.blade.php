@extends('layouts.customer')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-sky-600">Customer Dashboard</p>
        <h1 class="mt-2 customer-section-title">Manage Your Bookings</h1>
        <p class="mt-3 customer-muted">Track booking status, make payments, and reschedule when needed.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[15rem,1fr]">
        <aside class="customer-surface h-fit p-4">
            <nav class="space-y-1">
                <a href="{{ route('customer.dashboard') }}" class="customer-nav-link customer-nav-link-active block">My Bookings</a>
                <a href="{{ route('customer.bookings.create') }}" class="customer-nav-link block">New Booking</a>
                <a href="{{ route('customer.payments.index') }}" class="customer-nav-link block">Payment History</a>
                <a href="{{ route('notifications.index') }}" class="customer-nav-link block">Notifications</a>
                <a href="{{ route('profile.index') }}" class="customer-nav-link block">Profile</a>
            </nav>
        </aside>

        <div class="customer-surface overflow-hidden p-4 sm:p-5">
            <div class="mb-4 flex items-center justify-between gap-2">
                <h2 class="text-lg font-bold text-sky-900">Booking List</h2>
                <a href="{{ route('customer.bookings.create') }}" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-semibold text-white hover:bg-sky-500">
                    + New Booking
                </a>
            </div>

            <div class="overflow-x-auto">
                <table id="customerBookingsTable" class="display w-full text-sm">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Service</th>
                            <th>Provider</th>
                            <th>Scheduled</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(function () {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        const table = $('#customerBookingsTable').DataTable({
            ajax: {
                url: '{{ $bookingsDataUrl }}',
                dataSrc: 'data'
            },
            processing: true,
            paging: true,
            pagingType: 'simple_numbers',
            lengthChange: true,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            info: true,
            order: [[3, 'desc']],
            language: {
                emptyTable: 'No bookings yet. Use "New Booking" to create one.',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            },
            columns: [
                {
                    data: 'booking_number',
                    render: function (data, type, row) {
                        const variant = row.variant ? `<p class="text-xs text-sky-600 mt-0.5">Variant: ${$('<div>').text(row.variant).html()}</p>` : '';
                        return `<div>
                            <p class="font-semibold text-sky-900">${$('<div>').text(data).html()}</p>
                            <p class="text-xs text-sky-600 mt-0.5">Location: ${$('<div>').text(row.location).html()}</p>
                            ${variant}
                        </div>`;
                    }
                },
                {
                    data: 'service',
                    render: function (data) {
                        return `<span class="text-sky-800">${$('<div>').text(data).html()}</span>`;
                    }
                },
                {
                    data: 'provider',
                    render: function (data) {
                        return `<span class="text-sky-800">${$('<div>').text(data).html()}</span>`;
                    }
                },
                {
                    data: 'scheduled_at',
                    render: function (data, type, row) {
                        if (type === 'sort' || type === 'type') {
                            return row.scheduled_at_timestamp ?? 0;
                        }
                        return data;
                    }
                },
                {
                    data: null,
                    render: function (row) {
                        const badgeClass = {
                            pending: 'bg-amber-100 text-amber-700 border-amber-200',
                            accepted: 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            rejected: 'bg-rose-100 text-rose-700 border-rose-200',
                            completed: 'bg-sky-100 text-sky-700 border-sky-200',
                            cancelled: 'bg-zinc-100 text-zinc-700 border-zinc-200'
                        };
                        const cls = badgeClass[row.status] || badgeClass.cancelled;
                        return `<span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold ${cls}">${row.status_label}</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (row) {
                        const actions = [];
                        if (row.can_pay && row.checkout_url) {
                            actions.push(`<a href="${row.checkout_url}" class="inline-flex rounded-lg border border-sky-300 px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-50">Pay</a>`);
                        }
                        if (row.can_reschedule && row.reschedule_url) {
                            actions.push(`<a href="${row.reschedule_url}" class="inline-flex rounded-lg border border-sky-300 px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-50">Reschedule</a>`);
                        }
                        if (row.can_cancel && row.cancel_url) {
                            actions.push(`<button type="button" class="js-cancel-booking inline-flex rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50" data-url="${row.cancel_url}">Cancel</button>`);
                        }
                        if (!actions.length) {
                            actions.push('<span class="text-xs text-sky-600">No actions</span>');
                        }
                        return `<div class="flex flex-wrap gap-2">${actions.join('')}</div>`;
                    }
                }
            ]
        });

        $('#customerBookingsTable').on('click', '.js-cancel-booking', function () {
            const cancelUrl = $(this).data('url');
            if (!cancelUrl || !confirm('Cancel this booking?')) {
                return;
            }

            $.ajax({
                url: cancelUrl,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                data: {
                    reason: 'Cancelled by customer from dashboard'
                }
            }).done(function () {
                table.ajax.reload(null, false);
            }).fail(function () {
                alert('Unable to cancel booking right now. Please try again.');
            });
        });
    });
</script>
@endpush
