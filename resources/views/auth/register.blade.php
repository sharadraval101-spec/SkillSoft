<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | SkillSlot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#050505] text-zinc-200 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-2xl bg-[#0f0f11] border border-white/5 p-8 rounded-3xl shadow-2xl">
        <h2 class="text-3xl font-bold text-white mb-2">Create Your Account</h2>
        <p class="text-zinc-500 mb-8 text-sm">Choose the account type that matches your use case.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('register.customer') }}" class="rounded-2xl border border-white/10 bg-white/5 p-5 hover:border-indigo-400/60 hover:bg-indigo-500/10 transition">
                <h3 class="text-lg font-bold text-white">Customer</h3>
                <p class="mt-2 text-sm text-zinc-400">
                    Book services and manage your upcoming appointments.
                </p>
                <span class="mt-4 inline-block text-xs font-semibold text-indigo-300 uppercase tracking-widest">Register as Customer</span>
            </a>

            <a href="{{ route('register.provider') }}" class="rounded-2xl border border-white/10 bg-white/5 p-5 hover:border-cyan-400/60 hover:bg-cyan-500/10 transition">
                <h3 class="text-lg font-bold text-white">Provider</h3>
                <p class="mt-2 text-sm text-zinc-400">
                    Offer services and manage availability. Requires admin approval.
                </p>
                <span class="mt-4 inline-block text-xs font-semibold text-cyan-300 uppercase tracking-widest">Register as Provider</span>
            </a>
        </div>

        <div class="mt-8 pt-6 border-t border-white/5 text-sm text-zinc-400 text-center">
            Already have an account?
            <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold">Sign In</a>
        </div>
    </div>

    @include('components.flash-toasts')
</body>
</html>
