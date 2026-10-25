@extends('layouts.public')
@section('title', 'Réinitialiser le mot de passe')

@section('content')
<div class="pt-28"></div>
<section class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="font-heading text-3xl font-bold text-primary-700 dark:text-white">Nouveau mot de passe</h1>
        </div>

        <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 shadow-sm">
            <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nouveau mot de passe</label>
                    <input id="password" type="password" name="password" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Confirmer</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                </div>
                <button type="submit" class="w-full py-3 rounded-lg bg-primary-700 text-white font-bold hover:bg-primary-500 transition shadow">Réinitialiser</button>
            </form>
        </div>
    </div>
</section>
@endsection
