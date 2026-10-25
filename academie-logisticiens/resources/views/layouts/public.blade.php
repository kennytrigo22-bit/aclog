<!DOCTYPE html>
<html lang="fr" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteTitre)</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/logo/logo-192.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/logo/logo-192.jpg') }}">
    <meta name="description" content="@yield('description', 'Formations certifiantes en logistique, transport, commerce international et douane à Brazzaville, Congo.')">
    <meta property="og:title" content="@yield('title', $siteTitre)">
    <meta property="og:description" content="@yield('description', 'Formations certifiantes en logistique, transport, commerce international et douane à Brazzaville, Congo.')">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_CG">
    <meta property="og:image" content="{{ asset('assets/logo/LOGO ACLOG.jpg') }}">
    <script>
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        try { if (sessionStorage.getItem('intro_seen')) document.documentElement.classList.add('intro-seen'); } catch (e) {}
    </script>
    <style>.intro-seen #intro-overlay{display:none!important}.intro-seen #main-site{display:block!important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-body bg-white dark:bg-surface-darker text-gray-900 dark:text-gray-100 antialiased">

    {{-- Intro GIF : uniquement sur la page d'accueil --}}
    @if(request()->routeIs('home'))
        @include('partials.intro-animation')
    @endif

    {{-- Page loader pour les autres pages --}}
    @include('partials.page-loader')

    {{-- Main Site --}}
    <div id="main-site" @if(request()->routeIs('home')) style="display:none" @endif>
        @include('partials.navbar-public')

        <main class="min-h-screen">
            @yield('content')
        </main>

        @include('partials.footer')

        {{-- Chatbot Widget --}}
        @include('partials.chatbot')
    </div>

    @stack('scripts')
</body>
</html>
