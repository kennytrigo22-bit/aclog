@extends('layouts.guest')
@section('title', 'Connexion - ' . $siteTitre)

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 bg-surface-light dark:bg-surface-darker">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="{{ asset('assets/logo/logo-192.jpg') }}" alt="ACLOG" class="w-20 h-20 rounded-xl object-contain mx-auto shadow-sm">
            </a>
            <h1 class="font-heading text-3xl font-bold text-primary-700 dark:text-white mt-4">Connexion</h1>
            <p class="text-gray-500 mt-2">Accédez à votre espace apprenant</p>
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 shadow-sm">
            @if(session('status'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Mot de passe</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-accent-500 focus:ring-accent-500">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Se souvenir de moi</span>
                    </label>
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-accent-500 hover:underline">Mot de passe oublié ?</a>
                    @endif
                </div>
                <button type="submit" class="w-full py-3 rounded-lg bg-primary-700 text-white font-bold hover:bg-primary-500 transition shadow">Se connecter</button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">Pas encore de compte ? <a href="{{ route('register') }}" class="text-accent-500 font-semibold hover:underline">Créer un compte</a></p>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-primary-700 dark:hover:text-white transition flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour au site
            </a>
        </div>
    </div>
</div>
@endsection
