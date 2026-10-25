@extends('layouts.admin')
@section('page-title', $promo->exists ? 'Modifier le code promo' : 'Nouveau code promo')

@section('content')
<form method="POST" action="{{ $promo->exists ? route('admin.promos.update', $promo) : route('admin.promos.store') }}" class="max-w-xl space-y-6" x-data="{ perimetre: '{{ old('perimetre', $promo->perimetre ?? 'all') }}' }">
    @csrf
    @if($promo->exists) @method('PUT') @endif

    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm space-y-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Code *</label>
            <input type="text" name="code" value="{{ old('code', $promo->code) }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500 font-mono uppercase" placeholder="BIENVENUE2026">
            @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                <select name="type" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    <option value="pourcentage" {{ old('type', $promo->type) == 'pourcentage' ? 'selected' : '' }}>Pourcentage (%)</option>
                    <option value="fixe" {{ old('type', $promo->type) == 'fixe' ? 'selected' : '' }}>Montant fixe (FCFA)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Valeur *</label>
                <input type="number" name="valeur" value="{{ old('valeur', $promo->valeur) }}" required min="0" step="any" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Périmètre *</label>
            <select name="perimetre" x-model="perimetre" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                <option value="all">Toutes les formations</option>
                <option value="formation">Une formation spécifique</option>
                <option value="categorie">Une catégorie</option>
            </select>
        </div>

        <div x-show="perimetre === 'formation'" x-cloak>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Formation *</label>
            <select name="formation_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                <option value="">- Sélectionner -</option>
                @foreach($formations as $id => $titre)
                <option value="{{ $id }}" {{ old('formation_id', $promo->formation_id) == $id ? 'selected' : '' }}>{{ $titre }}</option>
                @endforeach
            </select>
        </div>

        <div x-show="perimetre === 'categorie'" x-cloak>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Catégorie *</label>
            <select name="categorie" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                <option value="">- Sélectionner -</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ old('categorie', $promo->categorie) == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Date début</label>
                <input type="date" name="date_debut" value="{{ old('date_debut', $promo->date_debut?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Date fin</label>
                <input type="date" name="date_fin" value="{{ old('date_fin', $promo->date_fin?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nombre d'utilisations max</label>
            <input type="number" name="nb_utilisations_max" value="{{ old('nb_utilisations_max', $promo->nb_utilisations_max) }}" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500" placeholder="Illimité si vide">
        </div>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $promo->exists ? $promo->is_active : true) ? 'checked' : '' }} class="text-accent-500 focus:ring-accent-500 rounded">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Code actif</span>
        </label>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition">{{ $promo->exists ? 'Enregistrer' : 'Créer le code' }}</button>
        <a href="{{ route('admin.promos.index') }}" class="px-6 py-3 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition">Annuler</a>
    </div>
</form>
@endsection
