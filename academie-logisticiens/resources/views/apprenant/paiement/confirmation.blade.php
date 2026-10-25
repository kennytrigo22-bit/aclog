@extends('layouts.apprenant')
@section('page-title', 'Paiement reçu')

@section('content')
<div class="max-w-lg mx-auto text-center">
    <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 shadow-sm">
        <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h2 class="font-heading text-2xl font-bold text-primary-700 dark:text-white mt-4">Paiement reçu !</h2>
        <p class="text-gray-500 mt-2">Votre paiement pour <strong>{{ $formation->titre }}</strong> a été soumis avec succès.</p>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mt-4 text-sm text-left">
            <div class="flex justify-between mb-2">
                <span class="text-gray-500">Montant</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-500">Statut</span>
                <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold">En attente de validation</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Référence</span>
                <span class="font-mono text-gray-700 dark:text-gray-300">#{{ $paiement->id }}</span>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-4">L'accès à votre formation sera activé dès la validation par notre équipe.</p>
        <a href="{{ route('apprenant.dashboard') }}" class="inline-block mt-6 px-6 py-2.5 rounded-lg bg-primary-700 text-white font-semibold hover:bg-primary-500 transition">
            Retour au tableau de bord
        </a>
    </div>
</div>
@endsection
