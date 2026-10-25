@extends('layouts.apprenant')
@section('page-title', 'Mes Certifications')

@section('content')
@if($certificats->isEmpty())
<div class="bg-white dark:bg-surface-dark rounded-xl p-8 text-center shadow-sm">
    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
    <p class="text-gray-500 mt-3">Aucun certificat pour le moment. Terminez une formation pour obtenir votre certification.</p>
    <a href="{{ route('apprenant.cours.index') }}" class="inline-block mt-4 px-6 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition">Mes formations</a>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($certificats as $certificat)
    <div class="bg-white dark:bg-surface-dark rounded-xl p-5 shadow-sm border-l-4 border-accent-500">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-accent-500 uppercase">{{ $certificat->formation->categorie }}</span>
            <span class="text-xs text-gray-500">{{ $certificat->issued_at->format('d/m/Y') }}</span>
        </div>
        <h3 class="font-heading font-bold text-primary-700 dark:text-white">{{ $certificat->formation->titre }}</h3>
        <p class="text-sm text-gray-500 mt-1">Score : {{ $certificat->score_obtenu }}% &bull; Code : <span class="font-mono">{{ $certificat->code_verification }}</span></p>
        <div class="flex items-center gap-2 mt-4">
            <a href="{{ route('apprenant.certificats.download', $certificat) }}" class="px-4 py-2 rounded-lg bg-primary-700 text-white text-sm font-semibold hover:bg-primary-500 transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Télécharger PDF
            </a>
            <a href="{{ route('certificat.verifier', $certificat->code_verification) }}" target="_blank" class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                Vérifier
            </a>
        </div>
        <form method="POST" action="{{ route('apprenant.certificats.visibilite', $certificat) }}" class="mt-3">
            @csrf @method('PATCH')
            <button type="submit" class="text-xs font-semibold {{ $certificat->is_public ? 'text-emerald-600' : 'text-gray-500' }} hover:underline">
                {{ $certificat->is_public ? '✓ Visible dans l\'annuaire des certifiés (cliquer pour masquer)' : 'Apparaître dans l\'annuaire des certifiés' }}
            </button>
        </form>
    </div>
    @endforeach
</div>
@endif
@endsection
