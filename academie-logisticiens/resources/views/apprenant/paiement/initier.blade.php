@extends('layouts.apprenant')
@section('page-title', 'Paiement - ' . $formation->titre)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm mb-6">
        <h2 class="font-heading text-xl font-bold text-primary-700 dark:text-white">{{ $formation->titre }}</h2>
        <div class="flex items-baseline gap-2 mt-2">
            @if($formation->promo_active && $formation->prix_promo)
            <span class="text-3xl font-bold text-primary-700 dark:text-white">{{ number_format($formation->prix_promo, 0, ',', ' ') }}</span>
            <span class="text-gray-500">FCFA</span>
            <span class="text-sm text-gray-400 line-through ml-2">{{ number_format($formation->prix, 0, ',', ' ') }}</span>
            @else
            <span class="text-3xl font-bold text-primary-700 dark:text-white">{{ number_format($formation->prix, 0, ',', ' ') }}</span>
            <span class="text-gray-500">FCFA</span>
            @endif
        </div>
    </div>

    {{-- Instructions paiement --}}
    <div class="bg-accent-500/10 dark:bg-accent-500/5 rounded-xl p-5 border border-accent-500/20 mb-6">
        <h3 class="font-heading font-bold text-primary-700 dark:text-white mb-3">Instructions de paiement Mobile Money</h3>
        <ol class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
            <li class="flex gap-2">
                <span class="w-6 h-6 rounded-full bg-accent-500 text-primary-700 flex items-center justify-center font-bold text-xs shrink-0">1</span>
                <span>Envoyez <strong>{{ number_format($formation->prix_effectif, 0, ',', ' ') }} FCFA</strong> via {{ $operateurs }} au <strong class="text-primary-700 dark:text-accent-400">{{ $numeroPaiement }}</strong></span>
            </li>
            <li class="flex gap-2">
                <span class="w-6 h-6 rounded-full bg-accent-500 text-primary-700 flex items-center justify-center font-bold text-xs shrink-0">2</span>
                <span>Nom du bénéficiaire : <strong>{{ $nomBeneficiaire }}</strong></span>
            </li>
            <li class="flex gap-2">
                <span class="w-6 h-6 rounded-full bg-accent-500 text-primary-700 flex items-center justify-center font-bold text-xs shrink-0">3</span>
                <span>Prenez une <strong>capture d'écran</strong> de la confirmation de paiement</span>
            </li>
            <li class="flex gap-2">
                <span class="w-6 h-6 rounded-full bg-accent-500 text-primary-700 flex items-center justify-center font-bold text-xs shrink-0">4</span>
                <span>Téléchargez la capture ci-dessous. Validation sous <strong>{{ $delai }} minutes</strong> maximum.</span>
            </li>
        </ol>
    </div>

    {{-- Formulaire --}}
    <form method="POST" action="{{ route('apprenant.paiement.confirmer', $formation) }}" enctype="multipart/form-data" class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Capture de paiement *</label>
            <input type="file" name="capture_paiement" accept=".jpg,.jpeg,.png,.pdf" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-700 file:text-white file:font-semibold file:cursor-pointer hover:file:bg-primary-500">
            <p class="text-xs text-gray-400 mt-1">JPG, PNG ou PDF - 5 Mo maximum</p>
            @error('capture_paiement')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Code promo (optionnel)</label>
            <input type="text" name="code_promo" value="{{ old('code_promo') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500" placeholder="BIENVENUE2026">
        </div>

        @if($user->jetons > 0)
        <label class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-accent-500 transition">
            <input type="checkbox" name="utiliser_jetons" value="1" class="text-accent-500 focus:ring-accent-500 rounded">
            <div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Utiliser mes jetons FCFA</span>
                <span class="text-xs text-gray-500 block">Solde actuel : {{ number_format($user->jetons) }} FCFA</span>
            </div>
        </label>
        @endif

        <button type="submit" class="w-full py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition shadow">
            Confirmer le paiement
        </button>
    </form>
</div>
@endsection
