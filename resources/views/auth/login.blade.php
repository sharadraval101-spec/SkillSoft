<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SkillSlot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#050505] text-zinc-200 min-h-screen flex items-center justify-center p-6">
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none -z-10">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-indigo-500/10 blur-[120px] rounded-full"></div>
    </div>

    <div class="w-full max-w-md bg-[#0f0f11] border border-white/5 p-10 rounded-3xl shadow-2xl relative">
        <div class="mb-10 text-center">
            <h2 class="text-3xl font-bold text-white tracking-tight">Welcome Back</h2>
            <p class="text-zinc-500 mt-2">Enter your credentials to access SkillSlot</p>
        </div>

        <form action="/login" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-widest mb-2 ml-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full bg-white/5 border border-white/10 py-3.5 px-4 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder:text-zinc-700"
                    placeholder="name@example.com">
            </div>

            <div>
                <div class="flex justify-between mb-2 ml-1">
                    <label class="text-xs font-semibold text-zinc-400 uppercase tracking-widest">Password</label>
                </div>
                <input type="password" name="password" required
                class="w-full bg-white/5 border border-white/10 py-3.5 px-4 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder:text-zinc-700"
                placeholder="********">
            </div>

            <div class="flex items-center px-1">
                <label class="flex items-center cursor-pointer group">
                    <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded border-white/10 bg-white/5 text-indigo-600 focus:ring-0 focus:ring-offset-0 transition-colors">
                    <span class="ml-3 text-sm text-zinc-500 group-hover:text-zinc-300 transition-colors">Stay logged in</span>
                </label>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-500/20 transition-transform active:scale-95">
                Sign In
            </button>

            <div class="text-center">
                <button
                    type="button"
                    id="forgotPasswordLink"
                    class="text-xs font-semibold uppercase tracking-wider text-indigo-400 hover:text-indigo-300 transition"
                >
                    Forgot Password?
                </button>
            </div>
        </form>

        <div class="mt-10 pt-6 border-t border-white/5 text-center">
            <p class="text-zinc-500 text-sm">
                New to the platform? <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold ml-1">Create account</a>
            </p>
        </div>
    </div>

    <div id="forgotEmailModal" class="fixed inset-0 z-[90] hidden">
        <button type="button" class="absolute inset-0 bg-black/70" data-forgot-close></button>
        <div class="relative z-10 mx-auto flex min-h-screen w-full items-center justify-center p-4">
            <div class="w-full max-w-sm rounded-2xl border border-white/10 bg-zinc-900 shadow-2xl shadow-black/40 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Forgot Password</h3>
                    <button type="button" class="text-xs font-semibold text-zinc-400 hover:text-zinc-200" data-forgot-close>Close</button>
                </div>
                <p class="mt-2 text-sm text-zinc-400">Enter your email and click verify to receive OTP.</p>

                <div class="mt-5 space-y-3">
                    <label for="forgotPasswordEmail" class="block text-xs font-semibold uppercase tracking-widest text-zinc-500">
                        Enter Your Email
                    </label>
                    <input
                        id="forgotPasswordEmail"
                        type="email"
                        autocomplete="email"
                        class="w-full bg-white/5 border border-white/10 py-3 px-4 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder:text-zinc-700"
                        placeholder="name@example.com"
                    >
                    <button
                        type="button"
                        id="sendOtpButton"
                        class="w-full bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold py-3 rounded-xl transition active:scale-[0.98]"
                    >
                        Verify
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="forgotOtpModal" class="fixed inset-0 z-[90] hidden">
        <button type="button" class="absolute inset-0 bg-black/70" data-otp-close></button>
        <div class="relative z-10 mx-auto flex min-h-screen w-full items-center justify-center p-4">
            <div class="w-full max-w-sm rounded-2xl border border-white/10 bg-zinc-900 shadow-2xl shadow-black/40 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Verify OTP</h3>
                    <button type="button" class="text-xs font-semibold text-zinc-400 hover:text-zinc-200" data-otp-close>Close</button>
                </div>
                <p class="mt-2 text-sm text-zinc-400">Enter the 4-digit OTP sent to your email.</p>

                <div class="mt-5 flex items-center justify-center gap-2">
                    <input data-otp-input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" class="w-12 h-12 text-center rounded-lg bg-black/30 border border-white/10 text-white text-lg font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <input data-otp-input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" class="w-12 h-12 text-center rounded-lg bg-black/30 border border-white/10 text-white text-lg font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <input data-otp-input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" class="w-12 h-12 text-center rounded-lg bg-black/30 border border-white/10 text-white text-lg font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <input data-otp-input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" class="w-12 h-12 text-center rounded-lg bg-black/30 border border-white/10 text-white text-lg font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <button type="button" id="verifyOtpButton" class="mt-6 w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl transition active:scale-95">
                    Verify OTP
                </button>

                <div id="resetPasswordSection" class="hidden mt-4 space-y-3">
                    <div>
                        <label for="forgotNewPassword" class="block text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-2">New Password</label>
                        <input
                            id="forgotNewPassword"
                            type="password"
                            autocomplete="new-password"
                            minlength="8"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                            title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                            class="w-full bg-white/5 border border-white/10 py-3 px-4 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder:text-zinc-700"
                            placeholder="Min 8, upper/lower/number/symbol"
                        >
                    </div>
                    <div>
                        <label for="forgotConfirmPassword" class="block text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-2">Confirm Password</label>
                        <input
                            id="forgotConfirmPassword"
                            type="password"
                            autocomplete="new-password"
                            minlength="8"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                            title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                            class="w-full bg-white/5 border border-white/10 py-3 px-4 rounded-xl focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder:text-zinc-700"
                            placeholder="Confirm new password"
                        >
                    </div>
                    <button type="button" id="resetPasswordButton" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl transition active:scale-95">
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
                    success: 'border-emerald-400/40 bg-emerald-500/15 text-emerald-100',
                    info: 'border-cyan-400/40 bg-cyan-500/15 text-cyan-100',
                    error: 'border-rose-400/40 bg-rose-500/15 text-rose-100'
                };

                const toast = document.createElement('div');
                toast.className = `toast-pop border backdrop-blur-md shadow-lg ${styleByType[type] || styleByType.error}`;
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
                    input.classList.add('border-white/10');

                    if (state === 'error') {
                        input.classList.remove('border-white/10');
                        input.classList.add('border-rose-500');
                    }

                    if (state === 'success') {
                        input.classList.remove('border-white/10');
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

            const openForgotEmailModal = () => {
                forgotEmailModal.classList.remove('hidden');
                syncBodyLock();
                forgotEmailInput.focus();
            };

            const closeForgotEmailModal = () => {
                forgotEmailModal.classList.add('hidden');
                sendOtpButton.disabled = false;
                sendOtpButton.textContent = 'Verify';
                syncBodyLock();
            };

            const openOtpModal = () => {
                otpModal.classList.remove('hidden');
                syncBodyLock();
                clearOtpInputs();
                resetPasswordState();
                setTimeout(() => otpInputs[0]?.focus(), 0);
            };

            const closeOtpModal = () => {
                otpModal.classList.add('hidden');
                clearOtpInputs();
                resetPasswordState();
                syncBodyLock();
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
