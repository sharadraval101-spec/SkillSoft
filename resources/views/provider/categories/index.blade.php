@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        #provider-categories-page .dataTables_wrapper .dataTables_length label,
        #provider-categories-page .dataTables_wrapper .dataTables_filter label,
        #provider-categories-page .dataTables_wrapper .dataTables_info,
        #provider-categories-page .dataTables_wrapper .dataTables_paginate {
            color: #a1a1aa;
            font-size: 0.875rem;
        }

        #provider-categories-page .dataTables_wrapper .dataTables_filter input,
        #provider-categories-page .dataTables_wrapper .dataTables_length select {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.65rem;
            background: rgba(9, 9, 11, 0.55);
            color: #e4e4e7;
            padding: 0.4rem 0.55rem;
        }

        #provider-categories-page .dataTables_wrapper {
            overflow-x: hidden;
        }

        #provider-categories-page table.dataTable {
            width: 100% !important;
        }

        #provider-categories-page table.dataTable th,
        #provider-categories-page table.dataTable td {
            white-space: normal;
            overflow-wrap: anywhere;
            vertical-align: middle;
        }

        #provider-categories-page table.dataTable.no-footer {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        #provider-categories-page table.dataTable thead th {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        #provider-categories-page table.dataTable tbody tr {
            background: transparent;
        }

        #provider-categories-page table.dataTable.stripe tbody tr.odd,
        #provider-categories-page table.dataTable.display tbody tr.odd {
            background-color: rgba(255, 255, 255, 0.01);
        }

        #provider-categories-page .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.6rem;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #d4d4d8 !important;
            background: rgba(9, 9, 11, 0.55) !important;
            padding: 0.3rem 0.7rem;
            margin-left: 0.25rem;
        }

        #provider-categories-page .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: rgba(6, 182, 212, 0.18) !important;
            border-color: rgba(34, 211, 238, 0.45) !important;
            color: #cffafe !important;
        }

        #provider-categories-page .dataTables_wrapper .dataTables_processing {
            border-radius: 0.75rem;
            border: 1px solid rgba(34, 211, 238, 0.35);
            background: rgba(9, 9, 11, 0.88);
            color: #cffafe;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.3);
            animation: providerCategoriesPulse 1.2s ease-in-out infinite;
        }

        #provider-categories-page .category-thumb {
            height: 48px;
            width: 48px;
            border-radius: 0.75rem;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        @keyframes providerCategoriesPulse {
            0%, 100% { opacity: 0.72; }
            50% { opacity: 1; }
        }
    </style>
@endpush

