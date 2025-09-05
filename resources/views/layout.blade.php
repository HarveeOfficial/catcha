<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CatchA</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="font-sans bg-slate-50 text-slate-800" x-data="{ open:false }">
<nav class="bg-slate-800 text-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center justify-between h-14">
            <div class="flex items-center gap-6 min-w-0">
                <a href="/" class="font-bold whitespace-nowrap">CatchA</a>
                <div class="hidden md:flex items-center gap-5 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:text-indigo-300">Dashboard</a>
                        <a href="{{ route('catches.index') }}" class="hover:text-indigo-300">Catches</a>
                        <a href="{{ route('catches.analytics') }}" class="hover:text-indigo-300">Analytics</a>
                        <a href="{{ route('guidances.index') }}" class="hover:text-indigo-300">Guidance</a>
                        <a href="{{ route('profile.edit') }}" class="hover:text-indigo-300">Profile</a>
                    @endauth
                </div>
            </div>
            <div class="flex items-center gap-3">
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                        @csrf
                        <button class="text-xs bg-slate-600 hover:bg-slate-500 px-3 py-1 rounded">Logout</button>
                    </form>
                @else
                    <div class="hidden md:flex gap-3">
                        <a href="{{ route('login') }}" class="text-xs bg-slate-600 hover:bg-slate-500 px-3 py-1 rounded">Login</a>
                        <a href="{{ route('register') }}" class="text-xs bg-indigo-600 hover:bg-indigo-500 px-3 py-1 rounded">Register</a>
                    </div>
                @endauth
                <button @click="open=!open" class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded bg-slate-700/60 hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-400" aria-label="Toggle navigation" :aria-expanded="open">
                    <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>
    </div>
    <div x-show="open" x-transition.origin.top.left x-cloak class="md:hidden border-t border-slate-700/60 bg-slate-800/95 backdrop-blur">
        <div class="px-4 py-3 space-y-3 text-sm">
            @auth
                <a href="{{ route('dashboard') }}" class="block">Dashboard</a>
                <a href="{{ route('catches.index') }}" class="block">Catches</a>
                <a href="{{ route('catches.analytics') }}" class="block">Analytics</a>
                <a href="{{ route('guidances.index') }}" class="block">Guidance</a>
                <a href="{{ route('profile.edit') }}" class="block">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="mt-1 inline-flex items-center text-xs bg-slate-600 hover:bg-slate-500 px-3 py-1 rounded">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="inline-block text-xs bg-slate-600 hover:bg-slate-500 px-3 py-1 rounded">Login</a>
                <a href="{{ route('register') }}" class="inline-block text-xs bg-indigo-600 hover:bg-indigo-500 px-3 py-1 rounded">Register</a>
            @endauth
        </div>
    </div>
</nav>
<main class="p-4 sm:p-6 max-w-5xl mx-auto">
    @if(session('status'))
        <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded">{{ session('status') }}</div>
    @endif
    @yield('content')
</main>
</body>
</html>
