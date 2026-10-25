<!DOCTYPE html>
<html lang="fr" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteTitre)</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/logo/logo-192.jpg') }}">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-body bg-surface-light dark:bg-surface-darker text-gray-900 dark:text-gray-100 antialiased">
    @yield('content')
</body>
</html>
