import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;

const body = document.body;
const userMotionRoot = body?.dataset.userMotionRoot ?? '';

const prefersReducedMotion = userMotionRoot
    ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
    : true;
const canHover = userMotionRoot
    ? window.matchMedia('(hover: hover) and (pointer: fine)').matches
    : false;
const canAnimate = Boolean(userMotionRoot) && !prefersReducedMotion;
const clearMotionProps = 'opacity,visibility,transform';

if (userMotionRoot) {
    gsap.registerPlugin(ScrollTrigger);
    document.documentElement.classList.add('js-user-motion');
}

const toUniqueElements = (elements) =>
    [...new Set(elements.filter((element) => element instanceof HTMLElement))];

const queryElements = (root, selectors) =>
    toUniqueElements(selectors.flatMap((selector) => Array.from(root.querySelectorAll(selector))));

const refreshScrollTriggers = () => {
    if (!canAnimate) {
        return;
    }

    requestAnimationFrame(() => ScrollTrigger.refresh());
};

const bindInteractiveMotion = (element) => {
    if (!canHover || !canAnimate || !(element instanceof HTMLElement) || element.dataset.motionHoverBound === 'true') {
        return;
    }

    element.dataset.motionHoverBound = 'true';

    const yOffset = element.hasAttribute('data-motion-card') ? -8 : -3;
    const scale = element.hasAttribute('data-motion-card') ? 1.01 : 1.02;

    element.addEventListener('pointerenter', () => {
        gsap.to(element, {
            y: yOffset,
            scale,
            duration: 0.28,
            ease: 'power2.out',
            overwrite: 'auto',
        });
    });

    element.addEventListener('pointerleave', () => {
        gsap.to(element, {
            y: 0,
            scale: 1,
            duration: 0.28,
            ease: 'power2.out',
            overwrite: 'auto',
        });
    });
};

const animatePageChrome = () => {
    if (!canAnimate) {
        return;
    }

    const header = document.querySelector('[data-motion-header]');

    if (!header) {
        return;
    }

    const targets = queryElements(header, [
        '[data-motion-brand]',
        '[data-motion-nav-item]',
        '[data-motion-utility]',
        '[data-motion-menu-trigger]',
    ]);

    gsap.from(targets.length ? targets : header, {
        y: -18,
        autoAlpha: 0,
        duration: 0.72,
        stagger: targets.length ? 0.06 : 0,
        ease: 'power3.out',
        clearProps: clearMotionProps,
    });
};

const animateSections = () => {
    if (!canAnimate) {
        return;
    }

    const sections = gsap.utils.toArray('[data-motion-section]');

    sections.forEach((section, index) => {
        const textTargets = queryElements(section, [
            '[data-motion-kicker]',
            '[data-motion-title]',
            '[data-motion-copy]',
            '[data-motion-actions]',
            '[data-motion-panel]',
        ]);

        const mediaTargets = queryElements(section, [
            '[data-motion-media]',
            '[data-motion-stats]',
            '[data-motion-aside]',
        ]);

        if (!textTargets.length && !mediaTargets.length) {
            return;
        }

        const timeline = gsap.timeline({
            defaults: { ease: 'power3.out' },
            scrollTrigger: {
                trigger: section,
                start: index === 0 ? 'top 92%' : 'top 84%',
                once: true,
            },
        });

        if (textTargets.length) {
            timeline.from(textTargets, {
                y: 36,
                autoAlpha: 0,
                duration: 0.82,
                stagger: 0.08,
                clearProps: clearMotionProps,
            });
        }

        if (mediaTargets.length) {
            timeline.from(
                mediaTargets,
                {
                    y: 42,
                    autoAlpha: 0,
                    scale: 0.96,
                    duration: 0.95,
                    stagger: 0.1,
                    clearProps: clearMotionProps,
                },
                textTargets.length ? '-=0.5' : 0,
            );
        }
    });
};

