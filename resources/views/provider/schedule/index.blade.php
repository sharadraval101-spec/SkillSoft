@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        #provider-availability-page .dataTables_wrapper .dataTables_length label,
        #provider-availability-page .dataTables_wrapper .dataTables_filter label,
        #provider-availability-page .dataTables_wrapper .dataTables_info,
        #provider-availability-page .dataTables_wrapper .dataTables_paginate {
            color: #a1a1aa;
            font-size: 0.875rem;
        }

        #provider-availability-page .dataTables_wrapper .dataTables_filter input,
        #provider-availability-page .dataTables_wrapper .dataTables_length select {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.65rem;
            background: rgba(9, 9, 11, 0.55);
            color: #e4e4e7;
            padding: 0.4rem 0.55rem;
        }

        #provider-availability-page .dataTables_wrapper {
            overflow-x: hidden;
        }

        #provider-availability-page table.dataTable {
            width: 100% !important;
        }

        #provider-availability-page table.dataTable th,
        #provider-availability-page table.dataTable td {
            white-space: normal;
            overflow-wrap: anywhere;
            vertical-align: middle;
        }

        #provider-availability-page table.dataTable.no-footer {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        #provider-availability-page table.dataTable thead th {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        #provider-availability-page table.dataTable tbody tr {
            background: transparent;
        }

        #provider-availability-page table.dataTable.stripe tbody tr.odd,
        #provider-availability-page table.dataTable.display tbody tr.odd {
            background-color: rgba(255, 255, 255, 0.01);
        }

        #provider-availability-page .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.6rem;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #d4d4d8 !important;
            background: rgba(9, 9, 11, 0.55) !important;
            padding: 0.3rem 0.7rem;
            margin-left: 0.25rem;
        }

        #provider-availability-page .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: rgba(6, 182, 212, 0.18) !important;
            border-color: rgba(34, 211, 238, 0.45) !important;
            color: #cffafe !important;
        }

        #provider-availability-page .dataTables_wrapper .dataTables_processing {
            border-radius: 0.75rem;
            border: 1px solid rgba(34, 211, 238, 0.35);
            background: rgba(9, 9, 11, 0.88);
            color: #cffafe;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.3);
            animation: providerAvailabilityPulse 1.2s ease-in-out infinite;
        }

        @keyframes providerAvailabilityPulse {
            0%, 100% { opacity: 0.72; }
            50% { opacity: 1; }
        }
    </style>
@endpush

@section('content')
@php
    $inputClass = 'w-full rounded-xl border border-white/15 bg-zinc-950/70 px-3 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 focus:border-cyan-400/50 focus:outline-none';
    $labelClass = 'mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400';
@endphp

<div id="provider-availability-page" class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-white">Availability Management</h1>
                <p class="mt-2 text-sm text-zinc-400">
                    Manage your provider slots with date, time range, block status, and reason.
                </p>
            </div>

            <button type="button" data-modal-open="add-availability-modal" class="smooth-action-btn inline-flex items-center rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-500">
                + Add Availability
            </button>
        </div>
    </section>

    <section class="dashboard-panel">
        <div>
            <table id="providerAvailabilityTable" class="display w-full text-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Blocked</th>
                        <th>Reason</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>

<x-modal id="add-availability-modal" title="Add Availability" max-width="max-w-2xl">
    <form id="addAvailabilityForm" method="POST" action="{{ route('provider.schedule.store') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="addAvailabilityDate" class="{{ $labelClass }}">Date</label>
                <input id="addAvailabilityDate" name="date" type="date" required class="{{ $inputClass }}">
            </div>

            <div>
                <label for="addAvailabilityBlocked" class="{{ $labelClass }}">Blocked</label>
                <select id="addAvailabilityBlocked" name="is_blocked" required class="{{ $inputClass }}">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>

            <div>
                <label for="addAvailabilityStart" class="{{ $labelClass }}">Start Time</label>
                <input id="addAvailabilityStart" name="start_time" type="time" required class="{{ $inputClass }}">
            </div>

            <div>
                <label for="addAvailabilityEnd" class="{{ $labelClass }}">End Time</label>
                <input id="addAvailabilityEnd" name="end_time" type="time" required class="{{ $inputClass }}">
            </div>

            <div id="addAvailabilityReasonWrap" class="hidden sm:col-span-2">
                <label for="addAvailabilityReason" class="{{ $labelClass }}">Reason</label>
                <input id="addAvailabilityReason" name="reason" type="text" maxlength="255" class="{{ $inputClass }}" placeholder="Optional reason for blocking">
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">
                Cancel
            </button>
            <button type="submit" class="smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">
                Create Availability
            </button>
        </div>
    </form>
