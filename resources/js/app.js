import './bootstrap';

const motionRoot = document.body?.dataset.userMotionRoot ?? '';
const skeletonEnabledRoots = new Set(['customer-site', 'auth-user', 'customer-dashboard']);

if (motionRoot) {
    window.skillslotMotionReady = import('./site-animations');
}

if (skeletonEnabledRoots.has(motionRoot)) {
    window.skillslotPageSkeletonsReady = import('./page-skeletons');
}
