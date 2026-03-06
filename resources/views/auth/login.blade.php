<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SkillSoft</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#050505] text-zinc-200 min-h-screen flex items-center justify-center p-6">
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none -z-10">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-indigo-500/10 blur-[120px] rounded-full"></div>
    </div>

    <div class="w-full max-w-md bg-[#0f0f11] border border-white/5 p-10 rounded-3xl shadow-2xl relative">
        <div class="mb-10 text-center">
            <h2 class="text-3xl font-bold text-white tracking-tight">Welcome Back</h2>
            <p class="text-zinc-500 mt-2">Enter your credentials to access SkillSoft</p>
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
                placeholder="••••••••">
            </div>
            {{-- <a href="#" class="text-xs text-right text-indigo-400 hover:text-indigo-300">Forgot?</a> --}}

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
        </form>

        <div class="mt-10 pt-6 border-t border-white/5 text-center">
            <p class="text-zinc-500 text-sm">
                New to the platform? <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold ml-1">Create account</a>
            </p>
        </div>
    </div>

    @include('components.flash-toasts')
</body>
</html>
