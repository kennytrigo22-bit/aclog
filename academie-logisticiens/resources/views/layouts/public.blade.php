<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteTitre . ' — Formations Certifiantes en Logistique, Douane & Commerce International')</title>
    <meta name="description" content="@yield('description', 'Formations certifiantes en logistique, transport, commerce international et douane à Brazzaville, Congo.')">
    <meta name="theme-color" content="#0E3A6B">
    <link rel="icon" type="image/png" href="{{ asset('assets/site/logo-aclog.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/site/logo-aclog.png') }}">
    <meta property="og:title" content="@yield('title', $siteTitre)">
    <meta property="og:description" content="@yield('description', 'Formations certifiantes en logistique, transport, commerce international et douane à Brazzaville, Congo.')">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_CG">
    <meta property="og:image" content="{{ asset('assets/site/logo-aclog.png') }}">
    <script>
        try { if (sessionStorage.getItem('intro_seen')) document.documentElement.classList.add('intro-seen'); } catch (e) {}
    </script>
    <style>.intro-seen #intro-overlay{display:none!important}.intro-seen #main-site{display:block!important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/site/aclog.css') }}?v={{ filemtime(public_path('assets/site/aclog.css')) }}">
    @stack('head')
</head>
<body data-wa="{{ $whatsapp }}" data-mail="{{ $emailContact }}">
<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs>
<symbol id="i-wa" viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.2 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8 0-1.3.7-2 1-2.3.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .5l-.4.6-.4.4c-.1.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l2 1c.3.1.5.2.5.3.1.2.1.8-.1 1.4z"/></symbol>
<symbol id="i-yt" viewBox="0 0 24 24"><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2C2 8.8 2 12 2 12s0 3.2.4 4.8a2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8c.4-1.6.4-4.8.4-4.8s0-3.2-.4-4.8zM10 15V9l5.2 3L10 15z"/></symbol>
<symbol id="i-fb" viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7A10 10 0 0 0 22 12z"/></symbol>
</defs></svg>

    {{-- Intro GIF : uniquement sur la page d'accueil --}}
    @if(request()->routeIs('home'))
        @include('partials.intro-animation')
    @endif

    <div id="main-site" @if(request()->routeIs('home')) style="display:none" @endif>
        @include('partials.navbar-public')

        <main>
            @yield('content')
        </main>

        @include('partials.footer')

        {{-- Assistant IA --}}
        @include('partials.chatbot')
    </div>

    @include('partials.presence')
    <script src="{{ asset('assets/site/aclog.js') }}?v={{ filemtime(public_path('assets/site/aclog.js')) }}" defer></script>
    @stack('scripts')
</body>
</html>
