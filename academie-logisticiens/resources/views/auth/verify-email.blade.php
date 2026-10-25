@extends('layouts.public')
@section('title', 'Vérification email')

@section('content')
<div class="pt-28"></div>
<section class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md text-center">
        <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-accent-500/10 text-accent-500 mx-auto flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h1 class="font-heading text-2xl font-bold text-primary-700 dark:text-white">Vérifiez votre email</h1>
            <p class="text-gray-500 mt-2 text-sm">Un lien de vérification a été envoyé à votre adresse email. Cliquez dessus pour activer votre compte.</p>

            @if(session('status') == 'verification-link-sent')
            <div class="mt-4 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm">Un nouveau lien de vérification a été envoyé.</div>
            @endif

            <div class="mt-6 flex items-center justify-center gap-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary-700 text-white font-semibold hover:bg-primary-500 transition text-sm">Renvoyer le lien</button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-red-500 transition">Déconnexion</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
