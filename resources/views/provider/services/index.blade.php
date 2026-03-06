@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        #provider-services-page .dataTables_wrapper .dataTables_length label,
        #provider-services-page .dataTables_wrapper .dataTables_filter label,
        #provider-services-page .dataTables_wrapper .dataTables_info,
        #provider-services-page .dataTables_wrapper .dataTables_paginate {
            color: #a1a1aa;
            font-size: 0.875rem;
        }

        #provider-services-page .dataTables_wrapper .dataTables_filter input,
        #provider-services-page .dataTables_wrapper .dataTables_length select {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.65rem;
            background: rgba(9, 9, 11, 0.55);
            color: #e4e4e7;
            padding: 0.4rem 0.55rem;
        }

        #provider-services-page .dataTables_wrapper {
            overflow-x: hidden;
        }

        #provider-services-page table.dataTable {
            width: 100% !important;
        }

        #provider-services-page table.dataTable th,
        #provider-services-page table.dataTable td {
            white-space: normal;
            overflow-wrap: anywhere;
            vertical-align: middle;
        }

        #provider-services-page table.dataTable.no-footer {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        #provider-services-page table.dataTable thead th {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        #provider-services-page table.dataTable tbody tr {
            background: transparent;
        }

        #provider-services-page table.dataTable.stripe tbody tr.odd,
        #provider-services-page table.dataTable.display tbody tr.odd {
            background-color: rgba(255, 255, 255, 0.01);
        }

        #provider-services-page .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.6rem;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #d4d4d8 !important;
            background: rgba(9, 9, 11, 0.55) !important;
            padding: 0.3rem 0.7rem;
            margin-left: 0.25rem;
        }

        #provider-services-page .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: rgba(6, 182, 212, 0.18) !important;
            border-color: rgba(34, 211, 238, 0.45) !important;
            color: #cffafe !important;
        }

        #provider-services-page .dataTables_wrapper .dataTables_processing {
            border-radius: 0.75rem;
            border: 1px solid rgba(34, 211, 238, 0.35);
            background: rgba(9, 9, 11, 0.88);
            color: #cffafe;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.3);
            animation: providerServicesPulse 1.2s ease-in-out infinite;
        }

        #provider-services-page .service-thumb {
            height: 48px;
            width: 48px;
            border-radius: 0.75rem;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        @keyframes providerServicesPulse {
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

<div id="provider-services-page" class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-white">Service Management</h1>
                <p class="mt-2 text-sm text-zinc-400">Create and manage your services in one table.</p>
            </div>
            <button type="button" data-modal-open="add-service-modal" class="smooth-action-btn inline-flex items-center rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-500">
                + Add Service
            </button>
        </div>
    </section>

    <section class="dashboard-panel">
        <div>
            <table id="providerServicesTable" class="display w-full text-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Type</th>
                        <th>Max</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>

<x-modal id="add-service-modal" title="Add Service" max-width="max-w-3xl">
    <form id="addServiceForm" method="POST" action="{{ route('provider.services.store') }}" class="space-y-4" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="addServiceName" class="{{ $labelClass }}">Service Name</label>
                <input id="addServiceName" name="name" type="text" required class="{{ $inputClass }}">
            </div>
            <div>
                <label for="addServiceCategory" class="{{ $labelClass }}">Category</label>
                <select id="addServiceCategory" name="category_id" required class="{{ $inputClass }}">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="addServicePrice" class="{{ $labelClass }}">Price</label>
                <input id="addServicePrice" name="price" type="number" step="0.01" min="0" required class="{{ $inputClass }}">
            </div>
            <div>
                <label for="addServiceDuration" class="{{ $labelClass }}">Duration (Minutes)</label>
                <input id="addServiceDuration" name="duration_minutes" type="number" min="1" required class="{{ $inputClass }}">
            </div>
            <div>
                <label for="addServiceType" class="{{ $labelClass }}">Type</label>
                <select id="addServiceType" name="type" required class="{{ $inputClass }}">
                    <option value="1-on-1">1-on-1</option>
                    <option value="group">Group</option>
                </select>
            </div>
            <div id="addServiceMaxWrap" class="hidden">
                <label for="addServiceMax" class="{{ $labelClass }}">Max Customers</label>
                <input id="addServiceMax" name="max_customers" type="number" min="2" class="{{ $inputClass }}">
            </div>
            <div>
                <label for="addServiceStatus" class="{{ $labelClass }}">Status</label>
                <select id="addServiceStatus" name="status" required class="{{ $inputClass }}">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="addServiceDescription" class="{{ $labelClass }}">Description</label>
                <textarea id="addServiceDescription" name="description" rows="3" class="{{ $inputClass }}"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label for="addServiceImage" class="{{ $labelClass }}">Service Image</label>
                <input id="addServiceImage" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="{{ $inputClass }}">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">Cancel</button>
            <button type="submit" class="smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">Create Service</button>
        </div>
    </form>
</x-modal>

<x-modal id="edit-service-modal" title="Edit Service" max-width="max-w-3xl">
    <form id="editServiceForm" method="POST" action="" class="space-y-4" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="editServiceName" class="{{ $labelClass }}">Service Name</label>
                <input id="editServiceName" name="name" type="text" required class="{{ $inputClass }}">
            </div>
            <div>
                <label for="editServiceCategory" class="{{ $labelClass }}">Category</label>
                <select id="editServiceCategory" name="category_id" required class="{{ $inputClass }}">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="editServicePrice" class="{{ $labelClass }}">Price</label>
                <input id="editServicePrice" name="price" type="number" step="0.01" min="0" required class="{{ $inputClass }}">
            </div>
            <div>
                <label for="editServiceDuration" class="{{ $labelClass }}">Duration (Minutes)</label>
                <input id="editServiceDuration" name="duration_minutes" type="number" min="1" required class="{{ $inputClass }}">
            </div>
            <div>
                <label for="editServiceType" class="{{ $labelClass }}">Type</label>
                <select id="editServiceType" name="type" required class="{{ $inputClass }}">
                    <option value="1-on-1">1-on-1</option>
                    <option value="group">Group</option>
                </select>
            </div>
            <div id="editServiceMaxWrap" class="hidden">
                <label for="editServiceMax" class="{{ $labelClass }}">Max Customers</label>
                <input id="editServiceMax" name="max_customers" type="number" min="2" class="{{ $inputClass }}">
            </div>
            <div>
                <label for="editServiceStatus" class="{{ $labelClass }}">Status</label>
                <select id="editServiceStatus" name="status" required class="{{ $inputClass }}">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="editServiceDescription" class="{{ $labelClass }}">Description</label>
                <textarea id="editServiceDescription" name="description" rows="3" class="{{ $inputClass }}"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label for="editServiceImage" class="{{ $labelClass }}">New Service Image (Optional)</label>
                <input id="editServiceImage" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="{{ $inputClass }}">
            </div>
            <div id="editServiceCurrentImageWrap" class="hidden sm:col-span-2 rounded-xl border border-white/10 bg-zinc-950/50 p-3">
                <p class="text-xs uppercase tracking-[0.16em] text-zinc-500">Current Image</p>
                <img id="editServiceCurrentImage" src="" alt="Current service image" class="mt-2 h-16 w-16 rounded-xl border border-white/10 object-cover">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">Cancel</button>
            <button type="submit" class="smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">Save Changes</button>
        </div>
    </form>
</x-modal>

<x-modal id="confirm-service-action-modal" title="Verify Action" max-width="max-w-lg">
    <div class="space-y-4">
        <div>
            <p id="confirmServiceTitle" class="text-base font-bold text-white">Please confirm this action</p>
            <p id="confirmServiceMessage" class="mt-2 text-sm text-zinc-300">Do you want to continue?</p>
        </div>
        <div class="flex justify-end gap-2 pt-1">
            <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">Cancel</button>
            <button id="confirmServiceButton" type="button" class="smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">Confirm</button>
        </div>
    </div>
</x-modal>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        (function ($) {
            const tableElement = $('#providerServicesTable');
            if (!tableElement.length) {
                return;
            }

            const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';

            const notify = function (message, tone) {
                const colorClass = tone === 'error' ? 'border-rose-500/40 bg-rose-500/20 text-rose-100' : 'border-emerald-500/40 bg-emerald-500/20 text-emerald-100';
                const notice = $(`<div class="fixed right-4 top-4 z-[90] rounded-xl border px-4 py-3 text-sm font-semibold ${colorClass} shadow-xl">${message}</div>`);
                $('body').append(notice);
                setTimeout(function () { notice.fadeOut(220, function () { $(this).remove(); }); }, 2200);
            };

            const extractError = function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const key = Object.keys(xhr.responseJSON.errors)[0];
                    if (key && xhr.responseJSON.errors[key][0]) return xhr.responseJSON.errors[key][0];
                }
                return xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to complete request. Please try again.';
            };

            const openModal = function (id) {
                if (window.SSModal && window.SSModal.openById) return window.SSModal.openById(id);
                const modal = document.getElementById(id);
                if (!modal) return;
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = function (id) {
                if (window.SSModal && window.SSModal.closeById) return window.SSModal.closeById(id);
                const modal = document.getElementById(id);
                if (!modal) return;
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            const setLoading = function (button, isLoading, text) {
                const target = button instanceof $ ? button : $(button);
                if (!target.length) return;
                if (isLoading) {
                    if (typeof target.data('defaultHtml') === 'undefined') target.data('defaultHtml', target.html());
                    target.prop('disabled', true).html(`<span class="inline-flex items-center gap-2"><span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/25 border-t-white"></span><span>${text || 'Please wait...'}</span></span>`);
                    return;
                }
                if (typeof target.data('defaultHtml') !== 'undefined') target.html(target.data('defaultHtml'));
                target.prop('disabled', false);
            };

            const syncMax = function (typeSel, wrapSel, inputSel) {
                const typeVal = $(typeSel).val();
                if (typeVal === 'group') {
                    $(wrapSel).removeClass('hidden');
                    $(inputSel).prop('required', true);
                    if (!$(inputSel).val()) $(inputSel).val(2);
                } else {
                    $(wrapSel).addClass('hidden');
                    $(inputSel).prop('required', false).val('');
                }
            };
            $('#addServiceType').on('change', function () { syncMax('#addServiceType', '#addServiceMaxWrap', '#addServiceMax'); });
            $('#editServiceType').on('change', function () { syncMax('#editServiceType', '#editServiceMaxWrap', '#editServiceMax'); });
            syncMax('#addServiceType', '#addServiceMaxWrap', '#addServiceMax');
            syncMax('#editServiceType', '#editServiceMaxWrap', '#editServiceMax');

            const resetEditImagePreview = function () {
                $('#editServiceCurrentImageWrap').addClass('hidden');
                $('#editServiceCurrentImage').attr('src', '');
            };

            let confirmHandler = null;
            const askConfirm = function (title, message, confirmText, tone, callback) {
                $('#confirmServiceTitle').text(title || 'Please confirm this action');
                $('#confirmServiceMessage').text(message || 'Do you want to continue?');
                $('#confirmServiceButton').text(confirmText || 'Confirm').attr(
                    'class',
                    tone === 'danger'
                        ? 'smooth-action-btn rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500'
                        : 'smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500'
                );
                confirmHandler = typeof callback === 'function' ? callback : null;
                openModal('confirm-service-action-modal');
            };

            $(document).off('click.providerServiceConfirm').on('click.providerServiceConfirm', '#confirmServiceButton', function () {
                const action = confirmHandler;
                confirmHandler = null;
                closeModal('confirm-service-action-modal');
                if (typeof action === 'function') action();
            });

            $(document).off('click.providerServiceConfirmCancel').on('click.providerServiceConfirmCancel', '#confirm-service-action-modal [data-modal-hide]', function () {
                confirmHandler = null;
            });

            $(document).off('keydown.providerServiceConfirmEscape').on('keydown.providerServiceConfirmEscape', function (event) {
                if (event.key === 'Escape') {
                    confirmHandler = null;
                }
            });

            const table = tableElement.DataTable({
                ajax: { url: '{{ $servicesDataUrl }}', dataSrc: 'data' },
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
                order: [[9, 'desc']],
                language: {
                    emptyTable: 'No services found.',
                    paginate: {
                        previous: 'Prev',
                        next: 'Next'
                    }
                },
                columns: [
                    { data: null, orderable: false, searchable: false, render: function (d, t, r, m) { return m.row + m.settings._iDisplayStart + 1; } },
                    {
                        data: 'image_url',
                        orderable: false,
                        searchable: false,
                        render: function (d, t, r) {
                            if (!d) {
                                return '<div class="service-thumb flex items-center justify-center bg-white/5 text-[11px] text-zinc-400">No Img</div>';
                            }
                            const name = $('<div>').text(r.name || 'Service').html();
                            return `<img src="${d}" alt="${name}" class="service-thumb">`;
                        }
                    },
                    { data: 'name', render: function (d) { return `<span class="font-semibold text-zinc-100">${$('<div>').text(d).html()}</span>`; } },
                    { data: 'category_name', render: function (d) { return `<span class="text-zinc-300">${$('<div>').text(d).html()}</span>`; } },
                    { data: 'price', render: function (d, t, r) { return (t === 'sort' || t === 'type') ? (r.price_value || 0) : `<span class="text-zinc-200">Rs. ${$('<div>').text(d).html()}</span>`; } },
                    { data: 'duration_minutes', render: function (d) { return `<span class="text-zinc-300">${$('<div>').text(String(d) + ' min').html()}</span>`; } },
                    { data: 'type', render: function (d) { const cls = d === 'group' ? 'border-amber-400/40 bg-amber-500/10 text-amber-200' : 'border-cyan-400/40 bg-cyan-500/10 text-cyan-200'; return `<span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${cls}">${$('<div>').text(d).html()}</span>`; } },
                    { data: 'max_customers_label', render: function (d) { return `<span class="text-zinc-300">${$('<div>').text(d).html()}</span>`; } },
                    { data: 'status_label', render: function (d, t, r) { const cls = r.status === 'active' ? 'border-emerald-400/40 bg-emerald-500/10 text-emerald-200' : 'border-rose-400/40 bg-rose-500/10 text-rose-200'; return `<span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${cls}">${$('<div>').text(d).html()}</span>`; } },
                    { data: 'created_at', render: function (d, t, r) { return (t === 'sort' || t === 'type') ? (r.created_at_timestamp || 0) : `<span class="text-zinc-400">${$('<div>').text(d).html()}</span>`; } },
                    { data: null, orderable: false, searchable: false, render: function (d, t, r) {
                        const statusBtnClass = r.status === 'active' ? 'border-emerald-400/30 text-emerald-200 hover:bg-emerald-500/10' : 'border-zinc-500/35 text-zinc-300 hover:bg-zinc-500/10';
                        const statusBtnLabel = r.status === 'active' ? 'Active' : 'Inactive';
                        return `<div class="flex flex-wrap gap-2">
                            <button type="button" class="js-toggle-status smooth-action-btn rounded-lg border px-3 py-1.5 text-xs font-semibold ${statusBtnClass}" data-url="${r.toggle_status_url}">${statusBtnLabel}</button>
                            <button type="button" class="js-edit-service smooth-action-btn rounded-lg border border-cyan-400/30 px-3 py-1.5 text-xs font-semibold text-cyan-200 hover:bg-cyan-500/10" data-modal-open="edit-service-modal">Edit</button>
                            <button type="button" class="js-delete-service smooth-action-btn rounded-lg border border-rose-400/35 px-3 py-1.5 text-xs font-semibold text-rose-200 hover:bg-rose-500/10" data-url="${r.delete_url}">Delete</button>
                        </div>`;
                    } }
                ]
            });

            tableElement.on('click', '.js-edit-service', function () {
                const rowData = table.row($(this).closest('tr')).data();
                if (!rowData) return;
                $('#editServiceForm').attr('action', rowData.update_url);
                $('#editServiceName').val(rowData.name);
                $('#editServiceCategory').val(rowData.category_id);
                $('#editServicePrice').val(rowData.price_value);
                $('#editServiceDuration').val(rowData.duration_minutes);
                $('#editServiceType').val(rowData.type);
                $('#editServiceMax').val(rowData.max_customers || '');
                $('#editServiceStatus').val(rowData.status);
                $('#editServiceDescription').val(rowData.full_description || '');
                $('#editServiceImage').val('');
                syncMax('#editServiceType', '#editServiceMaxWrap', '#editServiceMax');

                if (rowData.image_url) {
                    $('#editServiceCurrentImage').attr('src', rowData.image_url);
                    $('#editServiceCurrentImageWrap').removeClass('hidden');
                } else {
                    resetEditImagePreview();
                }
            });

            $(document).off('submit.providerServiceCreate').on('submit.providerServiceCreate', '#addServiceForm', function (event) {
                event.preventDefault();
                const form = $(this);
                askConfirm('Verify Add Service', 'Create this service now?', 'Create Service', 'primary', function () {
                    const submitButton = form.find('button[type="submit"]');
                    setLoading(submitButton, true, 'Creating...');
                    const formData = new FormData(form[0]);
                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
                    }).done(function (response) {
                        form[0].reset();
                        syncMax('#addServiceType', '#addServiceMaxWrap', '#addServiceMax');
                        closeModal('add-service-modal');
                        table.ajax.reload(null, false);
                        notify(response.message || 'Service created successfully.', 'success');
                    }).fail(function (xhr) {
                        notify(extractError(xhr), 'error');
                    }).always(function () {
                        setLoading(submitButton, false);
                    });
                });
            });

            $(document).off('submit.providerServiceUpdate').on('submit.providerServiceUpdate', '#editServiceForm', function (event) {
                event.preventDefault();
                const form = $(this);
                askConfirm('Verify Edit Service', 'Save changes for this service?', 'Save Changes', 'primary', function () {
                    const submitButton = form.find('button[type="submit"]');
                    setLoading(submitButton, true, 'Saving...');
                    const formData = new FormData(form[0]);
                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
                    }).done(function (response) {
                        closeModal('edit-service-modal');
                        table.ajax.reload(null, false);
                        notify(response.message || 'Service updated successfully.', 'success');
                    }).fail(function (xhr) {
                        notify(extractError(xhr), 'error');
                    }).always(function () {
                        setLoading(submitButton, false);
                    });
                });
            });

            tableElement.on('click', '.js-toggle-status', function () {
                const button = $(this);
                const url = button.data('url');
                const rowData = table.row(button.closest('tr')).data();
                if (!url) return;
                const nextStatus = rowData && rowData.status === 'active' ? 'inactive' : 'active';
                askConfirm('Verify Status Change', `Mark this service as ${nextStatus}?`, 'Update Status', 'danger', function () {
                    setLoading(button, true, 'Updating...');
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: { _token: csrfToken, _method: 'PATCH' },
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    }).done(function (response) {
                        table.ajax.reload(null, false);
                        notify(response.message || 'Service status updated.', 'success');
                    }).fail(function (xhr) {
                        notify(extractError(xhr), 'error');
                    }).always(function () {
                        setLoading(button, false);
                    });
                });
            });

            tableElement.on('click', '.js-delete-service', function () {
                const button = $(this);
                const url = button.data('url');
                const rowData = table.row(button.closest('tr')).data();
                if (!url) return;
                const serviceName = rowData && rowData.name ? rowData.name : 'this service';
                askConfirm('Verify Delete', `Delete ${serviceName}? This action cannot be undone.`, 'Delete Service', 'danger', function () {
                    setLoading(button, true, 'Deleting...');
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: { _token: csrfToken, _method: 'DELETE' },
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    }).done(function (response) {
                        table.ajax.reload(null, false);
                        notify(response.message || 'Service deleted successfully.', 'success');
                    }).fail(function (xhr) {
                        notify(extractError(xhr), 'error');
                    }).always(function () {
                        setLoading(button, false);
                    });
                });
            });
        })(jQuery);
    </script>
@endpush
