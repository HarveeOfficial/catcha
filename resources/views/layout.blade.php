<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CatchA</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="font-sans bg-slate-50 text-slate-800">
<nav class="bg-slate-800 text-white px-6 py-3 flex gap-4 text-sm items-center">
    <a href="/" class="font-bold">CatchA</a>
    @auth
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('catches.index') }}">Catches</a>
    <a href="{{ route('catches.analytics') }}">Analytics</a>
        <a href="{{ route('guidances.index') }}">Guidance</a>
        <a href="{{ route('profile.edit') }}">Profile</a>
        <form method="POST" action="{{ route('logout') }}" class="ml-auto">
            @csrf
            <button class="text-xs bg-slate-600 hover:bg-slate-500 px-3 py-1 rounded">Logout</button>
        </form>
    @else
        <div class="ml-auto flex gap-3">
            <a href="{{ route('login') }}" class="text-xs bg-slate-600 hover:bg-slate-500 px-3 py-1 rounded">Login</a>
            <a href="{{ route('register') }}" class="text-xs bg-indigo-600 hover:bg-indigo-500 px-3 py-1 rounded">Register</a>
        </div>
    @endauth
</nav>
<main class="p-6 max-w-5xl mx-auto">
    @if(session('status'))
        <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded">{{ session('status') }}</div>
    @endif
    @yield('content')
</main>
</body>
</html>
