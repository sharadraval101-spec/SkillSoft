@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        #provider-weekly-availability-page {
            --provider-input-icon-calendar: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='5' width='18' height='16' rx='2'/%3E%3Cpath d='M16 3v4M8 3v4M3 9h18'/%3E%3C/svg%3E");
            --provider-input-icon-time: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='8'/%3E%3Cpath d='M12 8v5l3 2'/%3E%3C/svg%3E");
            --provider-input-icon-text: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 7h14M5 12h14M5 17h9'/%3E%3C/svg%3E");
            --provider-input-icon-select: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M7 7h10M7 12h10M7 17h10M4 7h.01M4 12h.01M4 17h.01'/%3E%3C/svg%3E");
        }

        #provider-weekly-availability-page input[type="date"],
        #provider-weekly-availability-page input[type="time"],
        #provider-weekly-availability-page input[type="text"],
        #provider-weekly-availability-page select,
        #provider-weekly-availability-page textarea {
            background-repeat: no-repeat;
            background-position: 0.85rem center;
            background-size: 1rem 1rem;
            padding-left: 2.5rem !important;
        }

        #provider-weekly-availability-page input[type="date"] {
            background-image: var(--provider-input-icon-calendar);
            padding-right: 2.75rem;
        }

        #provider-weekly-availability-page input[type="time"] {
            background-image: var(--provider-input-icon-time);
            padding-right: 2.75rem;
        }

        #provider-weekly-availability-page input[type="text"],
        #provider-weekly-availability-page textarea {
            background-image: var(--provider-input-icon-text);
        }

        #provider-weekly-availability-page select {
            background-image: var(--provider-input-icon-select);
            padding-right: 2.75rem;
        }
        #provider-weekly-availability-page .dataTables_wrapper .dataTables_length label,
        #provider-weekly-availability-page .dataTables_wrapper .dataTables_filter label,
        #provider-weekly-availability-page .dataTables_wrapper .dataTables_info,
        #provider-weekly-availability-page .dataTables_wrapper .dataTables_paginate {
            color: #a1a1aa;
            font-size: 0.875rem;
        }

        #provider-weekly-availability-page .dataTables_wrapper .dataTables_filter input,
        #provider-weekly-availability-page .dataTables_wrapper .dataTables_length select {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.65rem;
            background: rgba(9, 9, 11, 0.55);
            color: #e4e4e7;
            padding: 0.4rem 0.55rem;
        }

        #provider-weekly-availability-page .dataTables_wrapper {
            overflow-x: hidden;
        }

        #provider-weekly-availability-page table.dataTable {
            width: 100% !important;
        }

        #provider-weekly-availability-page table.dataTable th,
        #provider-weekly-availability-page table.dataTable td {
            white-space: normal;
            overflow-wrap: anywhere;
            vertical-align: middle;
        }

        #provider-weekly-availability-page table.dataTable.no-footer {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        #provider-weekly-availability-page table.dataTable thead th {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        #provider-weekly-availability-page table.dataTable tbody tr {
            background: transparent;
        }

        #provider-weekly-availability-page table.dataTable.stripe tbody tr.odd,
        #provider-weekly-availability-page table.dataTable.display tbody tr.odd {
            background-color: rgba(255, 255, 255, 0.01);
        }

        #provider-weekly-availability-page .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.6rem;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #d4d4d8 !important;
            background: rgba(9, 9, 11, 0.55) !important;
            padding: 0.3rem 0.7rem;
            margin-left: 0.25rem;
        }

        #provider-weekly-availability-page .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: rgba(6, 182, 212, 0.18) !important;
            border-color: rgba(34, 211, 238, 0.45) !important;
            color: #cffafe !important;
        }

        #provider-weekly-availability-page .dataTables_wrapper .dataTables_processing {
            border-radius: 0.75rem;
            border: 1px solid rgba(34, 211, 238, 0.35);
            background: rgba(9, 9, 11, 0.88);
            color: #cffafe;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.3);
            animation: providerWeeklyAvailabilityPulse 1.2s ease-in-out infinite;
        }

        @keyframes providerWeeklyAvailabilityPulse {
            0%, 100% { opacity: 0.72; }
            50% { opacity: 1; }
        }
    </style>