const animateGroups = () => {
    if (!canAnimate) {
        return;
    }

    const groups = gsap.utils.toArray('[data-motion-group]');

    groups.forEach((group) => {
        const items = group.querySelectorAll('[data-motion-item]');

        if (!items.length) {
            return;
        }

        gsap.from(items, {
            y: 44,
            autoAlpha: 0,
            duration: 0.82,
            stagger: 0.1,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: group,
                start: 'top 86%',
                once: true,
            },
            clearProps: clearMotionProps,
        });
    });
};

const animateStandaloneCards = () => {
    if (!canAnimate) {
        return;
    }

    const cards = gsap
        .utils.toArray('[data-motion-card]')
        .filter((card) => !card.closest('[data-motion-group]'));

    cards.forEach((card) => {
        gsap.from(card, {
            y: 28,
            autoAlpha: 0,
            duration: 0.72,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: card,
                start: 'top 88%',
                once: true,
            },
            clearProps: clearMotionProps,
        });
    });
};

const animateFallbackBlocks = () => {
    if (!canAnimate) {
        return;
    }

    const fallbackBlocks = toUniqueElements([
        ...Array.from(document.querySelectorAll('[data-user-main] > section')),
        ...Array.from(document.querySelectorAll('[data-user-main] > div > section')),
        ...Array.from(document.querySelectorAll('[data-user-main] .dashboard-panel')),
        ...Array.from(document.querySelectorAll('[data-user-main] .dashboard-card')),
    ]).filter(
        (element) =>
            !element.hasAttribute('data-motion-section') &&
            !element.hasAttribute('data-motion-card') &&
            !element.closest('[data-motion-section]') &&
            !element.closest('[data-motion-group]'),
    );

    fallbackBlocks.forEach((block) => {
        gsap.from(block, {
            y: 26,
            autoAlpha: 0,
            duration: 0.72,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: block,
                start: 'top 88%',
                once: true,
            },
            clearProps: clearMotionProps,
        });
    });
};

const animateFooter = () => {
    const footer = document.querySelector('[data-motion-footer]');

    if (!footer || !canAnimate) {
        return;
    }

    const targets = queryElements(footer, [
        '[data-motion-brand]',
        '[data-motion-link]',
        '[data-motion-social]',
    ]);

    gsap.from(targets.length ? targets : footer, {
        y: 30,
        autoAlpha: 0,
        duration: 0.8,
        stagger: targets.length ? 0.08 : 0,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: footer,
            start: 'top bottom-=40',
            once: true,
        },
        clearProps: clearMotionProps,
    });
};

const animateMediaParallax = () => {
    if (!canAnimate) {
        return;
    }

    gsap.utils.toArray('[data-motion-media]').forEach((media) => {
        gsap.to(media, {
            yPercent: -6,
            ease: 'none',
            scrollTrigger: {
                trigger: media,
                start: 'top bottom',
                end: 'bottom top',
                scrub: 0.8,
            },
        });
    });
};

const initDetailsMenus = () => {
    if (!canAnimate) {
        return;
    }

    document.querySelectorAll('[data-motion-menu]').forEach((menu) => {
        const panel = menu.querySelector('[data-motion-menu-panel]');

        if (!panel) {
            return;
        }

        menu.addEventListener('toggle', () => {
            if (!menu.open) {
                gsap.set(panel, { clearProps: clearMotionProps });
                return;
            }

            gsap.fromTo(
                panel,
                {
                    autoAlpha: 0,
                    y: -10,
                    scale: 0.985,
                    transformOrigin: 'top right',
                },
                {
                    autoAlpha: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.24,
                    ease: 'power2.out',
                    clearProps: clearMotionProps,
                },
            );
        });
    });
};

