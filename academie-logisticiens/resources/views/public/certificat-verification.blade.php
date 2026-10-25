@extends('layouts.public')

@section('title', 'Vérification du Certificat')

@section('content')
<div class="pt-28"></div>

<section class="w-full py-16 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-lg mx-auto px-4 text-center">
        @if($certificat)
        <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h1 class="font-heading text-2xl font-bold text-primary-700 dark:text-white mt-4">Certificat Authentique</h1>
            <p class="text-gray-500 mt-2">Ce certificat est valide et vérifié.</p>
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mt-6 text-sm text-left space-y-2">
                <div class="flex justify-between"><span class="text-gray-500">Titulaire</span><span class="font-semibold">{{ $certificat->user->nom_complet ?? $certificat->user->name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Formation</span><span class="font-semibold">{{ $certificat->formation->titre }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Score</span><span class="font-semibold">{{ $certificat->score_obtenu }}%</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Délivré le</span><span class="font-semibold">{{ $certificat->issued_at->format('d/m/Y') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Code</span><span class="font-mono font-semibold">{{ $certificat->code_verification }}</span></div>
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 mx-auto flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h1 class="font-heading text-2xl font-bold text-primary-700 dark:text-white mt-4">Certificat non trouvé</h1>
            <p class="text-gray-500 mt-2">Le code <span class="font-mono font-semibold">{{ $code }}</span> ne correspond à aucun certificat dans notre base de données.</p>
        </div>
        @endif
    </div>
</section>
@endsection