</x-modal>

<x-modal id="edit-availability-modal" title="Edit Availability" max-width="max-w-2xl">
    <form id="editAvailabilityForm" method="POST" action="" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="editAvailabilityDate" class="{{ $labelClass }}">Date</label>
                <input id="editAvailabilityDate" name="date" type="date" required class="{{ $inputClass }}">
            </div>

            <div>
                <label for="editAvailabilityBlocked" class="{{ $labelClass }}">Blocked</label>
                <select id="editAvailabilityBlocked" name="is_blocked" required class="{{ $inputClass }}">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>

            <div>
                <label for="editAvailabilityStart" class="{{ $labelClass }}">Start Time</label>
                <input id="editAvailabilityStart" name="start_time" type="time" required class="{{ $inputClass }}">
            </div>

            <div>
                <label for="editAvailabilityEnd" class="{{ $labelClass }}">End Time</label>
                <input id="editAvailabilityEnd" name="end_time" type="time" required class="{{ $inputClass }}">
            </div>

            <div id="editAvailabilityReasonWrap" class="hidden sm:col-span-2">
                <label for="editAvailabilityReason" class="{{ $labelClass }}">Reason</label>
                <input id="editAvailabilityReason" name="reason" type="text" maxlength="255" class="{{ $inputClass }}" placeholder="Optional reason for blocking">
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">
                Cancel
            </button>
            <button type="submit" class="smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">
                Save Changes
            </button>
        </div>
    </form>
</x-modal>

<x-modal id="confirm-availability-action-modal" title="Verify Action" max-width="max-w-lg">
    <div class="space-y-4">
        <div>
            <p id="confirmAvailabilityActionTitle" class="text-base font-bold text-white">Please confirm this action</p>
            <p id="confirmAvailabilityActionMessage" class="mt-2 text-sm text-zinc-300">Do you want to continue?</p>
        </div>

        <div class="flex justify-end gap-2 pt-1">
            <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">
                Cancel
            </button>
            <button id="confirmAvailabilityActionButton" type="button" class="smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">
                Confirm
            </button>
        </div>
    </div>