const initFilterDrawers = () => {
    const drawers = document.querySelectorAll('[data-filter-drawer]');

    drawers.forEach((drawer) => {
        const drawerName = drawer.dataset.filterDrawer;
        const overlay = document.querySelector(`[data-filter-overlay="${drawerName}"]`);
        const openButtons = document.querySelectorAll(`[data-filter-open="${drawerName}"]`);
        const closeButtons = document.querySelectorAll(`[data-filter-close="${drawerName}"]`);

        if (!drawerName || !overlay || !openButtons.length || !closeButtons.length) {
            return;
        }

        let isOpen = false;

        if (canAnimate) {
            drawer.classList.remove('translate-x-full');
            overlay.classList.remove('pointer-events-none', 'opacity-0');
            gsap.set(drawer, { xPercent: 100 });
            gsap.set(overlay, { autoAlpha: 0, pointerEvents: 'none' });
        }

        const syncExpandedState = (expanded) => {
            openButtons.forEach((button) => {
                button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            });
        };

        const openDrawer = () => {
            if (isOpen) {
                return;
            }

            isOpen = true;
            syncExpandedState(true);

            if (!canAnimate) {
                drawer.classList.remove('translate-x-full');
                overlay.classList.remove('pointer-events-none', 'opacity-0');
                return;
            }

            gsap.killTweensOf([drawer, overlay]);
            gsap.to(overlay, {
                autoAlpha: 1,
                pointerEvents: 'auto',
                duration: 0.22,
                ease: 'power2.out',
                overwrite: 'auto',
            });
            gsap.to(drawer, {
                xPercent: 0,
                duration: 0.36,
                ease: 'power3.out',
                overwrite: 'auto',
            });
        };

        const closeDrawer = () => {
            if (!isOpen) {
                return;
            }

            isOpen = false;
            syncExpandedState(false);

            if (!canAnimate) {
                drawer.classList.add('translate-x-full');
                overlay.classList.add('pointer-events-none', 'opacity-0');
                return;
            }

            gsap.killTweensOf([drawer, overlay]);
            gsap.to(drawer, {
                xPercent: 100,
                duration: 0.3,
                ease: 'power3.inOut',
                overwrite: 'auto',
            });
            gsap.to(overlay, {
                autoAlpha: 0,
                duration: 0.2,
                ease: 'power2.inOut',
                overwrite: 'auto',
                onComplete: () => {
                    gsap.set(overlay, { pointerEvents: 'none' });
                },
            });
        };

        syncExpandedState(false);
        openButtons.forEach((button) => button.addEventListener('click', openDrawer));
        closeButtons.forEach((button) => button.addEventListener('click', closeDrawer));
        overlay.addEventListener('click', closeDrawer);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDrawer();
            }
        });
    });
};

