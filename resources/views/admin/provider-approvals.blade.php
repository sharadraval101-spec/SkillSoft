@extends('layouts.app')

@section('content')
<div id="admin-provider-approvals-page" class="space-y-6">
    <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
        <h1 class="text-2xl font-black text-white">Provider Approvals</h1>
        <p class="mt-2 text-sm text-zinc-400">
            Review newly registered providers and approve access to the provider dashboard.
        </p>
    </section>

    <section class="dashboard-panel">
        @if($pendingProviders->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 py-10 text-center text-zinc-500">
                No pending providers.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-left text-zinc-400">
                            <th class="py-3 pr-4 font-semibold">Name</th>
                            <th class="py-3 pr-4 font-semibold">Email</th>
                            <th class="py-3 pr-4 font-semibold">Business</th>
                            <th class="py-3 pr-4 font-semibold">Joined</th>
                            <th class="py-3 pr-4 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingProviders as $providerProfile)
                            <tr class="border-b border-white/5">
                                <td class="py-3 pr-4 text-zinc-100">{{ $providerProfile->user?->name }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ $providerProfile->user?->email }}</td>
                                <td class="py-3 pr-4 text-zinc-300">{{ $providerProfile->business_name }}</td>
                                <td class="py-3 pr-4 text-zinc-400">{{ $providerProfile->created_at?->diffForHumans() }}</td>
                                <td class="py-3 pr-4">
                                    <button type="button" data-modal-open="approve-provider-{{ $providerProfile->id }}" class="smooth-action-btn rounded-xl border border-emerald-400/35 px-3 py-2 text-xs font-semibold text-emerald-200 hover:bg-emerald-500/10">
                                        Review
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>

@foreach($pendingProviders as $providerProfile)
    <x-modal id="approve-provider-{{ $providerProfile->id }}" title="Approve Provider" max-width="max-w-xl">
        <div class="space-y-4">
            <div class="rounded-xl border border-white/10 bg-zinc-950/50 p-4">
                <p class="text-xs uppercase tracking-wider text-zinc-500">Provider Name</p>
                <p class="js-provider-name mt-1 text-sm font-semibold text-zinc-100">{{ $providerProfile->user?->name }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-zinc-950/50 p-4">
                <p class="text-xs uppercase tracking-wider text-zinc-500">Email</p>
                <p class="mt-1 text-sm font-semibold text-zinc-100">{{ $providerProfile->user?->email }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-zinc-950/50 p-4">
                <p class="text-xs uppercase tracking-wider text-zinc-500">Business</p>
                <p class="mt-1 text-sm font-semibold text-zinc-100">{{ $providerProfile->business_name ?: 'N/A' }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-zinc-950/50 p-4">
                <p class="text-xs uppercase tracking-wider text-zinc-500">Registered</p>
                <p class="mt-1 text-sm font-semibold text-zinc-100">{{ $providerProfile->created_at?->format('d M Y, h:i A') }}</p>
            </div>

            <form method="POST" action="{{ route('admin.providers.approve', $providerProfile) }}" class="js-ajax-form flex justify-end gap-2 pt-2">
                @csrf
                @method('PATCH')
                <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">
                    Cancel
                </button>
                <button type="submit" class="smooth-action-btn rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                    Approve Provider
                </button>
            </form>
        </div>
    </x-modal>
@endforeach

<x-modal id="confirm-provider-action-modal" title="Verify Action" max-width="max-w-lg">
    <div class="space-y-4">
        <div>
            <p id="confirmProviderActionTitle" class="text-base font-bold text-white">Please confirm this action</p>
            <p id="confirmProviderActionMessage" class="mt-2 text-sm text-zinc-300">Do you want to continue?</p>
        </div>

        <div class="flex justify-end gap-2 pt-1">
            <button type="button" data-modal-hide class="smooth-action-btn rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-white/10">
                Cancel
            </button>
            <button id="confirmProviderActionButton" type="button" class="smooth-action-btn rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                Confirm
            </button>
        </div>
    </div>
</x-modal>
@endsection

@push('scripts')
<script>
    (function ($) {
        const pageSelector = '#admin-provider-approvals-page';

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

        const closeModalByElement = function (modalElement) {
            if (window.SSModal && typeof window.SSModal.closeElement === 'function') {
                window.SSModal.closeElement(modalElement);
                return;
            }

            if (!modalElement) {
                return;
            }

            modalElement.classList.add('hidden');
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

        const closeModal = function (modalId) {
            if (window.SSModal && typeof window.SSModal.closeById === 'function') {
                window.SSModal.closeById(modalId);
                return;
            }

            const modal = document.getElementById(modalId);
            closeModalByElement(modal);
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

        let confirmProviderHandler = null;
        const confirmTitle = $('#confirmProviderActionTitle');
        const confirmMessage = $('#confirmProviderActionMessage');
        const confirmButton = $('#confirmProviderActionButton');

        const askConfirmation = function (options) {
            confirmTitle.text(options.title || 'Please confirm this action');
            confirmMessage.text(options.message || 'Do you want to continue?');
            confirmButton.text(options.confirmText || 'Confirm');

            const buttonClass = options.tone === 'danger'
                ? 'smooth-action-btn rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500'
                : 'smooth-action-btn rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500';
            confirmButton.attr('class', buttonClass);

            confirmProviderHandler = typeof options.onConfirm === 'function' ? options.onConfirm : null;
            openModal('confirm-provider-action-modal');
        };

        $(document).off('click.adminConfirmProviderAction').on('click.adminConfirmProviderAction', '#confirmProviderActionButton', function () {
            const action = confirmProviderHandler;
            confirmProviderHandler = null;
            closeModal('confirm-provider-action-modal');

            if (typeof action === 'function') {
                action();
            }
        });

        $(document).off('click.adminConfirmProviderCancel').on('click.adminConfirmProviderCancel', '#confirm-provider-action-modal [data-modal-hide]', function () {
            confirmProviderHandler = null;
        });

        $(document).off('keydown.adminConfirmProviderEscape').on('keydown.adminConfirmProviderEscape', function (event) {
            if (event.key === 'Escape') {
                confirmProviderHandler = null;
            }
        });

        $(document).off('submit.adminApproveAjax').on('submit.adminApproveAjax', '.js-ajax-form', function (event) {
            event.preventDefault();
            const form = $(this);
            const submitButtons = form.find('button[type="submit"]');
            const parentModal = form.closest('[data-modal-root]').get(0);
            const providerName = form.closest('[data-modal-root]').find('.js-provider-name').first().text().trim() || 'this provider';

            askConfirmation({
                title: 'Verify Provider Approval',
                message: `Approve ${providerName} now?`,
                confirmText: 'Approve Provider',
                tone: 'primary',
                onConfirm: function () {
                    setButtonLoading(submitButtons, true, 'Approving...');

                    $.ajax({
                        url: form.attr('action'),
                        method: form.attr('method') || 'POST',
                        data: form.serialize(),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
                        }
                    }).done(function (response) {
                        closeModalByElement(parentModal);

                        if (typeof response === 'string') {
                            const nextRoot = $('<div>').append($.parseHTML(response)).find(pageSelector).first();
                            if (nextRoot.length) {
                                $(pageSelector).replaceWith(nextRoot);
                            }
                        }

                        showNotice('Provider approved successfully.', 'success');
                    }).fail(function (xhr) {
                        showNotice(extractError(xhr), 'error');
                    }).always(function () {
                        setButtonLoading(submitButtons, false);
                    });
                }
            });
        });
    })(jQuery);
</script>
@endpush
