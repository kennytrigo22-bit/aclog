@extends('layouts.admin')
@section('page-title', $parcours ? 'Modifier le parcours' : 'Nouveau parcours')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.challenges.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-primary-700 dark:hover:text-accent-400 transition mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Retour aux parcours
    </a>

    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <h2 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-5">
            {{ $parcours ? 'Modifier : ' . $parcours->titre : 'Creer un nouveau parcours CTF' }}
        </h2>

        <form method="POST" action="{{ $parcours ? route('admin.challenges.parcours.update', $parcours) : route('admin.challenges.parcours.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @if($parcours) @method('PUT') @endif

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Titre du parcours</label>
                <input type="text" name="titre" value="{{ old('titre', $parcours?->titre) }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500" placeholder="Ex: Securite des Systemes Portuaires">
                @error('titre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500" placeholder="Description du parcours...">{{ old('description', $parcours?->description) }}</textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Domaine</label>
                    <select name="domaine" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                        @foreach(['transport' => 'Transport', 'logistique' => 'Logistique', 'transport_logistique' => 'Transport & Logistique', 'commerce' => 'Commerce', 'douane' => 'Douane'] as $v => $l)
                        <option value="{{ $v }}" {{ old('domaine', $parcours?->domaine) === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('domaine') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Niveau</label>
                    <select name="niveau" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                        <option value="debutant" {{ old('niveau', $parcours?->niveau) === 'debutant' ? 'selected' : '' }}>Debutant</option>
                        <option value="intermediaire" {{ old('niveau', $parcours?->niveau) === 'intermediaire' ? 'selected' : '' }}>Intermediaire</option>
                        <option value="avance" {{ old('niveau', $parcours?->niveau) === 'avance' ? 'selected' : '' }}>Avance</option>
                    </select>
                    @error('niveau') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Image (optionnel)</label>
                @if($parcours?->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $parcours->image) }}" alt="" class="w-24 h-24 rounded-lg object-cover">
                </div>
                @endif
                <input type="file" name="image" accept="image/*" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-accent-500 file:text-primary-700 file:font-semibold file:text-sm hover:file:bg-accent-400 file:cursor-pointer">
                @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" {{ old('is_active', $parcours?->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-accent-500 focus:ring-accent-500">
                <label for="is_active" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Actif (visible par les apprenants)</label>
            </div>

            <div class="pt-4 border-t dark:border-gray-800 flex gap-3">
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition">
                    {{ $parcours ? 'Mettre a jour' : 'Creer le parcours' }}
                </button>
                <a href="{{ route('admin.challenges.index') }}" class="px-6 py-2.5 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-300 dark:hover:bg-gray-600 transition">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
