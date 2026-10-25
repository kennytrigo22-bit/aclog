@extends('layouts.public')
@section('title', 'Confirmation')

@section('content')
<div class="pt-28"></div>
<section class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 shadow-sm">
            <h1 class="font-heading text-2xl font-bold text-primary-700 dark:text-white mb-2">Confirmation requise</h1>
            <p class="text-sm text-gray-500 mb-6">Veuillez confirmer votre mot de passe avant de continuer.</p>

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Mot de passe</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="w-full py-3 rounded-lg bg-primary-700 text-white font-bold hover:bg-primary-500 transition shadow">Confirmer</button>
            </form>
        </div>
    </div>
</section>
@endsection