const initFavoriteToggles = () => {
    const syncFavoritesCount = (count) => {
        document.querySelectorAll('[data-favorites-count], [data-favorites-total]').forEach((element) => {
            element.textContent = String(count);
        });
    };

    const applyFavoriteState = (serviceId, liked) => {
        document.querySelectorAll(`[data-favorite-form][data-service-id="${serviceId}"]`).forEach((form) => {
            const button = form.querySelector('[data-favorite-button]');
            const icon = form.querySelector('[data-favorite-icon]');

            if (!button || !icon) {
                return;
            }

            button.dataset.liked = liked ? 'true' : 'false';
            button.setAttribute('aria-pressed', liked ? 'true' : 'false');
            button.setAttribute(
                'aria-label',
                liked ? button.dataset.labelLiked ?? 'Remove from liked services' : button.dataset.labelUnliked ?? 'Add to liked services',
            );

            button.classList.toggle('border-rose-200', liked);
            button.classList.toggle('bg-rose-50', liked);
            button.classList.toggle('text-rose-500', liked);

            button.classList.toggle('border-zinc-200', !liked);
            button.classList.toggle('text-zinc-400', !liked);
            button.classList.toggle('hover:border-rose-300', !liked);
            button.classList.toggle('hover:bg-rose-50', !liked);
            button.classList.toggle('hover:text-rose-500', !liked);

            icon.setAttribute('fill', liked ? 'currentColor' : 'none');
        });
    };

    const updateFavoritesPageState = (serviceId, liked) => {
        if (liked) {
            return;
        }

        const card = document.querySelector(`[data-favorite-card][data-service-id="${serviceId}"]`);
        if (!card) {
            return;
        }

        const grid = card.closest('[data-favorites-grid]');
        if (!grid) {
            return;
        }

        const browseUrl = grid.dataset.browseUrl ?? '/services';
        const homeUrl = grid.dataset.homeUrl ?? '/';

        const removeCard = () => {
            card.remove();

            if (grid.querySelector('[data-favorite-card]')) {
                refreshScrollTriggers();
                return;
            }

            const emptyState = document.createElement('div');
            emptyState.className = 'col-span-full rounded-[32px] border border-dashed border-zinc-300 bg-white px-8 py-14 text-center shadow-[0_18px_50px_rgba(15,23,42,0.04)]';
            emptyState.setAttribute('data-motion-card', '');
            emptyState.setAttribute('data-favorites-empty', '');
            emptyState.innerHTML = `
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-400">No Likes Yet</p>
                <h3 class="mt-4 text-[2rem] font-semibold tracking-[-0.04em] text-zinc-950">You have not saved any services</h3>
                <p class="mx-auto mt-4 max-w-2xl text-[15px] leading-7 text-zinc-500">
                    Tap the heart icon on any service card to add it here. Your liked collection will stay ready for future browsing and booking.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="${browseUrl}" class="inline-flex min-w-[160px] items-center justify-center rounded-[14px] bg-zinc-950 px-5 py-3.5 text-sm font-medium text-white transition hover:bg-zinc-800" data-motion-action>Browse Services</a>
                    <a href="${homeUrl}" class="inline-flex min-w-[160px] items-center justify-center rounded-[14px] border border-zinc-300 px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950" data-motion-action>Back to Home</a>
                </div>
            `;
            grid.appendChild(emptyState);
            bindInteractiveMotion(emptyState);
            emptyState.querySelectorAll('[data-motion-action]').forEach(bindInteractiveMotion);

            if (canAnimate) {
                gsap.from(emptyState, {
                    y: 18,
                    autoAlpha: 0,
                    duration: 0.42,
                    ease: 'power2.out',
                    clearProps: clearMotionProps,
                });
            }

            refreshScrollTriggers();
        };

        if (canAnimate) {
            gsap.to(card, {
                autoAlpha: 0,
                y: 18,
                duration: 0.25,
                ease: 'power2.inOut',
                onComplete: removeCard,
            });
            return;
        }

        removeCard();
    };

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-favorite-form]');
        if (!form) {
            return;
        }

        event.preventDefault();

        const button = form.querySelector('[data-favorite-button]');
        if (!button || button.disabled) {
            return;
        }

        const serviceId = form.dataset.serviceId;
        button.disabled = true;
        button.classList.add('opacity-70', 'cursor-not-allowed');

        try {
            const response = await window.axios.post(form.action, new FormData(form), {
                headers: {
                    Accept: 'application/json',
                },
            });

            const payload = response.data?.data ?? {};
            const liked = Boolean(payload.liked);
            const likedCount = Number(payload.liked_count ?? 0);

            applyFavoriteState(serviceId, liked);
            syncFavoritesCount(likedCount);
            updateFavoritesPageState(serviceId, liked);

            window.showFlashToast?.(liked ? 'success' : 'info', response.data?.message ?? 'Liked services updated.');
        } catch (error) {
            window.showFlashToast?.(
                'error',
                error.response?.data?.message ?? 'Unable to update liked services right now. Please try again.',
            );
        } finally {
            button.disabled = false;
            button.classList.remove('opacity-70', 'cursor-not-allowed');
        }
    });
};

const setupInteractiveMotion = () => {
    if (!canHover || !canAnimate) {
        return;
    }

    document.querySelectorAll('[data-motion-card], [data-motion-action]').forEach(bindInteractiveMotion);
};

if (userMotionRoot) {
    animatePageChrome();
    animateSections();
    animateGroups();
    animateStandaloneCards();
    animateFallbackBlocks();
    animateFooter();
    animateMediaParallax();
    initDetailsMenus();
    initFilterDrawers();
    initFavoriteToggles();
    setupInteractiveMotion();

    window.addEventListener('load', refreshScrollTriggers, { once: true });
}