</x-modal>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        (function ($) {
            const tableElement = $('#providerAvailabilityTable');
            if (!tableElement.length) {
                return;
            }

            const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';

            const showNotice = function (message, tone) {
                const colorClass = tone === 'error'
                    ? 'border-rose-500/40 bg-rose-500/20 text-rose-100'
                    : 'border-emerald-500/40 bg-emerald-500/20 text-emerald-100';

                const notice = $(`<div class="fixed right-4 top-4 z-[90] rounded-xl border px-4 py-3 text-sm font-semibold ${colorClass} shadow-xl">${message}</div>`);
                $('body').append(notice);
                setTimeout(function () {
                    notice.fadeOut(220, function () {
                        $(this).remove();
                    });
                }, 2200);
            };

            const extractError = function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const firstField = Object.keys(xhr.responseJSON.errors)[0];
                    if (firstField && xhr.responseJSON.errors[firstField][0]) {
                        return xhr.responseJSON.errors[firstField][0];
                    }
                }

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    return xhr.responseJSON.message;
                }

                return 'Unable to complete request. Please try again.';
            };

            const closeModal = function (modalId) {
                if (window.SSModal && typeof window.SSModal.closeById === 'function') {
                    window.SSModal.closeById(modalId);
                    return;
                }

                const modal = document.getElementById(modalId);
                if (!modal) {
                    return;
                }

                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            const openModal = function (modalId) {
                if (window.SSModal && typeof window.SSModal.openById === 'function') {
                    window.SSModal.openById(modalId);
                    return;
                }

                const modal = document.getElementById(modalId);
                if (!modal) {
                    return;
                }

                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            const setButtonLoading = function (button, isLoading, loadingText) {
                const target = button instanceof $ ? button : $(button);
                if (!target.length) {
                    return;
                }

                if (isLoading) {
                    if (typeof target.data('defaultHtml') === 'undefined') {
                        target.data('defaultHtml', target.html());
                    }

                    target.prop('disabled', true);
                    target.html(`<span class="inline-flex items-center gap-2">
                        <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/25 border-t-white"></span>
                        <span>${loadingText || 'Please wait...'}</span>
                    </span>`);
                    return;
                }

                const original = target.data('defaultHtml');
                if (typeof original !== 'undefined') {
                    target.html(original);
                }
                target.prop('disabled', false);
            };

            const syncReasonVisibility = function (prefix) {
                const blockedField = $(`#${prefix}AvailabilityBlocked`);
                const reasonWrap = $(`#${prefix}AvailabilityReasonWrap`);
                const reasonField = $(`#${prefix}AvailabilityReason`);
                const isBlocked = String(blockedField.val()) === '1';

                if (isBlocked) {
                    reasonWrap.removeClass('hidden');
                } else {
                    reasonWrap.addClass('hidden');
                    reasonField.val('');
                }
            };

            $('#addAvailabilityBlocked').on('change', function () {
                syncReasonVisibility('add');
            });

            $('#editAvailabilityBlocked').on('change', function () {
                syncReasonVisibility('edit');
            });

            syncReasonVisibility('add');
            syncReasonVisibility('edit');

            let confirmHandler = null;
            const confirmTitle = $('#confirmAvailabilityActionTitle');
            const confirmMessage = $('#confirmAvailabilityActionMessage');
            const confirmButton = $('#confirmAvailabilityActionButton');

            const askConfirmation = function (options) {
                confirmTitle.text(options.title || 'Please confirm this action');
                confirmMessage.text(options.message || 'Do you want to continue?');
                confirmButton.text(options.confirmText || 'Confirm');

                const buttonClass = options.tone === 'danger'
                    ? 'smooth-action-btn rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500'
                    : 'smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500';
                confirmButton.attr('class', buttonClass);

                confirmHandler = typeof options.onConfirm === 'function' ? options.onConfirm : null;
                openModal('confirm-availability-action-modal');
            };

            $(document).off('click.providerAvailabilityConfirm').on('click.providerAvailabilityConfirm', '#confirmAvailabilityActionButton', function () {
                const action = confirmHandler;
                confirmHandler = null;
                closeModal('confirm-availability-action-modal');

                if (typeof action === 'function') {
                    action();
                }
            });

            $(document).off('click.providerAvailabilityConfirmCancel').on('click.providerAvailabilityConfirmCancel', '#confirm-availability-action-modal [data-modal-hide]', function () {
                confirmHandler = null;
            });

            $(document).off('keydown.providerAvailabilityConfirmEscape').on('keydown.providerAvailabilityConfirmEscape', function (event) {
                if (event.key === 'Escape') {
                    confirmHandler = null;
                }
            });

            const table = tableElement.DataTable({
                ajax: {
                    url: '{{ $availabilityDataUrl }}',
                    dataSrc: 'data'
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
                order: [[6, 'desc']],
                language: {
                    emptyTable: 'No availability slots found.',
                    paginate: {
                        previous: 'Prev',
                        next: 'Next'
                    }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'date',
                        render: function (data) {
                            return `<span class="text-zinc-200">${$('<div>').text(data).html()}</span>`;
                        }
                    },
                    {
                        data: 'start_time',
                        render: function (data) {
                            return `<span class="text-zinc-300">${$('<div>').text(data).html()}</span>`;
                        }
                    },
                    {
                        data: 'end_time',
                        render: function (data) {
                            return `<span class="text-zinc-300">${$('<div>').text(data).html()}</span>`;
                        }
                    },
                    {
                        data: 'blocked_label',
                        render: function (data, type, row) {
                            const statusClass = row.is_blocked
                                ? 'border-rose-400/40 bg-rose-500/10 text-rose-200'
                                : 'border-emerald-400/40 bg-emerald-500/10 text-emerald-200';

                            return `<span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${statusClass}">${$('<div>').text(data).html()}</span>`;
                        }
                    },
                    {
                        data: 'reason',
                        render: function (data, type, row) {
                            const fullReason = row.full_reason || '';
                            const text = data || '-';

                            if (type === 'display' && fullReason) {
                                return `<span class="text-zinc-300" title="${$('<div>').text(fullReason).html()}">${$('<div>').text(text).html()}</span>`;
                            }

                            return `<span class="text-zinc-300">${$('<div>').text(text).html()}</span>`;
                        }
                    },
                    {
                        data: 'created_at',
                        render: function (data, type, row) {
                            if (type === 'sort' || type === 'type') {
                                return row.created_at_timestamp || 0;
                            }

                            return `<span class="text-zinc-400">${$('<div>').text(data).html()}</span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            const statusBtnClass = row.is_blocked
                                ? 'border-rose-400/35 text-rose-200 hover:bg-rose-500/10'
                                : 'border-emerald-400/30 text-emerald-200 hover:bg-emerald-500/10';
                            const statusBtnLabel = row.is_blocked ? 'Blocked' : 'Available';
                            const disabledAttr = row.is_locked ? 'disabled' : '';
                            const lockedStatusClass = row.is_locked ? 'opacity-50 cursor-not-allowed border-zinc-600/35 text-zinc-500 hover:bg-transparent' : statusBtnClass;
                            const editClass = row.is_locked
                                ? 'opacity-50 cursor-not-allowed border-zinc-600/35 text-zinc-500 hover:bg-transparent'
                                : 'border-cyan-400/30 text-cyan-200 hover:bg-cyan-500/10';
                            const deleteClass = row.is_locked
                                ? 'opacity-50 cursor-not-allowed border-zinc-600/35 text-zinc-500 hover:bg-transparent'
                                : 'border-rose-400/35 text-rose-200 hover:bg-rose-500/10';

                            return `<div class="flex flex-wrap gap-2">
                                <button type="button" class="js-toggle-block smooth-action-btn rounded-lg border px-3 py-1.5 text-xs font-semibold ${lockedStatusClass}" data-url="${row.toggle_block_url}" ${disabledAttr}>
                                    ${statusBtnLabel}
                                </button>
                                <button type="button" class="js-edit-availability smooth-action-btn rounded-lg border px-3 py-1.5 text-xs font-semibold ${editClass}" data-modal-open="edit-availability-modal" ${disabledAttr}>
                                    Edit
                                </button>
                                <button type="button" class="js-delete-availability smooth-action-btn rounded-lg border px-3 py-1.5 text-xs font-semibold ${deleteClass}" data-url="${row.delete_url}" ${disabledAttr}>
                                    Delete
                                </button>
                            </div>`;
                        }
                    }
                ]
            });

            tableElement.on('click', '.js-edit-availability', function () {
                const button = $(this);
                if (button.prop('disabled')) {
                    return;
                }

                const rowData = table.row(button.closest('tr')).data();
                if (!rowData) {
                    return;
                }

                $('#editAvailabilityForm').attr('action', rowData.update_url);
                $('#editAvailabilityDate').val(rowData.date_input || '');
                $('#editAvailabilityStart').val(rowData.start_time_input || '');
                $('#editAvailabilityEnd').val(rowData.end_time_input || '');
                $('#editAvailabilityBlocked').val(rowData.is_blocked ? '1' : '0');
                $('#editAvailabilityReason').val(rowData.full_reason || '');
                syncReasonVisibility('edit');
            });

            $(document).off('submit.providerAvailabilityCreate').on('submit.providerAvailabilityCreate', '#addAvailabilityForm', function (event) {
                event.preventDefault();
                const form = $(this);

                askConfirmation({
                    title: 'Verify Add Availability',
                    message: 'Create this availability slot now?',
                    confirmText: 'Create Slot',
                    tone: 'primary',
                    onConfirm: function () {
                        const submitButton = form.find('button[type="submit"]');
                        setButtonLoading(submitButton, true, 'Creating...');

                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: form.serialize(),
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        }).done(function (response) {
                            form[0].reset();
                            syncReasonVisibility('add');
                            closeModal('add-availability-modal');
                            table.ajax.reload(null, false);
                            showNotice(response.message || 'Availability slot created successfully.', 'success');
                        }).fail(function (xhr) {
                            showNotice(extractError(xhr), 'error');
                        }).always(function () {
                            setButtonLoading(submitButton, false);
                        });
                    }
                });
            });

            $(document).off('submit.providerAvailabilityUpdate').on('submit.providerAvailabilityUpdate', '#editAvailabilityForm', function (event) {
                event.preventDefault();
                const form = $(this);

                askConfirmation({
                    title: 'Verify Edit Availability',
                    message: 'Save changes for this availability slot?',
                    confirmText: 'Save Changes',
                    tone: 'primary',
                    onConfirm: function () {
                        const submitButton = form.find('button[type="submit"]');
                        setButtonLoading(submitButton, true, 'Saving...');

                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: form.serialize(),
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        }).done(function (response) {
                            closeModal('edit-availability-modal');
                            table.ajax.reload(null, false);
                            showNotice(response.message || 'Availability slot updated successfully.', 'success');
                        }).fail(function (xhr) {
                            showNotice(extractError(xhr), 'error');
                        }).always(function () {
                            setButtonLoading(submitButton, false);
                        });
                    }
                });
            });

            tableElement.on('click', '.js-toggle-block', function () {
                const button = $(this);
                if (button.prop('disabled')) {
                    return;
                }

                const url = button.data('url');
                const rowData = table.row(button.closest('tr')).data();
                if (!url) {
                    return;
                }

                const nextStatus = rowData && rowData.is_blocked ? 'available' : 'blocked';
                askConfirmation({
                    title: 'Verify Block Status',
                    message: `Mark this slot as ${nextStatus}?`,
                    confirmText: 'Update Status',
                    tone: 'danger',
                    onConfirm: function () {
                        setButtonLoading(button, true, 'Updating...');

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                _token: csrfToken,
                                _method: 'PATCH'
                            },
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).done(function (response) {
                            table.ajax.reload(null, false);
                            showNotice(response.message || 'Slot status updated.', 'success');
                        }).fail(function (xhr) {
                            showNotice(extractError(xhr), 'error');
                        }).always(function () {
                            setButtonLoading(button, false);
                        });
                    }
                });
            });

            tableElement.on('click', '.js-delete-availability', function () {
                const button = $(this);
                if (button.prop('disabled')) {
                    return;
                }

                const url = button.data('url');
                if (!url) {
                    return;
                }

                askConfirmation({
                    title: 'Verify Delete',
                    message: 'Delete this availability slot? This action cannot be undone.',
                    confirmText: 'Delete Slot',
                    tone: 'danger',
                    onConfirm: function () {
                        setButtonLoading(button, true, 'Deleting...');

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                _token: csrfToken,
                                _method: 'DELETE'
                            },
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).done(function (response) {
                            table.ajax.reload(null, false);
                            showNotice(response.message || 'Availability slot deleted successfully.', 'success');
                        }).fail(function (xhr) {
                            showNotice(extractError(xhr), 'error');
                        }).always(function () {
                            setButtonLoading(button, false);
                        });
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
