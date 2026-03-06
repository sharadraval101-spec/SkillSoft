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

@if($toasts->isNotEmpty())
    <div id="flash-toast-root" class="fixed inset-x-0 bottom-4 z-[120] px-4 pointer-events-none">
        <div class="mx-auto max-w-xl flex flex-col items-stretch gap-2"></div>
    </div>

    <script>
        (() => {
            const toasts = @json($toasts->values());
            const root = document.querySelector('#flash-toast-root > div');
            if (!root) return;

            const styleByType = {
                success: 'border-emerald-400/40 bg-emerald-500/15 text-emerald-100',
                info: 'border-cyan-400/40 bg-cyan-500/15 text-cyan-100',
                error: 'border-rose-400/40 bg-rose-500/15 text-rose-100'
            };

            toasts.forEach((item, index) => {
                const toast = document.createElement('div');
                toast.className = `toast-pop border backdrop-blur-md shadow-lg ${styleByType[item.type] || styleByType.info}`;
                toast.textContent = item.text;
                root.appendChild(toast);

                requestAnimationFrame(() => toast.classList.add('toast-pop-show'));

                const hideAfterMs = 1000 + (index * 80);
                setTimeout(() => {
                    toast.classList.remove('toast-pop-show');
                    setTimeout(() => toast.remove(), 260);
                }, hideAfterMs);
            });
        })();
    </script>
@endif
