@extends('layouts.public')
@section('title', 'Mot de passe oublié')

@section('content')
<div class="pt-28"></div>
<section class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="font-heading text-3xl font-bold text-primary-700 dark:text-white">Mot de passe oublié</h1>
            <p class="text-gray-500 mt-2">Entrez votre email pour recevoir un lien de réinitialisation</p>
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 shadow-sm">
            @if(session('status'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="w-full py-3 rounded-lg bg-primary-700 text-white font-bold hover:bg-primary-500 transition shadow">Envoyer le lien</button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-accent-500 hover:underline">Retour à la connexion</a>
            </div>
        </div>
    </div>
</section>
@endsection
