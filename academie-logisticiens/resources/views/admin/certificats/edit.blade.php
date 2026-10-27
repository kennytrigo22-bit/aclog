@extends('layouts.admin')
@section('page-title', 'Modifier le certificat ' . $certificat->numero_affiche)

@section('content')
<div class="max-w-4xl">
    <a href="{{ route('admin.certificats.index') }}" class="text-sm text-gray-500 hover:text-primary-700">← Retour aux certificats</a>

    <form method="POST" action="{{ route('admin.certificats.update', $certificat) }}" enctype="multipart/form-data" class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm space-y-5 mt-4">
        @csrf @method('PUT')
        <div class="flex flex-wrap items-center justify-between gap-2 border-b dark:border-gray-800 pb-3">
            <h2 class="font-heading font-bold text-primary-700 dark:text-white">Certificat {{ $certificat->numero_affiche }} — présentiel</h2>
            <a href="{{ route('certificat.verifier', $certificat->code_verification) }}" target="_blank" class="font-mono text-xs text-accent-600 hover:underline">Vérification : {{ $certificat->code_verification }}</a>
        </div>
        <h3 class="text-sm font-bold text-accent-600 uppercase tracking-wider">Formation suivie</h3>
        @include('admin.certificats._formation-fields')
        <h3 class="text-sm font-bold text-accent-600 uppercase tracking-wider pt-2">Titulaire</h3>
        @include('admin.certificats._titulaire-fields')
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.certificats.pdf', $certificat) }}" class="px-5 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 font-semibold text-sm">Télécharger le PDF</a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
