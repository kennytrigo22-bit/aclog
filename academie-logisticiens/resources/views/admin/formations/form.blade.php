@extends('layouts.admin')
@section('page-title', $formation->exists ? 'Modifier la formation' : 'Nouvelle formation')

@section('content')
<form method="POST" action="{{ $formation->exists ? route('admin.formations.update', $formation) : route('admin.formations.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf
    @if($formation->exists) @method('PUT') @endif

    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm space-y-5">
        <h2 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3">Informations générales</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Titre *</label>
                <input type="text" name="titre" value="{{ old('titre', $formation->titre) }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                @error('titre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Catégorie *</label>
                <input type="text" name="categorie" value="{{ old('categorie', $formation->categorie) }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500" placeholder="logistique, douane, commerce...">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Niveau *</label>
                <select name="niveau" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @foreach(['debutant' => 'Débutant', 'intermediaire' => 'Intermédiaire', 'avance' => 'Avancé'] as $val => $label)
                    <option value="{{ $val }}" {{ old('niveau', $formation->niveau) == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Durée (heures) *</label>
                <input type="number" name="duree_heures" value="{{ old('duree_heures', $formation->duree_heures) }}" required min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Badge (optionnel)</label>
                <input type="text" name="badge" value="{{ old('badge', $formation->badge) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500" placeholder="Nouveau, Populaire...">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Description *</label>
            <textarea name="description" rows="4" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">{{ old('description', $formation->description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Programme (optionnel)</label>
            <textarea name="programme" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">{{ old('programme', $formation->programme) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Image de couverture</label>
            <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-700 file:text-white file:font-semibold file:cursor-pointer hover:file:bg-primary-500">
            @if($formation->image)
            <p class="text-xs text-gray-400 mt-1">Image actuelle : {{ $formation->image }}</p>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm space-y-5">
        <h2 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3">Tarification</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Prix (FCFA) *</label>
                <input type="number" name="prix" value="{{ old('prix', $formation->prix) }}" required min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Prix promo (FCFA)</label>
                <input type="number" name="prix_promo" value="{{ old('prix_promo', $formation->prix_promo) }}" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
            </div>
        </div>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="promo_active" value="1" {{ old('promo_active', $formation->promo_active) ? 'checked' : '' }} class="text-accent-500 focus:ring-accent-500 rounded">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Promotion active</span>
        </label>
    </div>

    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm space-y-4">
        <h2 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3">Publication</h2>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="is_published" value="1" {{ old('is_published', $formation->is_published) ? 'checked' : '' }} class="text-accent-500 focus:ring-accent-500 rounded">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Publier la formation</span>
        </label>
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $formation->is_featured) ? 'checked' : '' }} class="text-accent-500 focus:ring-accent-500 rounded">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Mettre en avant sur la page d'accueil</span>
        </label>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition">{{ $formation->exists ? 'Enregistrer' : 'Créer la formation' }}</button>
        @if($formation->exists)
        <a href="{{ route('admin.formations.contenu', $formation) }}" class="px-6 py-3 rounded-lg bg-primary-700 text-white font-bold hover:bg-primary-500 transition">Gérer le contenu</a>
        @endif
        <a href="{{ route('admin.formations.index') }}" class="px-6 py-3 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition">Annuler</a>
    </div>
</form>
@endsection
