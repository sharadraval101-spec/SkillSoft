@php
    $toasts = collect();

    $resolveToastText = static function (mixed $value): ?string {
        if (is_string($value) || is_numeric($value)) {
            $text = trim((string) $value);

            return $text !== '' ? $text : null;
        }

        if (is_array($value)) {
            $candidate = data_get($value, 'message')
                ?? data_get($value, 'text')
                ?? collect($value)->flatten()->first(fn ($item) => is_string($item) || is_numeric($item));

            return $candidate !== null ? trim((string) $candidate) : null;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            $text = trim((string) $value);

            return $text !== '' ? $text : null;
        }

        return null;
    };

    foreach ([
        'success' => 'success',
        'password_reset_success' => 'success',
        'status' => 'info',
        'info' => 'info',
        'code_sent' => 'info',
        'warning' => 'warning',
        'error' => 'error',
    ] as $key => $type) {
        $text = $resolveToastText(session($key));

        if ($text !== null) {
            $toasts->push([
                'type' => $type,
                'text' => $text,
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
            success: 'border-emerald-300 bg-emerald-50 text-emerald-800',
            info: 'border-sky-300 bg-sky-50 text-sky-800',
            warning: 'border-amber-300 bg-amber-50 text-amber-800',
            error: 'border-rose-300 bg-rose-50 text-rose-800'
        };

        window.showFlashToast = (type, text, options = {}) => {
            const root = ensureToastStack();
            const message = typeof text === 'string'
                ? text.trim()
                : String(text ?? '').trim();

            if (!root || !message) {
                return;
            }

            const toast = document.createElement('div');
            toast.className = `toast-pop border backdrop-blur-md shadow-lg ${styleByType[type] || styleByType.info}`;
            toast.textContent = message;
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
