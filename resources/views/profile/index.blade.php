@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-r from-zinc-900/80 to-zinc-900/60 p-6 lg:p-8 shadow-2xl shadow-black/40">
        <div class="absolute -top-16 right-16 h-52 w-52 rounded-full bg-cyan-500/15 blur-3xl pointer-events-none"></div>
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Account Center</p>
        <h1 class="mt-3 text-3xl lg:text-4xl font-black text-white tracking-tight">Profile Settings</h1>
        <p class="mt-2 text-zinc-400 max-w-2xl">Update account details, change your profile photo, and reset your password using email verification code.</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <aside class="xl:col-span-1 space-y-6">
            <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
                <div class="text-center">
                    <img id="profilePhotoPreview"
                        src="{{ $user->profile_photo_url ?? '' }}"
                        alt="Profile photo"
                        class="w-28 h-28 rounded-2xl object-cover mx-auto ring-2 ring-cyan-400/50 {{ $user->profile_photo_url ? '' : 'hidden' }}">
                    <div id="profilePhotoInitial" class="w-28 h-28 rounded-2xl bg-indigo-600 mx-auto flex items-center justify-center text-4xl font-black text-white ring-2 ring-indigo-300/40 {{ $user->profile_photo_url ? 'hidden' : '' }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>

                <div class="mt-5 text-center">
                    <h2 class="text-2xl font-bold text-white">{{ $user->name }}</h2>
                    <p class="text-sm text-zinc-400 mt-1">{{ $user->email }}</p>
                    <span class="inline-flex mt-3 rounded-full bg-cyan-500/10 border border-cyan-500/20 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-cyan-300">
                        {{ $user->getRoleName() }}
                    </span>
                </div>

                <div class="mt-6 border-t border-white/10 pt-4 text-center">
                    <p class="text-xs uppercase tracking-widest font-semibold text-zinc-500">Joined</p>
                    <p class="mt-1 text-zinc-200">{{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </section>

            <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 shadow-xl shadow-black/30">
                <h3 class="text-white font-bold">Security Tip</h3>
                <p class="mt-2 text-sm text-zinc-400">
                    Reset codes expire in 10 minutes. Use only the latest email code and avoid sharing it.
                </p>
            </section>
        </aside>

        <div class="xl:col-span-2 space-y-6">
            <form id="profileForm" data-has-photo="{{ $user->profile_photo_path ? '1' : '0' }}" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 lg:p-8 shadow-xl shadow-black/30 space-y-6">
                @csrf

                <div>
                    <h3 class="text-xl font-bold text-white">Profile Information</h3>
                    <p class="text-sm text-zinc-400 mt-1">Keep your account details and avatar up to date.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-2 ml-1">Full Name</label>
                        <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                            class="w-full bg-black/40 border border-white/10 py-3 px-4 rounded-xl text-white focus:ring-1 focus:ring-cyan-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-2 ml-1">Email Address</label>
                        <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                            class="w-full bg-black/40 border border-white/10 py-3 px-4 rounded-xl text-white focus:ring-1 focus:ring-cyan-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-2 ml-1">Profile Photo</label>
                    <input id="profilePhotoInput" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp" @if(!$user->profile_photo_path) required @endif
                        class="w-full bg-black/30 border border-dashed border-white/20 py-3 px-4 rounded-xl text-sm text-zinc-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cyan-500/20 file:text-cyan-200 hover:file:bg-cyan-500/30">
                    <p class="mt-2 text-xs text-zinc-500">JPG, PNG, WEBP up to 2MB.</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 text-white font-bold px-6 py-3 rounded-xl transition active:scale-95 shadow-lg shadow-cyan-700/30">
                        Save Profile
                    </button>
                </div>
            </form>

            <section class="rounded-3xl border border-white/10 bg-zinc-900/70 p-6 lg:p-8 shadow-xl shadow-black/30 space-y-6">
                <div>
                    <h3 class="text-xl font-bold text-white">Reset Password with Email Code</h3>
                    <p class="text-sm text-zinc-400 mt-1">Send a verification code to your registered email and use it below to set a new password.</p>
                </div>

                <form action="{{ route('profile.password.send_code') }}" method="POST" class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    @csrf
                    <p class="text-sm text-zinc-300">Registered email: <span class="font-semibold">{{ $user->email }}</span></p>
                    <button type="submit" class="mt-4 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                        Send Verification Code
                    </button>
                </form>

                <form id="passwordResetForm" action="{{ route('profile.password.reset_by_code') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-2 ml-1">Verification Code</label>
                        <input type="text" name="code" required pattern="[0-9]{6}" value="{{ old('code') }}" maxlength="6" inputmode="numeric" placeholder="Enter 6-digit code"
                            class="w-full bg-black/40 border border-white/10 py-3 px-4 rounded-xl text-white tracking-[0.2em] focus:ring-1 focus:ring-cyan-500 outline-none transition">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-2 ml-1">New Password</label>
                            <input type="password" name="password" required minlength="8"
                                class="w-full bg-black/40 border border-white/10 py-3 px-4 rounded-xl text-white focus:ring-1 focus:ring-cyan-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-2 ml-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" required minlength="8"
                                class="w-full bg-black/40 border border-white/10 py-3 px-4 rounded-xl text-white focus:ring-1 focus:ring-cyan-500 outline-none transition">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-3 rounded-xl transition active:scale-95 shadow-lg shadow-indigo-700/30">
                            Verify Code & Reset Password
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const ensureToastRoot = () => {
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

            const profileForm = document.getElementById('profileForm');
            const passwordResetForm = document.getElementById('passwordResetForm');
            const photoInput = document.getElementById('profilePhotoInput');
            const photoPreview = document.getElementById('profilePhotoPreview');
            const photoInitial = document.getElementById('profilePhotoInitial');

            if (photoInput && photoPreview && photoInitial) {
                photoInput.addEventListener('change', () => {
                    const file = photoInput.files && photoInput.files[0] ? photoInput.files[0] : null;
                    if (!file) return;

                    if (!file.type.startsWith('image/')) {
                        showToast('Please select a valid image file.');
                        photoInput.value = '';
                        return;
                    }

                    const previewUrl = URL.createObjectURL(file);
                    photoPreview.src = previewUrl;
                    photoPreview.classList.remove('hidden');
                    photoInitial.classList.add('hidden');
                });
            }

            if (profileForm) {
                profileForm.addEventListener('submit', (event) => {
                    const hasPhoto = profileForm.dataset.hasPhoto === '1';
                    const name = (profileForm.querySelector('input[name="name"]')?.value || '').trim();
                    const email = (profileForm.querySelector('input[name="email"]')?.value || '').trim();
                    const hasNewImage = photoInput && photoInput.files && photoInput.files.length > 0;

                    if (!name) {
                        event.preventDefault();
                        showToast('Name is required.');
                        return;
                    }

                    if (!email) {
                        event.preventDefault();
                        showToast('Email is required.');
                        return;
                    }

                    if (!hasPhoto && !hasNewImage) {
                        event.preventDefault();
                        showToast('Please attach a profile image before saving.');
                    }
                });
            }

            if (passwordResetForm) {
                passwordResetForm.addEventListener('submit', (event) => {
                    const code = (passwordResetForm.querySelector('input[name="code"]')?.value || '').trim();
                    const password = passwordResetForm.querySelector('input[name="password"]')?.value || '';
                    const confirmPassword = passwordResetForm.querySelector('input[name="password_confirmation"]')?.value || '';

                    if (!/^\d{6}$/.test(code)) {
                        event.preventDefault();
                        showToast('Verification code must be exactly 6 digits.');
                        return;
                    }

                    if (password.length < 8) {
                        event.preventDefault();
                        showToast('Password must be at least 8 characters.');
                        return;
                    }

                    if (password !== confirmPassword) {
                        event.preventDefault();
                        showToast('Password confirmation does not match.');
                    }
                });
            }
        })();
    </script>
@endpush
