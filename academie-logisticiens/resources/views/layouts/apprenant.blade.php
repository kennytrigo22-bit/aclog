<!DOCTYPE html>
<html lang="fr" x-data="{ darkMode: localStorage.getItem('theme') === 'dark', sidebarOpen: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mon Espace') - {{ $siteTitre }}</title>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-body bg-surface-light dark:bg-surface-darker text-gray-900 dark:text-gray-100 antialiased">

<div class="min-h-screen flex">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-50 w-64 max-w-[85vw] h-[100dvh] flex flex-col bg-primary-700 text-white transform transition-transform lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="p-5 flex items-center gap-3 border-b border-primary-500/30 shrink-0">
            <img src="{{ asset('assets/logo/logo-192.jpg') }}" alt="Logo" class="w-9 h-9 rounded-lg object-contain">
            <span class="font-heading font-bold text-sm">Mon Espace</span>
        </div>
        <nav class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-4 space-y-1">
            @php
                $links = [
                    ['route' => 'apprenant.dashboard', 'label' => 'Tableau de bord', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['route' => 'apprenant.cours.index', 'label' => 'Mes Formations', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    ['route' => 'apprenant.certificats.index', 'label' => 'Certifications', 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                    ['route' => 'apprenant.challenges.index', 'label' => 'Challenges CTF', 'icon' => 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9'],
                    ['route' => 'apprenant.support.index', 'label' => 'Support', 'icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z'],
                ];
            @endphp
            @foreach($links as $link)
            <a href="{{ route($link['route']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs($link['route'] . '*') ? 'bg-white/10 text-accent-400' : 'text-gray-300 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/></svg>
                {{ $link['label'] }}
            </a>
            @endforeach
        </nav>
        <div class="shrink-0 p-4 border-t border-primary-500/30">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-accent-500 text-primary-700 flex items-center justify-center font-bold text-sm overflow-hidden shrink-0">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->prenom ?? auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold truncate">{{ auth()->user()->nom_complet ?? auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400">{{ auth()->user()->points }} pts &bull; {{ number_format(auth()->user()->jetons) }} FCFA</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('profile.edit') }}" class="text-xs text-gray-400 hover:text-white transition">Profil</a>
                <span class="text-gray-600">&bull;</span>
                <a href="{{ route('home') }}" class="text-xs text-gray-400 hover:text-white transition">Accueil</a>
                <span class="text-gray-600">&bull;</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-gray-400 hover:text-red-400 transition">Déconnexion</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden" x-transition.opacity></div>

    {{-- Contenu --}}
    <div class="flex-1 min-w-0 lg:ml-64">
        {{-- Top bar --}}
        <header class="sticky top-0 z-30 bg-white dark:bg-surface-dark border-b dark:border-gray-800 px-4 lg:px-6 h-16 flex items-center justify-between gap-2">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="font-heading font-bold text-base sm:text-lg text-primary-700 dark:text-white truncate min-w-0 flex-1 lg:flex-none">@yield('page-title', 'Tableau de bord')</h1>
            <div class="flex items-center gap-1 shrink-0">
                @include('components.notification-bell')
                <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', darkMode)"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
            </div>
        </header>

        <main class="p-4 lg:p-6">
            @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
            @endif
            @if(session('info'))
            <div class="mb-4 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-sm">{{ session('info') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
