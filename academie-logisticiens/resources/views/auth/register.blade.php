@extends('layouts.guest')
@section('title', 'Inscription - ' . $siteTitre)

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 bg-surface-light dark:bg-surface-darker">
    <div class="w-full max-w-2xl">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="{{ asset('assets/logo/logo-192.jpg') }}" alt="ACLOG" class="w-20 h-20 rounded-xl object-contain mx-auto shadow-sm">
            </a>
            <h1 class="font-heading text-3xl font-bold text-primary-700 dark:text-white mt-4">Créer un compte</h1>
            <p class="text-gray-500 mt-2">Rejoignez {{ $siteTitre }}</p>
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 shadow-sm">
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <h3 class="text-xs font-bold uppercase tracking-wider text-accent-600">Identité</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="prenom" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Prénom *</label>
                        <input id="prenom" type="text" name="prenom" value="{{ old('prenom') }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="telephone" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Téléphone</label>
                    <input id="telephone" type="tel" name="telephone" value="{{ old('telephone') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500" placeholder="+242 06 XXX XX XX">
                </div>
                @include('partials.profil-fields', ['user' => null])
                <h3 class="text-xs font-bold uppercase tracking-wider text-accent-600 pt-2">Sécurité</h3>
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Mot de passe *</label>
                    <input id="password" type="password" name="password" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Confirmer le mot de passe *</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                </div>
                <button type="submit" class="w-full py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition shadow">Créer mon compte</button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">Déjà inscrit ? <a href="{{ route('login') }}" class="text-accent-500 font-semibold hover:underline">Se connecter</a></p>
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
