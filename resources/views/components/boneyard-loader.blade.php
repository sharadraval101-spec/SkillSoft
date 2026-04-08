<script>
    (() => {
        const storageKey = 'skillslot:boneyard-page';

        try {
            const rawPayload = window.sessionStorage.getItem(storageKey);
            if (!rawPayload) {
                return;
            }

            const payload = JSON.parse(rawPayload);
            if (!payload || !payload.html || !payload.createdAt) {
                return;
            }

            if (Date.now() - Number(payload.createdAt) > 5000) {
                window.sessionStorage.removeItem(storageKey);
                return;
            }

            const loader = document.createElement('div');
            loader.id = 'boneyardPageLoader';
            loader.className = 'boneyard-page-loader';
            loader.setAttribute('aria-hidden', 'true');

            const width = Math.max(320, Math.ceil(Number(payload.width ?? 0)));
            loader.innerHTML = `
                <div class="boneyard-page-loader__panel" style="--boneyard-loader-width:${width}px">
                    <div class="boneyard-page-loader__bones">${payload.html}</div>
                </div>
            `;

            document.body.appendChild(loader);
        } catch (error) {
            try {
                window.sessionStorage.removeItem(storageKey);
            } catch (storageError) {
                // Ignore storage cleanup failures.
            }
        }
    })();
</script>
