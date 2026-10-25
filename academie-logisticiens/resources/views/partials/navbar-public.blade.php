<header class="fixed top-0 left-0 right-0 z-50 bg-white dark:bg-surface-dark shadow-sm" x-data="{ mobileMenu: false }">
    {{-- Top bar --}}
    <div class="bg-primary-700 text-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-6 flex items-center justify-between py-1.5 text-xs">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span class="text-accent-300">+242 06 828 79 15</span>
                </span>
                <span class="hidden md:flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="text-accent-300">{{ $emailContact }}</span>
                </span>
            </div>
            <div class="hidden lg:flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-accent-400 animate-pulse"></span>
                <span class="text-accent-300 font-semibold">Session 2026 ouverte</span>
                <span class="text-gray-300">- Gagnez des jetons FCFA sur vos formations !</span>
            </div>
        </div>
    </div>

    {{-- Main nav --}}
    <div class="max-w-7xl mx-auto px-4 lg:px-6 h-16 md:h-20 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <img src="{{ asset('assets/logo/logo-192.jpg') }}" alt="Logo" class="w-14 h-14 md:w-16 md:h-16 rounded-lg object-contain">
            <div class="hidden sm:flex flex-col">
                <span class="font-heading font-bold text-primary-700 dark:text-white text-sm md:text-base leading-tight">{{ $siteTitre }}</span>
                <span class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400 italic font-semibold">{{ $siteSlogan }}</span>
            </div>
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden xl:flex items-center gap-6">
            @php
                $navLinks = [
                    ['route' => 'home', 'label' => 'Accueil'],
                    ['route' => 'formations.index', 'label' => 'Formations'],
                    ['route' => 'tests.index', 'label' => 'Tests de Niveau'],
                    ['route' => 'a-propos', 'label' => 'À Propos'],
                    ['route' => 'contact', 'label' => 'Contact'],
                ];
            @endphp
            @foreach($navLinks as $link)
                <a href="{{ route($link['route']) }}"
                   class="text-sm font-medium transition pb-1 border-b-2 {{ request()->routeIs($link['route']) ? 'font-semibold text-primary-700 dark:text-white border-accent-500' : 'text-gray-600 dark:text-gray-300 border-transparent hover:text-primary-700 dark:hover:text-white' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Right side --}}
        <div class="flex items-center gap-2 md:gap-3">
            {{-- Dark mode toggle --}}
            <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', darkMode)"
                    class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>

            @guest
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg bg-accent-500 text-primary-700 text-sm font-bold hover:bg-accent-400 transition shadow-sm">Connexion</a>
            @else
                <a href="{{ auth()->user()->isGestionnaire() ? route('admin.dashboard') : route('apprenant.dashboard') }}"
                   class="px-4 py-2 rounded-lg bg-primary-700 text-white text-sm font-semibold hover:bg-primary-600 transition">
                    Mon espace
                </a>
            @endguest

            {{-- Mobile menu button --}}
            <button @click="mobileMenu = !mobileMenu" class="xl:hidden p-2 rounded-lg text-gray-500 dark:text-gray-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileMenu" x-transition class="xl:hidden bg-white dark:bg-surface-dark border-t dark:border-gray-800 pb-4">
        <nav class="max-w-7xl mx-auto px-4 flex flex-col gap-1 pt-2">
            @php
                $mobileLinks = [
                    ['route' => 'home', 'label' => 'Accueil'],
                    ['route' => 'formations.index', 'label' => 'Formations & Catalogue'],
                    ['route' => 'tests.index', 'label' => 'Tests de Niveau (+2 pts)'],
                    ['route' => 'a-propos', 'label' => 'À Propos'],
                    ['route' => 'contact', 'label' => 'Contact'],
                ];
            @endphp
            @foreach($mobileLinks as $link)
                <a href="{{ route($link['route']) }}"
                   class="py-2.5 px-3 rounded-lg text-sm font-medium {{ request()->routeIs($link['route']) ? 'bg-primary-50 dark:bg-gray-800 text-primary-700 dark:text-accent-400 font-semibold' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
