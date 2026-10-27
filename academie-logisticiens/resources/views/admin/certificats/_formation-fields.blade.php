@php
    $inp = 'w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500';
    $lbl = 'block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1';
    $c = $certificat ?? null;
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="{{ $lbl }}">Formation de la plateforme</label>
        <select name="formation_id" class="{{ $inp }}">
            <option value="">— Aucune (saisir l'intitulé à droite) —</option>
            @foreach($formations as $f)
            <option value="{{ $f->id }}" @selected(old('formation_id', $c?->formation_id) == $f->id)>{{ $f->titre }}{{ $f->duree_heures ? ' (' . $f->duree_heures . ' h)' : '' }}</option>
            @endforeach
        </select>
        @error('formation_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="{{ $lbl }}">… ou intitulé de la formation suivie</label>
        <input type="text" name="formation_titre" list="titres-catalogue{{ $suffixe ?? '' }}" value="{{ old('formation_titre', $c?->formation_titre) }}" placeholder="Ex : Gestion des stocks" class="{{ $inp }}">
        <datalist id="titres-catalogue{{ $suffixe ?? '' }}">
            @foreach($titresCatalogue as $t)<option value="{{ $t }}">@endforeach
        </datalist>
        @error('formation_titre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="{{ $lbl }}">Volume horaire (heures)</label>
        <input type="number" name="duree_heures" min="1" max="2000" value="{{ old('duree_heures', $c?->duree_heures) }}" placeholder="Par défaut : durée de la formation" class="{{ $inp }}">
        @error('duree_heures') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="{{ $lbl }}">Début de la session</label>
            <input type="date" name="date_debut" value="{{ old('date_debut', $c?->date_debut?->format('Y-m-d')) }}" class="{{ $inp }}">
            @error('date_debut') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="{{ $lbl }}">Fin de la session</label>
            <input type="date" name="date_fin" value="{{ old('date_fin', $c?->date_fin?->format('Y-m-d')) }}" class="{{ $inp }}">
            @error('date_fin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
