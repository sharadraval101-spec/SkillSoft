<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Provider Registration | SkillSlot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#050505] text-zinc-200 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-[#0f0f11] border border-white/5 p-8 rounded-3xl shadow-2xl">
        <h2 class="text-3xl font-bold text-white mb-2">Provider Sign Up</h2>
        <p class="text-zinc-500 mb-8 text-sm">Provider accounts require admin approval before dashboard access.</p>

        <form action="{{ route('register.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_PROVIDER }}">

            <input type="text" name="name" value="{{ old('name') }}" placeholder="Full Name" required class="w-full bg-white/5 border border-white/10 py-3 px-4 rounded-xl focus:ring-1 focus:ring-cyan-500 outline-none transition-all">
            <input type="text" name="business_name" value="{{ old('business_name') }}" placeholder="Business Name" required class="w-full bg-white/5 border border-white/10 py-3 px-4 rounded-xl focus:ring-1 focus:ring-cyan-500 outline-none transition-all">
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required class="w-full bg-white/5 border border-white/10 py-3 px-4 rounded-xl focus:ring-1 focus:ring-cyan-500 outline-none transition-all">
            <input type="password" name="password" placeholder="Password" required minlength="8"
                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                class="w-full bg-white/5 border border-white/10 py-3 px-4 rounded-xl focus:ring-1 focus:ring-cyan-500 outline-none transition-all">
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required minlength="8"
                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                class="w-full bg-white/5 border border-white/10 py-3 px-4 rounded-xl focus:ring-1 focus:ring-cyan-500 outline-none transition-all">
            <p class="text-xs text-zinc-500">Use 8+ characters with uppercase, lowercase, number, and special character.</p>

            <button class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-cyan-500/20 transition-transform active:scale-95">
                Submit Provider Registration
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-zinc-400">
            Already have an account?
            <a href="{{ route('login') }}" class="text-cyan-400 hover:text-cyan-300 font-semibold">Sign In</a>
        </div>
    </div>

    @include('components.flash-toasts')
</body>
</html>
