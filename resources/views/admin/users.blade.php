@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        #admin-users-page .dataTables_wrapper .dataTables_length label,
        #admin-users-page .dataTables_wrapper .dataTables_filter label,
        #admin-users-page .dataTables_wrapper .dataTables_info,
        #admin-users-page .dataTables_wrapper .dataTables_paginate {
            color: #a1a1aa;
            font-size: 0.875rem;
        }

        #admin-users-page .dataTables_wrapper .dataTables_filter input,
        #admin-users-page .dataTables_wrapper .dataTables_length select {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.65rem;
            background: rgba(9, 9, 11, 0.55);
            color: #e4e4e7;
            padding: 0.4rem 0.55rem;
        }

        #admin-users-page table.dataTable.no-footer {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        #admin-users-page table.dataTable thead th {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        #admin-users-page table.dataTable tbody tr {
            background: transparent;
        }

        #admin-users-page table.dataTable.stripe tbody tr.odd,
        #admin-users-page table.dataTable.display tbody tr.odd {
            background-color: rgba(255, 255, 255, 0.01);
        }

        #admin-users-page .dataTables_wrapper {
            overflow-x: hidden;
        }

        #admin-users-page table.dataTable {
            width: 100% !important;
        }

        #admin-users-page table.dataTable th,
        #admin-users-page table.dataTable td {
            white-space: normal;
            overflow-wrap: anywhere;
            vertical-align: middle;
        }

        #admin-users-page .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.6rem;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #d4d4d8 !important;
            background: rgba(9, 9, 11, 0.55) !important;
            padding: 0.3rem 0.7rem;
            margin-left: 0.25rem;
        }

        #admin-users-page .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: rgba(6, 182, 212, 0.18) !important;
            border-color: rgba(34, 211, 238, 0.45) !important;
            color: #cffafe !important;
        }

        #admin-users-page .dataTables_wrapper .dataTables_processing {
            border-radius: 0.75rem;
            border: 1px solid rgba(34, 211, 238, 0.35);
            background: rgba(9, 9, 11, 0.88);
            color: #cffafe;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.3);
            animation: adminUsersPulse 1.2s ease-in-out infinite;
        }

        @keyframes adminUsersPulse {
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

<div id="admin-users-page" class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-white">User Management</h1>
                <p class="mt-2 text-sm text-zinc-400">
                    Create users and manage active status, role, and profile details from one table.
                </p>
            </div>

            <button type="button" data-modal-open="add-user-modal" class="smooth-action-btn inline-flex items-center rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-500">
                + Add User
            </button>
        </div>
    </section>

    <section class="dashboard-panel">
        <div>
            <table id="adminUsersTable" class="display w-full text-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>

<x-modal id="add-user-modal" title="Add User" max-width="max-w-2xl">
    <form id="addUserForm" method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="addUserName" class="{{ $labelClass }}">Name</label>
                <input id="addUserName" name="name" type="text" required class="{{ $inputClass }}">
            </div>

            <div>
                <label for="addUserEmail" class="{{ $labelClass }}">Email</label>
                <input id="addUserEmail" name="email" type="email" required class="{{ $inputClass }}">
            </div>

            <div>
                <label for="addUserPassword" class="{{ $labelClass }}">Password</label>
                <input id="addUserPassword" name="password" type="password" required class="{{ $inputClass }}">
            </div>

            <div>
                <label for="addUserPasswordConfirmation" class="{{ $labelClass }}">Confirm Password</label>
                <input id="addUserPasswordConfirmation" name="password_confirmation" type="password" required class="{{ $inputClass }}">
            </div>

            <div>
                <label for="addUserRole" class="{{ $labelClass }}">Role</label>
                <select id="addUserRole" name="role" required class="{{ $inputClass }}">
                    <option value="{{ \App\Models\User::ROLE_CUSTOMER }}">Customer</option>
                    <option value="{{ \App\Models\User::ROLE_PROVIDER }}">Provider</option>
                    <option value="{{ \App\Models\User::ROLE_ADMIN }}">Admin</option>
                </select>
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-zinc-950/40 px-3 py-2 text-sm text-zinc-200">
                    <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-white/20 bg-zinc-900 text-cyan-500">
                    Active User
                </label>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">
                Cancel
            </button>
            <button type="submit" class="smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">
                Create User
            </button>
        </div>
    </form>
</x-modal>

<x-modal id="edit-user-modal" title="Edit User" max-width="max-w-2xl">
    <form id="editUserForm" method="POST" action="" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="editUserName" class="{{ $labelClass }}">Name</label>
                <input id="editUserName" name="name" type="text" required class="{{ $inputClass }}">
            </div>

            <div>
                <label for="editUserEmail" class="{{ $labelClass }}">Email</label>
                <input id="editUserEmail" name="email" type="email" required class="{{ $inputClass }}">
            </div>

            <div>
                <label for="editUserPassword" class="{{ $labelClass }}">New Password (Optional)</label>
                <input id="editUserPassword" name="password" type="password" class="{{ $inputClass }}">
            </div>

            <div>
                <label for="editUserPasswordConfirmation" class="{{ $labelClass }}">Confirm New Password</label>
                <input id="editUserPasswordConfirmation" name="password_confirmation" type="password" class="{{ $inputClass }}">
            </div>

            <div>
                <label for="editUserRole" class="{{ $labelClass }}">Role</label>
                <select id="editUserRole" name="role" required class="{{ $inputClass }}">
                    <option value="{{ \App\Models\User::ROLE_CUSTOMER }}">Customer</option>
                    <option value="{{ \App\Models\User::ROLE_PROVIDER }}">Provider</option>
                    <option value="{{ \App\Models\User::ROLE_ADMIN }}">Admin</option>
                </select>
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-zinc-950/40 px-3 py-2 text-sm text-zinc-200">
                    <input id="editUserActive" type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-white/20 bg-zinc-900 text-cyan-500">
                    Active User
                </label>
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

<x-modal id="confirm-action-modal" title="Verify Action" max-width="max-w-lg">
    <div class="space-y-4">
        <div>
            <p id="confirmActionTitle" class="text-base font-bold text-white">Please confirm this action</p>
            <p id="confirmActionMessage" class="mt-2 text-sm text-zinc-300">Do you want to continue?</p>
        </div>

        <div class="flex justify-end gap-2 pt-1">
            <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">
                Cancel
            </button>
            <button id="confirmActionButton" type="button" class="smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">
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
            const tableElement = $('#adminUsersTable');
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
                const hasOpenModal = !!document.querySelector('[data-modal-root]:not(.hidden)');
                document.body.classList.toggle('overflow-hidden', hasOpenModal);
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

            let confirmHandler = null;
            const confirmTitle = $('#confirmActionTitle');
            const confirmMessage = $('#confirmActionMessage');
            const confirmButton = $('#confirmActionButton');

            const askConfirmation = function (options) {
                confirmTitle.text(options.title || 'Please confirm this action');
                confirmMessage.text(options.message || 'Do you want to continue?');
                confirmButton.text(options.confirmText || 'Confirm');

                const buttonClass = options.tone === 'danger'
                    ? 'smooth-action-btn rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500'
                    : 'smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500';
                confirmButton.attr('class', buttonClass);

                confirmHandler = typeof options.onConfirm === 'function' ? options.onConfirm : null;
                openModal('confirm-action-modal');
            };

            $(document).off('click.adminConfirmAction').on('click.adminConfirmAction', '#confirmActionButton', function () {
                const action = confirmHandler;
                confirmHandler = null;
                closeModal('confirm-action-modal');

                if (typeof action === 'function') {
                    action();
                }
            });

            $(document).off('click.adminConfirmCancel').on('click.adminConfirmCancel', '#confirm-action-modal [data-modal-hide]', function () {
                confirmHandler = null;
            });

            $(document).off('keydown.adminConfirmEscape').on('keydown.adminConfirmEscape', function (event) {
                if (event.key === 'Escape') {
                    confirmHandler = null;
                }
            });

            const table = tableElement.DataTable({
                ajax: {
                    url: '{{ $usersDataUrl }}',
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
                order: [[5, 'desc']],
                language: {
                    emptyTable: 'No users found.',
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
                        data: 'name',
                        render: function (data) {
                            return `<span class="font-semibold text-zinc-100">${$('<div>').text(data).html()}</span>`;
                        }
                    },
                    {
                        data: 'email',
                        render: function (data) {
                            return `<span class="text-zinc-300">${$('<div>').text(data).html()}</span>`;
                        }
                    },
                    {
                        data: 'role_label',
                        render: function (data, type, row) {
                            const roleClass = row.role === {{ \App\Models\User::ROLE_ADMIN }}
                                ? 'border-cyan-400/40 bg-cyan-500/10 text-cyan-200'
                                : row.role === {{ \App\Models\User::ROLE_PROVIDER }}
                                    ? 'border-amber-400/40 bg-amber-500/10 text-amber-200'
                                    : 'border-zinc-500/40 bg-zinc-500/10 text-zinc-200';

                            return `<span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${roleClass}">${$('<div>').text(data).html()}</span>`;
                        }
                    },
                    {
                        data: 'status_label',
                        render: function (data, type, row) {
                            const statusClass = row.is_active
                                ? 'border-emerald-400/40 bg-emerald-500/10 text-emerald-200'
                                : 'border-rose-400/40 bg-rose-500/10 text-rose-200';

                            return `<span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${statusClass}">${$('<div>').text(data).html()}</span>`;
                        }
                    },
                    {
                        data: 'joined_at',
                        render: function (data, type, row) {
                            if (type === 'sort' || type === 'type') {
                                return row.joined_at_timestamp || 0;
                            }

                            return `<span class="text-zinc-400">${$('<div>').text(data).html()}</span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            const activeClass = row.is_active
                                ? 'border-emerald-400/30 text-emerald-200 hover:bg-emerald-500/10'
                                : 'border-zinc-500/35 text-zinc-300 hover:bg-zinc-500/10';

                            const activeText = row.is_active ? 'Active' : 'Inactive';

                            return `<div class="flex flex-wrap gap-2">
                                <button type="button" class="js-toggle-active smooth-action-btn rounded-lg border px-3 py-1.5 text-xs font-semibold ${activeClass}" data-url="${row.toggle_active_url}">
                                    ${activeText}
                                </button>
                                <button type="button" class="js-edit-user smooth-action-btn rounded-lg border border-cyan-400/30 px-3 py-1.5 text-xs font-semibold text-cyan-200 hover:bg-cyan-500/10" data-modal-open="edit-user-modal">
                                    Edit
                                </button>
                                <button type="button" class="js-delete-user smooth-action-btn rounded-lg border border-rose-400/35 px-3 py-1.5 text-xs font-semibold text-rose-200 hover:bg-rose-500/10" data-url="${row.delete_url}">
                                    Delete
                                </button>
                            </div>`;
                        }
                    }
                ]
            });

            tableElement.on('click', '.js-edit-user', function () {
                const rowData = table.row($(this).closest('tr')).data();
                if (!rowData) {
                    return;
                }

                $('#editUserForm').attr('action', rowData.update_url);
                $('#editUserName').val(rowData.name);
                $('#editUserEmail').val(rowData.email);
                $('#editUserRole').val(String(rowData.role));
                $('#editUserActive').prop('checked', !!rowData.is_active);
                $('#editUserPassword').val('');
                $('#editUserPasswordConfirmation').val('');
            });

            $(document).off('submit.adminUserCreate').on('submit.adminUserCreate', '#addUserForm', function (event) {
                event.preventDefault();
                const form = $(this);
                askConfirmation({
                    title: 'Verify Add User',
                    message: 'Create this user now?',
                    confirmText: 'Create User',
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
                            closeModal('add-user-modal');
                            table.ajax.reload(null, false);
                            showNotice(response.message || 'User created successfully.', 'success');
                        }).fail(function (xhr) {
                            showNotice(extractError(xhr), 'error');
                        }).always(function () {
                            setButtonLoading(submitButton, false);
                        });
                    }
                });
            });

            $(document).off('submit.adminUserUpdate').on('submit.adminUserUpdate', '#editUserForm', function (event) {
                event.preventDefault();
                const form = $(this);
                askConfirmation({
                    title: 'Verify Edit User',
                    message: 'Save these changes for this user?',
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
                            closeModal('edit-user-modal');
                            table.ajax.reload(null, false);
                            showNotice(response.message || 'User updated successfully.', 'success');
                        }).fail(function (xhr) {
                            showNotice(extractError(xhr), 'error');
                        }).always(function () {
                            setButtonLoading(submitButton, false);
                        });
                    }
                });
            });

            tableElement.on('click', '.js-toggle-active', function () {
                const button = $(this);
                const url = button.data('url');
                const rowData = table.row(button.closest('tr')).data();
                if (!url) {
                    return;
                }

                const nextStateText = rowData && rowData.is_active ? 'inactive' : 'active';
                askConfirmation({
                    title: 'Verify Status Change',
                    message: `Mark this user as ${nextStateText}?`,
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
                            showNotice(response.message || 'User status updated.', 'success');
                        }).fail(function (xhr) {
                            showNotice(extractError(xhr), 'error');
                        }).always(function () {
                            setButtonLoading(button, false);
                        });
                    }
                });
            });

            tableElement.on('click', '.js-delete-user', function () {
                const button = $(this);
                const url = button.data('url');
                const rowData = table.row(button.closest('tr')).data();
                if (!url) {
                    return;
                }

                const userName = rowData && rowData.name ? rowData.name : 'this user';
                askConfirmation({
                    title: 'Verify Delete',
                    message: `Delete ${userName}? This action cannot be undone.`,
                    confirmText: 'Delete User',
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
                            showNotice(response.message || 'User deleted successfully.', 'success');
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
