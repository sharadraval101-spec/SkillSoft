@php
    $toasts = collect();

    foreach (['success', 'code_sent', 'password_reset_success', 'status', 'error'] as $key) {
        if (session($key)) {
            $type = in_array($key, ['success', 'password_reset_success'], true) ? 'success' : (in_array($key, ['error'], true) ? 'error' : 'info');
            $toasts->push([
                'type' => $type,
                'text' => session($key),
            ]);
        }
    }

    if ($errors->any()) {
        foreach ($errors->all() as $errorMessage) {
            $toasts->push([
                'type' => 'error',
                'text' => $errorMessage,
            ]);
        }
    }
@endphp

<div id="flash-toast-root" class="fixed inset-x-0 bottom-4 z-[120] pointer-events-none px-4">
    <div data-toast-stack class="mx-auto flex max-w-xl flex-col items-stretch gap-2"></div>
</div>

<script>
    (() => {
        const initialToasts = @json($toasts->values());

        const ensureToastStack = () => {
            let root = document.getElementById('flash-toast-root');
            if (!root) {
                root = document.createElement('div');
                root.id = 'flash-toast-root';
                root.className = 'fixed inset-x-0 bottom-4 z-[120] pointer-events-none px-4';
                root.innerHTML = '<div data-toast-stack class="mx-auto flex max-w-xl flex-col items-stretch gap-2"></div>';
                document.body.appendChild(root);
            }

            return root.querySelector('[data-toast-stack]');
        };

        const styleByType = {
            success: 'border-emerald-400/40 bg-emerald-500/15 text-emerald-100',
            info: 'border-cyan-400/40 bg-cyan-500/15 text-cyan-100',
            error: 'border-rose-400/40 bg-rose-500/15 text-rose-100'
        };

        window.showFlashToast = (type, text, options = {}) => {
            const root = ensureToastStack();
            if (!root || !text) {
                return;
            }

            const toast = document.createElement('div');
            toast.className = `toast-pop border backdrop-blur-md shadow-lg ${styleByType[type] || styleByType.info}`;
            toast.textContent = text;
            root.appendChild(toast);

            requestAnimationFrame(() => toast.classList.add('toast-pop-show'));

            const hideAfterMs = options.duration ?? 1000;
            setTimeout(() => {
                toast.classList.remove('toast-pop-show');
                setTimeout(() => toast.remove(), 260);
            }, hideAfterMs);
        };

        initialToasts.forEach((item, index) => {
            window.showFlashToast(item.type, item.text, { duration: 1000 + (index * 80) });
        });
    })();
</script>
