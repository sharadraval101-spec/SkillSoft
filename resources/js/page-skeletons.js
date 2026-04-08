import { renderBones, snapshotBones } from 'boneyard-js';

const PAGE_SKELETON_KEY = 'skillslot:boneyard-page';
const PAGE_SKELETON_TTL = 5000;
const PAGE_SKELETON_ENABLED_ROOTS = new Set(['customer-site', 'auth-user', 'customer-dashboard']);

const body = document.body;
const userMotionRoot = body?.dataset.userMotionRoot ?? '';

if (!PAGE_SKELETON_ENABLED_ROOTS.has(userMotionRoot)) {
    // No-op outside the customer/auth experience.
} else {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const readStoredPayload = () => {
        try {
            const rawPayload = window.sessionStorage.getItem(PAGE_SKELETON_KEY);
            return rawPayload ? JSON.parse(rawPayload) : null;
        } catch (error) {
            return null;
        }
    };

    const clearStoredPayload = () => {
        try {
            window.sessionStorage.removeItem(PAGE_SKELETON_KEY);
        } catch (error) {
            // Ignore storage failures and keep navigation functional.
        }
    };

    const getSkeletonColor = () => {
        if (userMotionRoot === 'customer-dashboard') {
            return document.documentElement.dataset.theme === 'dark'
                ? 'rgba(255,255,255,0.08)'
                : 'rgba(24,24,27,0.08)';
        }

        return '#e4e4e7';
    };

    const getBoneY = (bone) => {
        if (Array.isArray(bone)) {
            return Number(bone[1] ?? 0);
        }

        return Number(bone?.y ?? 0);
    };

    const trimSkeletonResult = (result) => {
        const maxHeight = Math.max(360, Math.round(window.innerHeight - 32));
        const trimmedBones = Array.isArray(result?.bones)
            ? result.bones.filter((bone) => getBoneY(bone) < maxHeight)
            : [];

        return {
            ...result,
            height: Math.min(Number(result?.height ?? maxHeight), maxHeight),
            bones: trimmedBones,
        };
    };

    const resolveTarget = () => document.querySelector('[data-boneyard-target]') ?? document.querySelector('[data-user-main]');

    const storePageSkeleton = () => {
        const target = resolveTarget();
        if (!(target instanceof HTMLElement) || target.offsetWidth <= 0 || target.offsetHeight <= 0) {
            clearStoredPayload();
            return;
        }

        try {
            const result = trimSkeletonResult(snapshotBones(target, 'page-loader'));
            if (!result.bones.length || result.height <= 0) {
                clearStoredPayload();
                return;
            }

            const payload = {
                createdAt: Date.now(),
                html: renderBones(result, getSkeletonColor(), !prefersReducedMotion),
                width: Math.max(320, Math.ceil(Number((result.width ?? target.getBoundingClientRect().width) || 0))),
            };

            window.sessionStorage.setItem(PAGE_SKELETON_KEY, JSON.stringify(payload));
        } catch (error) {
            clearStoredPayload();
        }
    };

    const dismissLoader = () => {
        const loader = document.getElementById('boneyardPageLoader');
        if (!loader || loader.dataset.dismissed === 'true') {
            return;
        }

        loader.dataset.dismissed = 'true';
        loader.classList.add('is-leaving');
        window.setTimeout(() => loader.remove(), 260);
    };

    const queueDismissLoader = () => {
        const pendingPayload = readStoredPayload();
        if (!pendingPayload) {
            dismissLoader();
            return;
        }

        if (Date.now() - Number(pendingPayload.createdAt ?? 0) > PAGE_SKELETON_TTL) {
            clearStoredPayload();
            dismissLoader();
            return;
        }

        const fallbackTimer = window.setTimeout(() => {
            clearStoredPayload();
            dismissLoader();
        }, 900);

        const finish = () => {
            window.clearTimeout(fallbackTimer);
            clearStoredPayload();
            window.requestAnimationFrame(() => window.setTimeout(dismissLoader, 80));
        };

        if (document.readyState === 'complete') {
            finish();
            return;
        }

        window.addEventListener('load', finish, { once: true });
    };

    const shouldIgnoreAnchor = (anchor) => {
        if (!(anchor instanceof HTMLAnchorElement)) {
            return true;
        }

        if (
            anchor.hasAttribute('download') ||
            anchor.dataset.boneyardIgnore !== undefined ||
            anchor.closest('[data-boneyard-ignore]') ||
            (anchor.target && anchor.target !== '_self')
        ) {
            return true;
        }

        const href = anchor.getAttribute('href') ?? '';
        if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) {
            return true;
        }

        const url = new URL(anchor.href, window.location.href);
        return (
            url.origin !== window.location.origin ||
            (url.pathname === window.location.pathname && url.search === window.location.search)
        );
    };

    const shouldIgnoreForm = (form) => {
        if (!(form instanceof HTMLFormElement)) {
            return true;
        }

        if (form.dataset.boneyardIgnore !== undefined || form.matches('[data-favorite-form]')) {
            return true;
        }

        const action = form.getAttribute('action') || window.location.href;
        const url = new URL(action, window.location.href);
        return url.origin !== window.location.origin;
    };

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const anchor = event.target.closest('a[href]');
        if (shouldIgnoreAnchor(anchor)) {
            return;
        }

        storePageSkeleton();
    });

    document.addEventListener('submit', (event) => {
        if (event.defaultPrevented) {
            return;
        }

        const form = event.target.closest('form');
        if (shouldIgnoreForm(form)) {
            return;
        }

        storePageSkeleton();
    });

    queueDismissLoader();
}
