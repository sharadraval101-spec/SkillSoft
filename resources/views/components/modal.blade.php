@props([
    'id',
    'title' => 'Modal',
    'maxWidth' => 'max-w-2xl',
])

<div id="{{ $id }}" class="fixed inset-0 z-[80] hidden" data-modal-root>
    <div class="modal-backdrop absolute inset-0 bg-black/70" data-modal-hide></div>
    <div class="relative z-10 mx-auto flex min-h-screen w-full items-center justify-center p-4">
        <div class="modal-panel w-full {{ $maxWidth }} rounded-2xl border border-white/10 bg-zinc-900 shadow-2xl shadow-black/40">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                <h3 class="text-base font-bold text-white">{{ $title }}</h3>
                <button type="button" class="smooth-action-btn rounded-lg border border-white/10 px-2 py-1 text-xs font-semibold text-zinc-300 hover:bg-white/10" data-modal-hide>
                    Close
                </button>
            </div>
            <div class="px-5 py-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const lockClass = 'overflow-hidden';
                const closingClass = 'is-closing';
                const closeDelayMs = 170;

                const hasOpenModal = function () {
                    return !!document.querySelector('[data-modal-root]:not(.hidden):not(.is-closing)');
                };

                const syncBodyLock = function () {
                    document.body.classList.toggle(lockClass, hasOpenModal());
                };

                const closeModal = function (modal) {
                    if (!modal) {
                        syncBodyLock();
                        return;
                    }

                    if (modal.__closeTimer) {
                        clearTimeout(modal.__closeTimer);
                        modal.__closeTimer = null;
                    }

                    modal.classList.add(closingClass);
                    modal.__closeTimer = window.setTimeout(function () {
                        modal.classList.add('hidden');
                        modal.classList.remove(closingClass);
                        modal.__closeTimer = null;
                        syncBodyLock();
                    }, closeDelayMs);
                };

                const openModal = function (modal) {
                    if (!modal) {
                        return;
                    }

                    if (modal.__closeTimer) {
                        clearTimeout(modal.__closeTimer);
                        modal.__closeTimer = null;
                    }

                    modal.classList.remove('hidden');
                    modal.classList.remove(closingClass);
                    document.body.classList.add(lockClass);
                };

                window.SSModal = window.SSModal || {};
                window.SSModal.openById = function (id) {
                    openModal(id ? document.getElementById(id) : null);
                };
                window.SSModal.closeById = function (id) {
                    closeModal(id ? document.getElementById(id) : null);
                };
                window.SSModal.closeElement = function (modal) {
                    closeModal(modal);
                };
                window.SSModal.hasOpen = hasOpenModal;

                document.addEventListener('click', function (event) {
                    const openTrigger = event.target.closest('[data-modal-open]');
                    if (openTrigger) {
                        const targetId = openTrigger.getAttribute('data-modal-open');
                        const modal = targetId ? document.getElementById(targetId) : null;
                        openModal(modal);
                        return;
                    }

                    const hideTrigger = event.target.closest('[data-modal-hide]');
                    if (hideTrigger) {
                        const modal = hideTrigger.closest('[data-modal-root]');
                        closeModal(modal);
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key !== 'Escape') {
                        return;
                    }
                    const activeModal = document.querySelector('[data-modal-root]:not(.hidden):not(.is-closing)');
                    if (activeModal) {
                        closeModal(activeModal);
                    }
                });
            })();
        </script>
    @endpush
@endonce