@endpush

@section('content')
<div id="provider-weekly-availability-page" class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <h1 class="text-2xl font-black text-white">Manage Availability</h1>
        <p class="mt-2 text-sm text-zinc-400">Set your weekly working schedule.</p>
        <p class="mt-1 text-sm text-zinc-500">Customers will only see available slots during these times.</p>
    </section>

    <section class="dashboard-panel">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-white">Weekly Schedule</h2>
            <p class="text-sm text-zinc-400 mt-1">Add break time if you are unavailable during working hours.</p>
        </div>

        <form method="POST" action="{{ route('provider.availability.weekly.save') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-zinc-400 border-b border-white/10">
                            <th class="py-3 pr-4 font-semibold">Day</th>
                            <th class="py-3 pr-4 font-semibold">Active</th>
                            <th class="py-3 pr-4 font-semibold">Start Time</th>
                            <th class="py-3 pr-4 font-semibold">End Time</th>
                            <th class="py-3 pr-4 font-semibold">Break Start</th>
                            <th class="py-3 pr-4 font-semibold">Break End</th>
                            <th class="py-3 pr-4 font-semibold">Slot Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weeklyRows as $row)
                            @php
                                $day = (int) $row['day_of_week'];
                            @endphp
                            <tr class="border-b border-white/5">
                                <td class="py-3 pr-4 text-zinc-100 font-semibold">{{ $row['label'] }}</td>
                                <td class="py-3 pr-4 text-zinc-300">
                                    <input type="hidden" name="days[{{ $day }}][is_active]" value="0">
                                    <label class="inline-flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            name="days[{{ $day }}][is_active]"
                                            value="1"
                                            @checked($row['is_active'])
                                            data-day-toggle="{{ $day }}"
                                            class="h-4 w-4 rounded border-white/20 bg-zinc-900 text-cyan-500 focus:ring-cyan-500/30"
                                        >
                                        <span>{{ $row['is_active'] ? 'Enabled' : 'Disabled' }}</span>
                                    </label>
                                </td>
                                <td class="py-3 pr-4">
                                    <input
                                        type="time"
                                        name="days[{{ $day }}][start_time]"
                                        value="{{ $row['start_time'] }}"
                                        data-day-field="{{ $day }}"
                                        data-field-role="start"
                                        class="w-full min-w-28 rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100"
                                    >
                                </td>
                                <td class="py-3 pr-4">
                                    <input
                                        type="time"
                                        name="days[{{ $day }}][end_time]"
                                        value="{{ $row['end_time'] }}"
                                        data-day-field="{{ $day }}"
                                        data-field-role="end"
                                        class="w-full min-w-28 rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100"
                                    >
                                </td>
                                <td class="py-3 pr-4">
                                    <input
                                        type="time"
                                        name="days[{{ $day }}][break_start_time]"
                                        value="{{ $row['break_start_time'] }}"
                                        data-day-field="{{ $day }}"
                                        class="w-full min-w-28 rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100"
                                    >
                                </td>
                                <td class="py-3 pr-4">
                                    <input
                                        type="time"
                                        name="days[{{ $day }}][break_end_time]"
                                        value="{{ $row['break_end_time'] }}"
                                        data-day-field="{{ $day }}"
                                        class="w-full min-w-28 rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100"
                                    >
                                </td>
                                <td class="py-3 pr-4">
                                    <select
                                        name="days[{{ $day }}][slot_duration]"
                                        data-day-field="{{ $day }}"
                                        data-field-role="duration"
                                        class="w-full min-w-28 rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100"
                                    >
                                        @foreach([15, 30, 45, 60] as $duration)
                                            <option value="{{ $duration }}" @selected((int) $row['slot_duration'] === $duration)>{{ $duration }} minutes</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                <button type="submit" class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-cyan-400">
                    Save Availability
                </button>
            </div>
        </form>
    </section>

    <section class="dashboard-panel">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-white">Saved Weekly Availability</h2>
            <p class="text-sm text-zinc-400 mt-1">Quick view of your current weekly setup.</p>
        </div>

        <div class="overflow-x-auto">
            <table id="providerWeeklyAvailabilityTable" class="display w-full text-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Day</th>
                        <th>Status</th>
                        <th>Working Hours</th>
                        <th>Break Time</th>
                        <th>Slot Duration</th>
                        <th>Updated</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <section class="dashboard-panel">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-white">Block Dates / Time</h2>
            <p class="text-sm text-zinc-400 mt-1">Block dates for holidays, vacations, or emergencies.</p>
            <p class="mt-1 text-sm text-zinc-500">Choose the current appointment date and time window, then choose the new date to move those appointments in one action.</p>
        </div>

        <form method="POST" action="{{ route('provider.availability.blocks.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-6">
            @csrf
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Current Appointment Date</label>
                <input
                    type="date"
                    name="block_date"
                    value="{{ old('block_date') }}"
                    min="{{ now()->toDateString() }}"
                    required
                    data-block-date-input
                    class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100"
                >
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">From Time (Optional)</label>
                <input type="time" name="start_time" value="{{ old('start_time') }}" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100">
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">To Time (Optional)</label>
                <input type="time" name="end_time" value="{{ old('end_time') }}" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100">
            </div>
            <div class="md:col-span-3">
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Reason</label>
                <input type="text" name="reason" value="{{ old('reason') }}" placeholder="Holiday / Vacation / Personal Leave / Emergency" class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100">
            </div>
            <div class="md:col-span-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                <label class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Move Appointments To Date</label>
                <input
                    type="date"
                    name="reschedule_to_date"
                    value="{{ old('reschedule_to_date') }}"
                    data-reschedule-date-input
                    class="mt-1 w-full rounded-xl border border-white/10 bg-zinc-950/60 px-3 py-2 text-sm text-zinc-100"
                >
                <p class="mt-2 text-xs text-zinc-500">Leave this empty if you only want to block the date. Add a new date if you want matching appointments moved there.</p>
            </div>
            <div class="md:col-span-6">
                <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-amber-400">
                    Add Block
                </button>
            </div>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-zinc-400 border-b border-white/10">
                        <th class="py-3 pr-4 font-semibold">Date</th>
                        <th class="py-3 pr-4 font-semibold">Blocked Window</th>
                        <th class="py-3 pr-4 font-semibold">Reason</th>
                        <th class="py-3 pr-4 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blockedDates as $blocked)
                        <tr class="border-b border-white/5">
                            <td class="py-3 pr-4 text-zinc-200">{{ $blocked->block_date?->format('d M Y') }}</td>
                            <td class="py-3 pr-4 text-zinc-300">
                                @if(!$blocked->start_time && !$blocked->end_time)
                                    Full Day
                                @else
                                    {{ \Illuminate\Support\Str::of($blocked->start_time)->substr(0, 5) }} - {{ \Illuminate\Support\Str::of($blocked->end_time)->substr(0, 5) }}
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-zinc-400">{{ $blocked->reason ?: '-' }}</td>
                            <td class="py-3 pr-4">
                                <form method="POST" action="{{ route('provider.availability.blocks.destroy', $blocked) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-rose-400/35 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-zinc-500">No blocked date/time entries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $blockedDates->links() }}
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
    (() => {
        const updateRowState = (day) => {
            const checkbox = document.querySelector(`[data-day-toggle="${day}"]`);
            if (!checkbox) return;

            const active = checkbox.checked;
            const fields = document.querySelectorAll(`[data-day-field="${day}"]`);
            fields.forEach((field) => {
                field.disabled = !active;
            });

            const startField = document.querySelector(`[data-day-field="${day}"][data-field-role="start"]`);
            const endField = document.querySelector(`[data-day-field="${day}"][data-field-role="end"]`);
            const durationField = document.querySelector(`[data-day-field="${day}"][data-field-role="duration"]`);
            if (startField) startField.required = active;
            if (endField) endField.required = active;
            if (durationField) durationField.required = active;
        };

        document.querySelectorAll('[data-day-toggle]').forEach((checkbox) => {
            const day = checkbox.getAttribute('data-day-toggle');
            updateRowState(day);
            checkbox.addEventListener('change', () => updateRowState(day));
        });
    })();

    (() => {
        const blockDateInput = document.querySelector('[data-block-date-input]');
        const targetInput = document.querySelector('[data-reschedule-date-input]');

        if (!blockDateInput || !targetInput) {
            return;
        }

        const syncRescheduleState = () => {
            if (!blockDateInput.value) {
                targetInput.removeAttribute('min');
                return;
            }

            const nextDay = new Date(`${blockDateInput.value}T00:00:00`);
            if (Number.isNaN(nextDay.getTime())) {
                targetInput.removeAttribute('min');
                return;
            }

            nextDay.setDate(nextDay.getDate() + 1);
            targetInput.min = nextDay.toISOString().split('T')[0];

            if (targetInput.value && targetInput.value <= blockDateInput.value) {
                targetInput.value = '';
            }
        };

        blockDateInput.addEventListener('change', syncRescheduleState);
        syncRescheduleState();
    })();

    ((root) => {
        const $ = root.jQuery;
        if (!$ || !$.fn || !$.fn.DataTable) {
            return;
        }

        const tableElement = $('#providerWeeklyAvailabilityTable');
        if (!tableElement.length) {
            return;
        }

        tableElement.DataTable({
            ajax: {
                url: '{{ route('provider.availability.weekly.data') }}',
                dataSrc: 'data',
            },
            processing: true,
            paging: true,
            stateSave: true,
            stateDuration: -1,
            pagingType: 'simple_numbers',
            lengthChange: true,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            info: true,
            autoWidth: false,
            ordering: false,
            language: {
                emptyTable: 'No weekly availability saved yet.',
                paginate: {
                    previous: 'Prev',
                    next: 'Next',
                },
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                {
                    data: 'day_label',
                    render: function (data) {
                        return `<span class="font-semibold text-zinc-100">${$('<div>').text(data).html()}</span>`;
                    },
                },
                {
                    data: 'status_label',
                    render: function (data, type, row) {
                        const statusClass = row.is_active
                            ? 'border-emerald-400/40 bg-emerald-500/10 text-emerald-200'
                            : 'border-rose-400/40 bg-rose-500/10 text-rose-200';

                        return `<span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${statusClass}">${$('<div>').text(data).html()}</span>`;
                    },
                },
                {
                    data: 'working_window',
                    render: function (data) {
                        return `<span class="text-zinc-300">${$('<div>').text(data || '-').html()}</span>`;
                    },
                },
                {
                    data: 'break_window',
                    render: function (data) {
                        return `<span class="text-zinc-300">${$('<div>').text(data || 'No break').html()}</span>`;
                    },
                },
                {
                    data: 'slot_duration_label',
                    render: function (data) {
                        return `<span class="text-zinc-300">${$('<div>').text(data).html()}</span>`;
                    },
                },
                {
                    data: 'updated_at',
                    render: function (data, type, row) {
                        if (type === 'sort' || type === 'type') {
                            return row.updated_at_timestamp || 0;
                        }

                        return `<span class="text-zinc-400">${$('<div>').text(data).html()}</span>`;
                    },
                },
            ],
        });
    })(window);
</script>
@endpush