@push('styles')
    <style>
        #provider-categories-page { position: relative; }
        #provider-categories-page::before { content: ''; position: absolute; top: -2rem; right: 4rem; width: 11rem; height: 11rem; border-radius: 9999px; background: radial-gradient(circle, rgba(34, 211, 238, 0.16), transparent 70%); pointer-events: none; }
        #provider-categories-page > * { position: relative; z-index: 1; }
        #provider-categories-page > section:first-child { border: 1px solid rgba(228, 228, 231, 0.9); background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(244, 244, 245, 0.94)), radial-gradient(circle at top right, rgba(34, 211, 238, 0.12), transparent 30%); box-shadow: 0 26px 70px rgba(15, 23, 42, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.85); }
        #provider-categories-page > section:first-child h1 { font-size: clamp(1.9rem, 2.7vw, 2.7rem); line-height: 1.05; letter-spacing: -0.04em; color: #111827 !important; }
        #provider-categories-page > section:first-child p { max-width: 40rem; font-size: 0.98rem; line-height: 1.7; color: #5b6474 !important; }
        #provider-categories-page [data-modal-open='add-category-modal'] { border: 1px solid rgba(14, 165, 233, 0.16); background: linear-gradient(135deg, #0891b2, #0ea5e9) !important; color: #f8fafc !important; box-shadow: 0 14px 30px rgba(8, 145, 178, 0.2); }
        #provider-categories-page [data-modal-open='add-category-modal']:hover { transform: translateY(-1px); box-shadow: 0 18px 36px rgba(8, 145, 178, 0.24); }
        #provider-categories-page .dashboard-panel { border: 1px solid rgba(228, 228, 231, 0.9); background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96)); box-shadow: 0 30px 80px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.92); }
        #provider-categories-page .dataTables_wrapper .dataTables_length label, #provider-categories-page .dataTables_wrapper .dataTables_filter label, #provider-categories-page .dataTables_wrapper .dataTables_info, #provider-categories-page .dataTables_wrapper .dataTables_paginate { color: #6b7280; font-size: 0.875rem; }
        #provider-categories-page .dataTables_wrapper .dataTables_length label, #provider-categories-page .dataTables_wrapper .dataTables_filter label { display: inline-flex; align-items: center; gap: 0.55rem; font-weight: 500; }
        #provider-categories-page .dataTables_wrapper .dataTables_filter input, #provider-categories-page .dataTables_wrapper .dataTables_length select { min-height: 2.45rem; border: 1px solid rgba(212, 212, 216, 0.9); border-radius: 0.85rem; background: rgba(255, 255, 255, 0.96); color: #18181b; padding: 0.42rem 0.8rem; box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04); }
        #provider-categories-page .dataTables_wrapper .dataTables_filter input:focus, #provider-categories-page .dataTables_wrapper .dataTables_length select:focus { outline: none; border-color: rgba(14, 165, 233, 0.4); box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.12); }
        #provider-categories-page table.dataTable.no-footer { border-bottom: 1px solid rgba(228, 228, 231, 0.95); }
        #provider-categories-page table.dataTable thead th { padding: 0.95rem 0.75rem; border-bottom: 1px solid rgba(228, 228, 231, 0.95); font-size: 0.77rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #52525b; }
        #provider-categories-page table.dataTable.stripe tbody tr.odd, #provider-categories-page table.dataTable.display tbody tr.odd { background-color: rgba(248, 250, 252, 0.92); }
        #provider-categories-page table.dataTable tbody td { padding: 1.2rem 0.75rem; border-top: 1px solid rgba(228, 228, 231, 0.82); color: #27272a; }
        #provider-categories-page table.dataTable tbody tr:first-child td { border-top: none; }
        #provider-categories-page table.dataTable tbody tr:hover td { background: rgba(244, 244, 245, 0.82); }
        #provider-categories-page table.dataTable tbody td:first-child { border-radius: 1rem 0 0 1rem; }
        #provider-categories-page table.dataTable tbody td:last-child { border-radius: 0 1rem 1rem 0; }
        #provider-categories-page .dataTables_wrapper .dataTables_paginate .paginate_button { border-radius: 0.8rem; border: 1px solid rgba(212, 212, 216, 0.95) !important; color: #52525b !important; background: rgba(255, 255, 255, 0.95) !important; padding: 0.38rem 0.82rem; margin-left: 0.25rem; transition: all 0.18s ease; }
        #provider-categories-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover { border-color: rgba(14, 165, 233, 0.32) !important; background: rgba(240, 249, 255, 0.95) !important; color: #0f172a !important; }
        #provider-categories-page .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: linear-gradient(135deg, rgba(34, 211, 238, 0.16), rgba(14, 165, 233, 0.2)) !important; border-color: rgba(14, 165, 233, 0.35) !important; color: #0f172a !important; box-shadow: 0 10px 18px rgba(34, 211, 238, 0.12); }
        #provider-categories-page .dataTables_wrapper .dataTables_processing { border-radius: 0.75rem; border: 1px solid rgba(14, 165, 233, 0.24); background: rgba(255, 255, 255, 0.96); color: #0f172a; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12); }
        #provider-categories-page .category-thumb, #providerCategoriesTable .category-thumb.flex { height: 3.35rem; width: 3.35rem; border-radius: 1rem; }
        #provider-categories-page .category-thumb { border: 1px solid rgba(228, 228, 231, 0.95); box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08); }
        #providerCategoriesTable .category-thumb.flex { display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #fafafa, #f4f4f5) !important; border: 1px dashed rgba(161, 161, 170, 0.9); color: #71717a !important; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; }
        #providerCategoriesTable .text-zinc-100 { color: #18181b !important; font-weight: 700; }
        #providerCategoriesTable .text-zinc-300 { color: #52525b !important; line-height: 1.65; }
        #providerCategoriesTable .text-zinc-400 { color: #71717a !important; }
        #providerCategoriesTable tbody td:nth-child(5) span { display: inline-flex; align-items: center; justify-content: center; border-radius: 9999px; padding: 0.45rem 0.8rem; font-size: 0.76rem; font-weight: 700; letter-spacing: 0.01em; }
        #providerCategoriesTable tbody td:nth-child(5) [class*='emerald'] { border-color: rgba(16, 185, 129, 0.2) !important; background: rgba(236, 253, 245, 0.98) !important; color: #047857 !important; }
        #providerCategoriesTable tbody td:nth-child(5) [class*='rose'] { border-color: rgba(244, 63, 94, 0.18) !important; background: rgba(255, 241, 242, 0.98) !important; color: #be123c !important; }
        #providerCategoriesTable .js-toggle-status, #providerCategoriesTable .js-edit-category, #providerCategoriesTable .js-delete-category { display: inline-flex; align-items: center; justify-content: center; min-width: 5.8rem; border-radius: 0.9rem; padding: 0.52rem 0.85rem; font-size: 0.78rem; font-weight: 700; transition: all 0.18s ease; }
        #providerCategoriesTable .js-toggle-status:hover, #providerCategoriesTable .js-edit-category:hover, #providerCategoriesTable .js-delete-category:hover { transform: translateY(-1px); }
        #providerCategoriesTable .js-toggle-status[class*='emerald'] { border-color: rgba(16, 185, 129, 0.2) !important; background: rgba(236, 253, 245, 0.96) !important; color: #047857 !important; }
        #providerCategoriesTable .js-toggle-status[class*='zinc'] { border-color: rgba(161, 161, 170, 0.35) !important; background: rgba(250, 250, 250, 0.96) !important; color: #3f3f46 !important; }
        #providerCategoriesTable .js-edit-category { border-color: rgba(14, 165, 233, 0.2) !important; background: rgba(240, 249, 255, 0.98) !important; color: #0369a1 !important; }
        #providerCategoriesTable .js-delete-category { border-color: rgba(244, 63, 94, 0.18) !important; background: rgba(255, 241, 242, 0.98) !important; color: #be123c !important; }
        #provider-categories-page #addCategoryForm input, #provider-categories-page #addCategoryForm select, #provider-categories-page #addCategoryForm textarea, #provider-categories-page #editCategoryForm input, #provider-categories-page #editCategoryForm select, #provider-categories-page #editCategoryForm textarea { border-color: rgba(212, 212, 216, 0.95) !important; background: rgba(255, 255, 255, 0.95) !important; color: #18181b !important; box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04); }
        #provider-categories-page #addCategoryForm label, #provider-categories-page #editCategoryForm label { color: #71717a !important; }
        #provider-categories-page #editCategoryCurrentImageWrap { border-color: rgba(212, 212, 216, 0.9) !important; background: rgba(244, 244, 245, 0.82) !important; }
        #provider-categories-page #confirmCategoryActionTitle { color: #18181b !important; }
        #provider-categories-page #confirmCategoryActionMessage { color: #52525b !important; }
        #provider-categories-page #confirmCategoryActionButton { color: #f8fafc !important; }
        #provider-categories-page #confirmCategoryActionButton[class*='bg-cyan'] { border: 1px solid rgba(14, 165, 233, 0.16) !important; background: linear-gradient(135deg, #0284c7, #0ea5e9) !important; }
        #provider-categories-page #confirmCategoryActionButton[class*='bg-rose'] { border: 1px solid rgba(244, 63, 94, 0.18) !important; background: linear-gradient(135deg, #e11d48, #f43f5e) !important; }
        @media (max-width: 768px) { #provider-categories-page > section:first-child h1 { font-size: 1.7rem; } #provider-categories-page > section:first-child p { font-size: 0.92rem; } #provider-categories-page .dataTables_wrapper .dataTables_length, #provider-categories-page .dataTables_wrapper .dataTables_filter, #provider-categories-page .dataTables_wrapper .dataTables_info, #provider-categories-page .dataTables_wrapper .dataTables_paginate { text-align: left; } }
    </style>
@endpush
@section('content')
@php
    $inputClass = 'w-full rounded-xl border border-white/15 bg-zinc-950/70 px-3 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 focus:border-cyan-400/50 focus:outline-none';
    $labelClass = 'mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400';
@endphp

<div id="provider-categories-page" class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-white">Category Management</h1>
                <p class="mt-2 text-sm text-zinc-400">
                    Create and manage service categories with status, image, and description.
                </p>
            </div>

            <button type="button" data-modal-open="add-category-modal" class="smooth-action-btn inline-flex items-center rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-500">
                + Add Category
            </button>
        </div>
    </section>

    <section class="dashboard-panel">
        <div>
            <table id="providerCategoriesTable" class="display w-full text-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>

<x-modal id="add-category-modal" title="Add Category" max-width="max-w-2xl">
    <form id="addCategoryForm" method="POST" action="{{ route('provider.categories.store') }}" class="space-y-4" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="addCategoryName" class="{{ $labelClass }}">Name</label>
                <input id="addCategoryName" name="name" type="text" required class="{{ $inputClass }}">
            </div>

            <div>
                <label for="addCategoryStatus" class="{{ $labelClass }}">Status</label>
                <select id="addCategoryStatus" name="status" required class="{{ $inputClass }}">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label for="addCategoryDescription" class="{{ $labelClass }}">Description</label>
                <textarea id="addCategoryDescription" name="description" rows="3" class="{{ $inputClass }}"></textarea>
            </div>

            <div class="sm:col-span-2">
                <label for="addCategoryImage" class="{{ $labelClass }}">Image</label>
                <input id="addCategoryImage" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="{{ $inputClass }}">
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">
                Cancel
            </button>
            <button type="submit" class="smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">
                Create Category
            </button>
        </div>
    </form>
</x-modal>

<x-modal id="edit-category-modal" title="Edit Category" max-width="max-w-2xl">
    <form id="editCategoryForm" method="POST" action="" class="space-y-4" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="editCategoryName" class="{{ $labelClass }}">Name</label>
                <input id="editCategoryName" name="name" type="text" required class="{{ $inputClass }}">
            </div>

            <div>
                <label for="editCategoryStatus" class="{{ $labelClass }}">Status</label>
                <select id="editCategoryStatus" name="status" required class="{{ $inputClass }}">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label for="editCategoryDescription" class="{{ $labelClass }}">Description</label>
                <textarea id="editCategoryDescription" name="description" rows="3" class="{{ $inputClass }}"></textarea>
            </div>

            <div class="sm:col-span-2">
                <label for="editCategoryImage" class="{{ $labelClass }}">New Image (Optional)</label>
                <input id="editCategoryImage" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="{{ $inputClass }}">
            </div>

            <div id="editCategoryCurrentImageWrap" class="hidden sm:col-span-2 rounded-xl border border-white/10 bg-zinc-950/50 p-3">
                <p class="text-xs uppercase tracking-[0.16em] text-zinc-500">Current Image</p>
                <img id="editCategoryCurrentImage" src="" alt="Current category image" class="mt-2 h-16 w-16 rounded-xl border border-white/10 object-cover">
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

<x-modal id="confirm-category-action-modal" title="Verify Action" max-width="max-w-lg">
    <div class="space-y-4">
        <div>
            <p id="confirmCategoryActionTitle" class="text-base font-bold text-white">Please confirm this action</p>
            <p id="confirmCategoryActionMessage" class="mt-2 text-sm text-zinc-300">Do you want to continue?</p>
        </div>

        <div class="flex justify-end gap-2 pt-1">
            <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">
                Cancel
            </button>
            <button id="confirmCategoryActionButton" type="button" class="smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">
                Confirm
            </button>
        </div>
    </div>
</x-modal>
@endsection

@push('styles')
    <style>
        #add-category-modal .modal-panel, #edit-category-modal .modal-panel, #confirm-category-action-modal .modal-panel { border-color: rgba(228, 228, 231, 0.92) !important; box-shadow: 0 28px 70px rgba(15, 23, 42, 0.12); }
        #add-category-modal .modal-panel > div:first-child, #edit-category-modal .modal-panel > div:first-child, #confirm-category-action-modal .modal-panel > div:first-child { border-color: rgba(228, 228, 231, 0.92) !important; }
        #add-category-modal .modal-panel h3, #edit-category-modal .modal-panel h3, #confirm-category-action-modal .modal-panel h3 { color: #18181b !important; }
        #add-category-modal [data-modal-hide], #edit-category-modal [data-modal-hide], #confirm-category-action-modal [data-modal-hide] { border: 1px solid rgba(212, 212, 216, 0.95) !important; background: rgba(255, 255, 255, 0.94) !important; color: #3f3f46 !important; }
        #addCategoryForm input, #addCategoryForm select, #addCategoryForm textarea, #editCategoryForm input, #editCategoryForm select, #editCategoryForm textarea { border-color: rgba(212, 212, 216, 0.95) !important; background: rgba(255, 255, 255, 0.95) !important; color: #18181b !important; box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04); }
        #addCategoryForm label, #editCategoryForm label { color: #71717a !important; }
        #addCategoryForm button[type='submit'], #editCategoryForm button[type='submit'], #confirmCategoryActionButton { border: 1px solid rgba(14, 165, 233, 0.16) !important; background: linear-gradient(135deg, #0284c7, #0ea5e9) !important; color: #f8fafc !important; box-shadow: 0 14px 30px rgba(14, 165, 233, 0.18); }
        #confirmCategoryActionButton[class*='bg-rose'] { border-color: rgba(244, 63, 94, 0.18) !important; background: linear-gradient(135deg, #e11d48, #f43f5e) !important; box-shadow: 0 14px 28px rgba(244, 63, 94, 0.16); }
        #editCategoryCurrentImageWrap { border-color: rgba(212, 212, 216, 0.9) !important; background: rgba(244, 244, 245, 0.82) !important; }
        #confirmCategoryActionTitle { color: #18181b !important; }
        #confirmCategoryActionMessage { color: #52525b !important; }
    </style>
@endpush
@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        (function ($) {
            const tableElement = $('#providerCategoriesTable');
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

            let confirmHandler = null;
            const confirmTitle = $('#confirmCategoryActionTitle');
            const confirmMessage = $('#confirmCategoryActionMessage');
            const confirmButton = $('#confirmCategoryActionButton');

            const askConfirmation = function (options) {
                confirmTitle.text(options.title || 'Please confirm this action');
                confirmMessage.text(options.message || 'Do you want to continue?');
                confirmButton.text(options.confirmText || 'Confirm');

                const buttonClass = options.tone === 'danger'
                    ? 'smooth-action-btn rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500'
                    : 'smooth-action-btn rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500';
                confirmButton.attr('class', buttonClass);

                confirmHandler = typeof options.onConfirm === 'function' ? options.onConfirm : null;
                openModal('confirm-category-action-modal');
            };

            $(document).off('click.providerCategoryConfirm').on('click.providerCategoryConfirm', '#confirmCategoryActionButton', function () {
                const action = confirmHandler;
                confirmHandler = null;
                closeModal('confirm-category-action-modal');

                if (typeof action === 'function') {
                    action();
                }
            });

            $(document).off('click.providerCategoryConfirmCancel').on('click.providerCategoryConfirmCancel', '#confirm-category-action-modal [data-modal-hide]', function () {
                confirmHandler = null;
            });

            $(document).off('keydown.providerCategoryConfirmEscape').on('keydown.providerCategoryConfirmEscape', function (event) {
                if (event.key === 'Escape') {
                    confirmHandler = null;
                }
            });

            const table = tableElement.DataTable({
                ajax: {
                    url: '{{ $categoriesDataUrl }}',
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
                    emptyTable: 'No categories found.',
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
                        data: 'image_url',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            if (!data) {
                                return '<div class="category-thumb flex items-center justify-center bg-white/5 text-[11px] text-zinc-400">No Img</div>';
                            }

                            const name = $('<div>').text(row.name || 'Category').html();
                            return `<img src="${data}" alt="${name}" class="category-thumb">`;
                        }
                    },
                    {
                        data: 'name',
                        render: function (data) {
                            return `<span class="font-semibold text-zinc-100">${$('<div>').text(data).html()}</span>`;
                        }
                    },
                    {
                        data: 'description',
                        render: function (data, type, row) {
                            const fullDescription = row.full_description || '';
                            const text = data || '-';

                            if (type === 'display' && fullDescription) {
                                return `<span class="text-zinc-300" title="${$('<div>').text(fullDescription).html()}">${$('<div>').text(text).html()}</span>`;
                            }

                            return `<span class="text-zinc-300">${$('<div>').text(text).html()}</span>`;
                        }
                    },
                    {
                        data: 'status_label',
                        render: function (data, type, row) {
                            const statusClass = row.status === 'active'
                                ? 'border-emerald-400/40 bg-emerald-500/10 text-emerald-200'
                                : 'border-rose-400/40 bg-rose-500/10 text-rose-200';

                            return `<span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${statusClass}">${$('<div>').text(data).html()}</span>`;
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
                            const statusBtnClass = row.status === 'active'
                                ? 'border-emerald-400/30 text-emerald-200 hover:bg-emerald-500/10'
                                : 'border-zinc-500/35 text-zinc-300 hover:bg-zinc-500/10';
                            const statusBtnLabel = row.status === 'active' ? 'Active' : 'Inactive';

                            return `<div class="flex flex-wrap gap-2">
                                <button type="button" class="js-toggle-status smooth-action-btn rounded-lg border px-3 py-1.5 text-xs font-semibold ${statusBtnClass}" data-url="${row.toggle_status_url}">
                                    ${statusBtnLabel}
                                </button>
                                <button type="button" class="js-edit-category smooth-action-btn rounded-lg border border-cyan-400/30 px-3 py-1.5 text-xs font-semibold text-cyan-200 hover:bg-cyan-500/10" data-modal-open="edit-category-modal">
                                    Edit
                                </button>
                                <button type="button" class="js-delete-category smooth-action-btn rounded-lg border border-rose-400/35 px-3 py-1.5 text-xs font-semibold text-rose-200 hover:bg-rose-500/10" data-url="${row.delete_url}">
                                    Delete
                                </button>
                            </div>`;
                        }
                    }
                ]
            });

            const resetEditImagePreview = function () {
                $('#editCategoryCurrentImageWrap').addClass('hidden');
                $('#editCategoryCurrentImage').attr('src', '');
            };

            tableElement.on('click', '.js-edit-category', function () {
                const rowData = table.row($(this).closest('tr')).data();
                if (!rowData) {
                    return;
                }

                $('#editCategoryForm').attr('action', rowData.update_url);
                $('#editCategoryName').val(rowData.name);
                $('#editCategoryDescription').val(rowData.full_description || '');
                $('#editCategoryStatus').val(rowData.status);
                $('#editCategoryImage').val('');

                if (rowData.image_url) {
                    $('#editCategoryCurrentImage').attr('src', rowData.image_url);
                    $('#editCategoryCurrentImageWrap').removeClass('hidden');
                } else {
                    resetEditImagePreview();
                }
            });

            $(document).off('submit.providerCategoryCreate').on('submit.providerCategoryCreate', '#addCategoryForm', function (event) {
                event.preventDefault();
                const form = $(this);

                askConfirmation({
                    title: 'Verify Add Category',
                    message: 'Create this category now?',
                    confirmText: 'Create Category',
                    tone: 'primary',
                    onConfirm: function () {
                        const submitButton = form.find('button[type="submit"]');
                        setButtonLoading(submitButton, true, 'Creating...');

                        const formData = new FormData(form[0]);
                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        }).done(function (response) {
                            form[0].reset();
                            closeModal('add-category-modal');
                            table.ajax.reload(null, false);
                            showNotice(response.message || 'Category created successfully.', 'success');
                        }).fail(function (xhr) {
                            showNotice(extractError(xhr), 'error');
                        }).always(function () {
                            setButtonLoading(submitButton, false);
                        });
                    }
                });
            });

            $(document).off('submit.providerCategoryUpdate').on('submit.providerCategoryUpdate', '#editCategoryForm', function (event) {
                event.preventDefault();
                const form = $(this);

                askConfirmation({
                    title: 'Verify Edit Category',
                    message: 'Save changes for this category?',
                    confirmText: 'Save Changes',
                    tone: 'primary',
                    onConfirm: function () {
                        const submitButton = form.find('button[type="submit"]');
                        setButtonLoading(submitButton, true, 'Saving...');

                        const formData = new FormData(form[0]);
                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        }).done(function (response) {
                            closeModal('edit-category-modal');
                            table.ajax.reload(null, false);
                            showNotice(response.message || 'Category updated successfully.', 'success');
                        }).fail(function (xhr) {
                            showNotice(extractError(xhr), 'error');
                        }).always(function () {
                            setButtonLoading(submitButton, false);
                        });
                    }
                });
            });

            tableElement.on('click', '.js-toggle-status', function () {
                const button = $(this);
                const url = button.data('url');
                const rowData = table.row(button.closest('tr')).data();
                if (!url) {
                    return;
                }

                const nextStatus = rowData && rowData.status === 'active' ? 'inactive' : 'active';
                askConfirmation({
                    title: 'Verify Status Change',
                    message: `Mark this category as ${nextStatus}?`,
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
                            showNotice(response.message || 'Category status updated.', 'success');
                        }).fail(function (xhr) {
                            showNotice(extractError(xhr), 'error');
                        }).always(function () {
                            setButtonLoading(button, false);
                        });
                    }
                });
            });

            tableElement.on('click', '.js-delete-category', function () {
                const button = $(this);
                const url = button.data('url');
                const rowData = table.row(button.closest('tr')).data();
                if (!url) {
                    return;
                }

                const categoryName = rowData && rowData.name ? rowData.name : 'this category';
                askConfirmation({
                    title: 'Verify Delete',
                    message: `Delete ${categoryName}? This action cannot be undone.`,
                    confirmText: 'Delete Category',
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
                            showNotice(response.message || 'Category deleted successfully.', 'success');
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
