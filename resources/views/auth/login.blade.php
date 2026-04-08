<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SkillSlot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white px-4 py-8 text-zinc-950 antialiased sm:px-6 lg:px-8" data-user-motion-root="auth-user">
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute inset-x-0 top-0 h-80 bg-gradient-to-b from-zinc-50 via-white to-white"></div>
        <div class="absolute -left-24 top-20 h-64 w-64 rounded-full bg-zinc-100 blur-3xl"></div>
        <div class="absolute right-0 top-10 h-56 w-56 rounded-full bg-stone-100 blur-3xl"></div>
    </div>

    <main class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-[1180px] items-center justify-center" data-user-main>
        <section class="grid w-full gap-8 lg:grid-cols-[minmax(0,1.02fr)_minmax(0,0.9fr)]" data-motion-section>
            <section class="hidden overflow-hidden rounded-[2rem] border border-zinc-200 bg-gradient-to-br from-zinc-950 via-zinc-900 to-zinc-800 px-10 py-12 text-white shadow-[0_28px_80px_-36px_rgba(15,23,42,0.45)] lg:block" data-motion-media>
                <a href="{{ route('site.home') }}" class="inline-flex items-center gap-3 text-sm font-medium text-white/90 transition hover:text-white leading-none" data-motion-kicker data-motion-action>
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-white/15 bg-white/5">
                        <svg viewBox="0 0 56 52" class="block h-9 w-9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M8 42V10l16 8 16-8v32l-16-8-16 8Z" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M24 16c0-3.866 3.134-7 7-7s7 3.134 7 7c0 5.044-7 11-7 11s-7-5.956-7-11Z" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="31" cy="16" r="2.5" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="self-center text-base font-semibold tracking-[-0.03em] leading-none">SkillSlot</span>
                </a>

                <div class="mt-12 max-w-xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-white/60" data-motion-kicker>User-side auth</p>
                    <h1 class="mt-5 text-5xl font-semibold tracking-[-0.05em] text-white" data-motion-title>Clean login and registration in the same marketplace style.</h1>
                    <p class="mt-6 text-base leading-8 text-zinc-300" data-motion-copy>
                        The auth pages now use the same neutral colors, typography, and premium spacing as your user-side homepage,
                        while the underlying login, forgot-password, and provider approval logic remain unchanged.
                    </p>
                </div>

                <div class="mt-10 grid gap-4 sm:grid-cols-2" data-motion-group>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur" data-motion-item data-motion-card>
                        <p class="text-sm font-semibold text-white">Matching colors</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-300">White backgrounds, zinc text, soft shadows, and the same button treatment as the user pages.</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur" data-motion-item data-motion-card>
                        <p class="text-sm font-semibold text-white">System safe</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-300">Existing form actions, IDs, and JavaScript behavior are preserved to avoid breaking auth.</p>
                    </div>
                </div>
            </section>

            <div class="w-full rounded-[2rem] border border-zinc-200 bg-white p-8 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.18)] sm:p-10" data-motion-panel data-motion-card>
                <div class="mb-8 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-zinc-500" data-motion-kicker>Welcome back</p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-[-0.04em] text-zinc-950" data-motion-title>Login to your account</h2>
                        <p class="mt-2 text-sm leading-6 text-zinc-500" data-motion-copy>Use your existing credentials to continue.</p>
                    </div>
                    <a href="{{ route('site.home') }}" class="inline-flex rounded-full border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:border-zinc-300 hover:text-zinc-950 lg:hidden" data-motion-action>
                        Home
                    </a>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" data-motion-card>
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" data-motion-card>
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700" data-motion-card>
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('login.attempt') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-zinc-700">Email address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-400 focus:ring-4 focus:ring-zinc-100"
                            placeholder="name@example.com"
                        >
                    </div>

                    <div class="space-y-2">
                      
                        <label for="password" class="block text-sm font-medium text-zinc-700">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-400 focus:ring-4 focus:ring-zinc-100"
                            placeholder="Enter your password"
                        >
                          <div class="flex items-center justify-between gap-4">
                            <button
                                type="button"
                                id="forgotPasswordLink"
                                class="text-sm font-medium text-zinc-500 transition hover:text-zinc-950"
                                data-motion-action
                            >
                                Forgot password?
                            </button>
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-3 text-sm text-zinc-600">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-300"
                        >
                        <span>Keep me signed in</span>
                    </label>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
                        data-motion-action
                    >
                        Sign in
                    </button>
                </form>

                <div class="mt-8 rounded-[1.75rem] border border-zinc-200 bg-zinc-50 px-5 py-5" data-motion-card>
                    <p class="text-sm font-medium text-zinc-700">Need an account?</p>
                    <p class="mt-1 text-sm leading-6 text-zinc-500">
                        Create a customer or provider account using the same user-side color, font, and spacing system.
                    </p>
                    <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800" data-motion-action>
                            Sign up
                        </a>
                        <a href="{{ route('register.provider') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-200 px-5 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-white" data-motion-action>
                            Become a provider
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div id="forgotEmailModal" class="fixed inset-0 z-[90] hidden" data-motion-modal-root>
        <button type="button" class="absolute inset-0 bg-zinc-950/45 backdrop-blur-sm" data-forgot-close data-motion-modal-overlay></button>
        <div class="relative z-10 mx-auto flex min-h-screen w-full items-center justify-center p-4">
            <div class="w-full max-w-sm rounded-[1.75rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.2)]" data-motion-modal-panel>
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-xl font-semibold tracking-[-0.04em] text-zinc-950">Forgot Password</h3>
                    <button type="button" class="rounded-full border border-zinc-200 px-3 py-1.5 text-xs font-semibold text-zinc-600 transition hover:text-zinc-950" data-forgot-close data-motion-action>Close</button>
                </div>
                <p class="mt-2 text-sm leading-6 text-zinc-500">Enter your email and click verify to receive OTP.</p>

                <div class="mt-5 space-y-3">
                    <label for="forgotPasswordEmail" class="block text-sm font-medium text-zinc-700">
                        Enter Your Email
                    </label>
                    <input
                        id="forgotPasswordEmail"
                        type="email"
                        autocomplete="email"
                        class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-400 focus:ring-4 focus:ring-zinc-100"
                        placeholder="name@example.com"
                    >
                    <button
                        type="button"
                        id="sendOtpButton"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-zinc-800 active:scale-[0.98]"
                        data-motion-action
                    >
                        Verify
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="forgotOtpModal" class="fixed inset-0 z-[90] hidden" data-motion-modal-root>
        <button type="button" class="absolute inset-0 bg-zinc-950/45 backdrop-blur-sm" data-otp-close data-motion-modal-overlay></button>
        <div class="relative z-10 mx-auto flex min-h-screen w-full items-center justify-center p-4">
            <div class="w-full max-w-sm rounded-[1.75rem] border border-zinc-200 bg-white p-6 shadow-[0_28px_80px_-40px_rgba(15,23,42,0.2)]" data-motion-modal-panel>
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-xl font-semibold tracking-[-0.04em] text-zinc-950">Verify OTP</h3>
                    <button type="button" class="rounded-full border border-zinc-200 px-3 py-1.5 text-xs font-semibold text-zinc-600 transition hover:text-zinc-950" data-otp-close data-motion-action>Close</button>
                </div>
                <p class="mt-2 text-sm leading-6 text-zinc-500">Enter the 4-digit OTP sent to your email.</p>

                <div class="mt-5 flex items-center justify-center gap-3">
                    <input data-otp-input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" class="h-12 w-12 rounded-2xl border border-zinc-200 bg-white text-center text-lg font-semibold text-zinc-950 outline-none transition focus:border-zinc-400 focus:ring-4 focus:ring-zinc-100">
                    <input data-otp-input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" class="h-12 w-12 rounded-2xl border border-zinc-200 bg-white text-center text-lg font-semibold text-zinc-950 outline-none transition focus:border-zinc-400 focus:ring-4 focus:ring-zinc-100">
                    <input data-otp-input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" class="h-12 w-12 rounded-2xl border border-zinc-200 bg-white text-center text-lg font-semibold text-zinc-950 outline-none transition focus:border-zinc-400 focus:ring-4 focus:ring-zinc-100">
                    <input data-otp-input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" class="h-12 w-12 rounded-2xl border border-zinc-200 bg-white text-center text-lg font-semibold text-zinc-950 outline-none transition focus:border-zinc-400 focus:ring-4 focus:ring-zinc-100">
                </div>

                <button type="button" id="verifyOtpButton" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-zinc-800" data-motion-action>
                    Verify OTP
                </button>

                <div id="resetPasswordSection" class="hidden mt-5 space-y-4 border-t border-zinc-200 pt-5">
                    <div>
                        <label for="forgotNewPassword" class="mb-2 block text-sm font-medium text-zinc-700">New Password</label>
                        <input
                            id="forgotNewPassword"
                            type="password"
                            autocomplete="new-password"
                            minlength="8"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                            title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-400 focus:ring-4 focus:ring-zinc-100"
                            placeholder="Min 8, upper/lower/number/symbol"
                        >
                    </div>
                    <div>
                        <label for="forgotConfirmPassword" class="mb-2 block text-sm font-medium text-zinc-700">Confirm Password</label>
                        <input
                            id="forgotConfirmPassword"
                            type="password"
                            autocomplete="new-password"
                            minlength="8"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                            title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-400 focus:ring-4 focus:ring-zinc-100"
                            placeholder="Confirm new password"
                        >
                    </div>
                    <button type="button" id="resetPasswordButton" class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-zinc-800" data-motion-action>
                        Set New Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const ensureToastRoot = () => {
                const flashRoot = document.querySelector('#flash-toast-root > div');
                if (flashRoot) return flashRoot;

                let root = document.querySelector('#client-toast-root > div');
                if (root) return root;

                const wrapper = document.createElement('div');
                wrapper.id = 'client-toast-root';
                wrapper.className = 'fixed inset-x-0 bottom-4 z-[130] px-4 pointer-events-none';
                wrapper.innerHTML = '<div class="mx-auto max-w-xl flex flex-col items-stretch gap-2"></div>';
                document.body.appendChild(wrapper);
                return wrapper.querySelector('div');
            };

            const showToast = (text, type = 'error') => {
                const root = ensureToastRoot();
                const styleByType = {
                    success: 'border-emerald-300 bg-emerald-50 text-emerald-700',
                    info: 'border-sky-300 bg-sky-50 text-sky-700',
                    error: 'border-rose-300 bg-rose-50 text-rose-700'
                };

                const toast = document.createElement('div');
                toast.className = `toast-pop border bg-white shadow-lg ${styleByType[type] || styleByType.error}`;
                toast.textContent = text;
                root.appendChild(toast);

                requestAnimationFrame(() => toast.classList.add('toast-pop-show'));
                setTimeout(() => {
                    toast.classList.remove('toast-pop-show');
                    setTimeout(() => toast.remove(), 240);
                }, 1200);
            };

            const csrfToken = document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
            const loginEmailInput = document.querySelector('input[name="email"]');
            const forgotLink = document.getElementById('forgotPasswordLink');
            const forgotEmailModal = document.getElementById('forgotEmailModal');
            const forgotEmailInput = document.getElementById('forgotPasswordEmail');
            const sendOtpButton = document.getElementById('sendOtpButton');
            const otpModal = document.getElementById('forgotOtpModal');
            const verifyOtpButton = document.getElementById('verifyOtpButton');
            const resetPasswordSection = document.getElementById('resetPasswordSection');
            const forgotNewPassword = document.getElementById('forgotNewPassword');
            const forgotConfirmPassword = document.getElementById('forgotConfirmPassword');
            const resetPasswordButton = document.getElementById('resetPasswordButton');
            const otpInputs = Array.from(document.querySelectorAll('[data-otp-input]'));
            const forgotCloseButtons = Array.from(document.querySelectorAll('[data-forgot-close]'));
            const otpCloseButtons = Array.from(document.querySelectorAll('[data-otp-close]'));

            if (!forgotLink || !forgotEmailModal || !forgotEmailInput || !sendOtpButton || !otpModal || !verifyOtpButton || !resetPasswordSection || !forgotNewPassword || !forgotConfirmPassword || !resetPasswordButton || otpInputs.length !== 4) {
                return;
            }

            let pendingEmail = '';
            let verifiedOtp = '';
            const strongPasswordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

            const setOtpState = (state = 'default') => {
                otpInputs.forEach((input) => {
                    input.classList.remove('border-rose-500', 'border-emerald-500');
                    input.classList.add('border-zinc-200');

                    if (state === 'error') {
                        input.classList.remove('border-zinc-200');
                        input.classList.add('border-rose-500');
                    }

                    if (state === 'success') {
                        input.classList.remove('border-zinc-200');
                        input.classList.add('border-emerald-500');
                    }
                });
            };

            const clearOtpInputs = () => {
                otpInputs.forEach((input) => {
                    input.value = '';
                });
                setOtpState();
            };

            const resetPasswordState = () => {
                verifiedOtp = '';
                resetPasswordSection.classList.add('hidden');
                forgotNewPassword.value = '';
                forgotConfirmPassword.value = '';
                resetPasswordButton.disabled = false;
                resetPasswordButton.textContent = 'Set New Password';
                verifyOtpButton.disabled = false;
                verifyOtpButton.textContent = 'Verify OTP';
            };

            const hasVisibleModal = () => {
                return !forgotEmailModal.classList.contains('hidden') || !otpModal.classList.contains('hidden');
            };

            const syncBodyLock = () => {
                document.body.classList.toggle('overflow-hidden', hasVisibleModal());
            };

            const toggleModal = (modal, visible, focusTarget = null, onClosed = null) => {
                const gsap = window.gsap;
                const canAnimate = Boolean(gsap) && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const overlay = modal.querySelector('[data-motion-modal-overlay]');
                const panel = modal.querySelector('[data-motion-modal-panel]');

                if (!canAnimate || !overlay || !panel) {
                    modal.classList.toggle('hidden', !visible);

                    if (!visible) {
                        onClosed?.();
                    }

                    syncBodyLock();

                    if (visible && focusTarget instanceof HTMLElement) {
                        setTimeout(() => focusTarget.focus(), 0);
                    }

                    return;
                }

                gsap.killTweensOf([overlay, panel]);

                if (visible) {
                    modal.classList.remove('hidden');
                    syncBodyLock();
                    gsap.set(overlay, { autoAlpha: 0 });
                    gsap.set(panel, {
                        autoAlpha: 0,
                        y: 20,
                        scale: 0.98,
                        transformOrigin: 'center top',
                    });

                    gsap.timeline({ defaults: { overwrite: 'auto' } })
                        .to(overlay, {
                            autoAlpha: 1,
                            duration: 0.18,
                            ease: 'power2.out',
                        })
                        .to(
                            panel,
                            {
                                autoAlpha: 1,
                                y: 0,
                                scale: 1,
                                duration: 0.26,
                                ease: 'power3.out',
                            },
                            0.02,
                        );

                    if (focusTarget instanceof HTMLElement) {
                        requestAnimationFrame(() => focusTarget.focus());
                    }

                    return;
                }

                gsap.timeline({
                    defaults: { overwrite: 'auto' },
                    onComplete: () => {
                        modal.classList.add('hidden');
                        onClosed?.();
                        syncBodyLock();
                    },
                })
                    .to(panel, {
                        autoAlpha: 0,
                        y: 16,
                        scale: 0.98,
                        duration: 0.2,
                        ease: 'power2.in',
                    })
                    .to(
                        overlay,
                        {
                            autoAlpha: 0,
                            duration: 0.18,
                            ease: 'power2.inOut',
                        },
                        0,
                    );
            };

            const openForgotEmailModal = () => {
                toggleModal(forgotEmailModal, true, forgotEmailInput);
            };

            const closeForgotEmailModal = () => {
                toggleModal(forgotEmailModal, false, null, () => {
                    sendOtpButton.disabled = false;
                    sendOtpButton.textContent = 'Verify';
                });
            };

            const openOtpModal = () => {
                clearOtpInputs();
                resetPasswordState();
                toggleModal(otpModal, true, otpInputs[0]);
            };

            const closeOtpModal = () => {
                toggleModal(otpModal, false, null, () => {
                    clearOtpInputs();
                    resetPasswordState();
                });
            };

            const parseApiError = (payload, fallbackText) => {
                if (payload?.errors) {
                    const firstErrorKey = Object.keys(payload.errors)[0];
                    if (firstErrorKey && payload.errors[firstErrorKey]?.length) {
                        return payload.errors[firstErrorKey][0];
                    }
                }

                return payload?.message || fallbackText;
            };

            forgotLink.addEventListener('click', () => {
                const loginEmail = (loginEmailInput?.value || '').trim();
                if (!(forgotEmailInput.value || '').trim() && loginEmail) {
                    forgotEmailInput.value = loginEmail;
                }
                openForgotEmailModal();
            });

            sendOtpButton.addEventListener('click', async () => {
                const email = (forgotEmailInput.value || '').trim();

                if (!email) {
                    showToast('Email is required.');
                    forgotEmailInput.focus();
                    return;
                }

                sendOtpButton.disabled = true;
                sendOtpButton.textContent = 'Sending...';

                try {
                    const response = await fetch('{{ route('password.forgot.send_otp') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ email })
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(parseApiError(payload, 'Unable to send OTP.'));
                    }

                    pendingEmail = email;
                    closeForgotEmailModal();
                    showToast(payload.message || '4-digit OTP sent to your email.', 'info');
                    openOtpModal();
                } catch (error) {
                    showToast(error.message || 'Unable to send OTP.');
                } finally {
                    sendOtpButton.disabled = false;
                    sendOtpButton.textContent = 'Verify';
                }
            });

            otpInputs.forEach((input, index) => {
                input.addEventListener('input', (event) => {
                    const target = event.target;
                    const value = target.value.replace(/\D/g, '');
                    target.value = value.slice(-1);
                    setOtpState();
                    verifiedOtp = '';
                    resetPasswordSection.classList.add('hidden');
                    verifyOtpButton.disabled = false;
                    verifyOtpButton.textContent = 'Verify OTP';

                    if (target.value && index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                });

                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Backspace' && !input.value && index > 0) {
                        otpInputs[index - 1].focus();
                    }
                });

                input.addEventListener('paste', (event) => {
                    event.preventDefault();
                    const pasted = (event.clipboardData?.getData('text') || '').replace(/\D/g, '').slice(0, 4);
                    if (!pasted) {
                        return;
                    }

                    pasted.split('').forEach((char, charIndex) => {
                        if (otpInputs[charIndex]) {
                            otpInputs[charIndex].value = char;
                        }
                    });

                    const focusIndex = Math.min(pasted.length, otpInputs.length - 1);
                    otpInputs[focusIndex]?.focus();
                    setOtpState();
                });
            });

            verifyOtpButton.addEventListener('click', async () => {
                const email = pendingEmail || (forgotEmailInput.value || '').trim();
                const otp = otpInputs.map((input) => input.value).join('');

                if (!email) {
                    showToast('Email is required.');
                    return;
                }

                if (!/^\d{4}$/.test(otp)) {
                    setOtpState('error');
                    showToast('Enter the 4-digit OTP.');
                    return;
                }

                verifyOtpButton.disabled = true;
                verifyOtpButton.textContent = 'Verifying...';

                let verified = false;

                try {
                    const response = await fetch('{{ route('password.forgot.verify_otp') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            email,
                            otp
                        })
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        setOtpState('error');
                        showToast(parseApiError(payload, 'Invalid OTP.'));
                        return;
                    }

                    verified = true;
                    verifiedOtp = otp;
                    setOtpState('success');
                    verifyOtpButton.disabled = true;
                    verifyOtpButton.textContent = 'Verified';
                    resetPasswordSection.classList.remove('hidden');
                    const revealGsap = window.gsap;
                    const canAnimateReveal = Boolean(revealGsap) && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    if (canAnimateReveal) {
                        revealGsap.fromTo(
                            resetPasswordSection,
                            { autoAlpha: 0, y: 14 },
                            {
                                autoAlpha: 1,
                                y: 0,
                                duration: 0.28,
                                ease: 'power2.out',
                                clearProps: 'opacity,visibility,transform',
                            },
                        );
                    }
                    showToast(payload.message || 'OTP verified. Enter your new password.', 'success');
                    forgotNewPassword.focus();
                } catch (error) {
                    setOtpState('error');
                    showToast(error.message || 'Unable to verify OTP.');
                } finally {
                    if (!verified) {
                        verifyOtpButton.disabled = false;
                        verifyOtpButton.textContent = 'Verify OTP';
                    }
                }
            });

            resetPasswordButton.addEventListener('click', async () => {
                const email = pendingEmail || (forgotEmailInput.value || '').trim();
                const otp = verifiedOtp || otpInputs.map((input) => input.value).join('');
                const password = forgotNewPassword.value || '';
                const confirmPassword = forgotConfirmPassword.value || '';

                if (!email) {
                    showToast('Email is required.');
                    return;
                }

                if (!/^\d{4}$/.test(otp)) {
                    setOtpState('error');
                    showToast('Verify OTP first.');
                    return;
                }

                if (!strongPasswordPattern.test(password)) {
                    showToast('Password must be Strong ');
                    forgotNewPassword.focus();
                    return;
                }

                if (password !== confirmPassword) {
                    showToast('Password confirmation does not match.');
                    forgotConfirmPassword.focus();
                    return;
                }

                resetPasswordButton.disabled = true;
                resetPasswordButton.textContent = 'Updating...';

                try {
                    const response = await fetch('{{ route('password.forgot.reset') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            email,
                            otp,
                            password,
                            password_confirmation: confirmPassword
                        })
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const errorText = parseApiError(payload, 'Unable to reset password.');
                        if (errorText.toLowerCase().includes('otp')) {
                            setOtpState('error');
                        }
                        throw new Error(errorText);
                    }

                    showToast(payload.message || 'Password reset successful. Please login.', 'success');
                    setTimeout(() => {
                        window.location.href = payload.redirect || '{{ route('login') }}';
                    }, 700);
                } catch (error) {
                    showToast(error.message || 'Unable to reset password.');
                    resetPasswordButton.disabled = false;
                    resetPasswordButton.textContent = 'Set New Password';
                }
            });

            forgotCloseButtons.forEach((button) => {
                button.addEventListener('click', () => closeForgotEmailModal());
            });

            otpCloseButtons.forEach((button) => {
                button.addEventListener('click', () => closeOtpModal());
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') {
                    return;
                }

                if (!otpModal.classList.contains('hidden')) {
                    closeOtpModal();
                    return;
                }

                if (!forgotEmailModal.classList.contains('hidden')) {
                    closeForgotEmailModal();
                }
            });
        })();
    </script>

    @include('components.flash-toasts')
</body>
</html>
