<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SkillSoft</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#050505] text-zinc-200 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-[#0f0f11] border border-white/5 p-8 rounded-3xl shadow-2xl">
        <h2 class="text-3xl font-bold text-white mb-2">Create Account</h2>
        <p class="text-zinc-500 mb-8 text-sm">Select your role and get started.</p>

        <form action="/register" method="POST" class="space-y-4">
            @csrf
            <div>
                {{-- <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-2">Choose Role</label> --}}
                <div class="grid grid-cols-2 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="1" class="peer hidden" checked>
                        <div class="p-3 text-center border border-white/5 rounded-xl peer-checked:border-indigo-500 peer-checked:bg-indigo-500/10 transition-all text-sm">Regular User</div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="3" class="peer hidden">
                        <div class="p-3 text-center border border-white/5 rounded-xl peer-checked:border-indigo-500 peer-checked:bg-indigo-500/10 transition-all text-sm">Service Provider</div>
                    </label>
                </div>
            </div>

            <input type="text" name="name" placeholder="Full Name" required class="w-full bg-white/5 border border-white/10 py-3 px-4 rounded-xl focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
            <input type="email" name="email" placeholder="Email" required class="w-full bg-white/5 border border-white/10 py-3 px-4 rounded-xl focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
            <input type="password" name="password" placeholder="Password" required class="w-full bg-white/5 border border-white/10 py-3 px-4 rounded-xl focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required class="w-full bg-white/5 border border-white/10 py-3 px-4 rounded-xl focus:ring-1 focus:ring-indigo-500 outline-none transition-all">

            <button class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-500/20 transition-transform active:scale-95">
                Sign Up
            </button>
        </form>
    </div>

    @include('components.flash-toasts')
</body>
