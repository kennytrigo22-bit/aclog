@php
    $inp = 'w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500';
    $lbl = 'block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1';
    $c = $certificat ?? null;
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="{{ $lbl }}">Nom <span class="text-red-500">*</span></label>
        <input type="text" name="titulaire_nom" required value="{{ old('titulaire_nom', $c?->titulaire_nom) }}" class="{{ $inp }} uppercase">
        @error('titulaire_nom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="{{ $lbl }}">Prénoms</label>
        <input type="text" name="titulaire_prenom" value="{{ old('titulaire_prenom', $c?->titulaire_prenom) }}" class="{{ $inp }}">
        @error('titulaire_prenom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="{{ $lbl }}">Pays</label>
        <select name="titulaire_pays" class="{{ $inp }}">
            <option value="">—</option>
            @foreach($pays as $p)
            <option value="{{ $p }}" @selected(old('titulaire_pays', $c?->titulaire_pays ?? ($c ? null : 'Congo')) === $p)>{{ $p }}</option>
            @endforeach
        </select>
        @error('titulaire_pays') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="{{ $lbl }}">Né(e) le</label>
            <input type="date" name="titulaire_date_naissance" value="{{ old('titulaire_date_naissance', $c?->titulaire_date_naissance?->format('Y-m-d')) }}" class="{{ $inp }}">
            @error('titulaire_date_naissance') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="{{ $lbl }}">à</label>
            <input type="text" name="titulaire_lieu_naissance" value="{{ old('titulaire_lieu_naissance', $c?->titulaire_lieu_naissance) }}" class="{{ $inp }}">
            @error('titulaire_lieu_naissance') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
    <div class="md:col-span-2">
        <label class="{{ $lbl }}">Photo de profil (affichée en miniature dans la liste des certifiés)</label>
        <div class="flex items-center gap-4">
            @if($c?->titulaire_photo)
            <img src="{{ $c->photo_url }}" alt="" class="w-14 h-14 rounded-full object-cover border-2 border-accent-500">
            <label class="text-xs text-gray-500 flex items-center gap-1"><input type="checkbox" name="retirer_photo" value="1" class="rounded"> Retirer</label>
            @endif
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="{{ $inp }} file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-accent-500 file:text-primary-700 file:font-semibold file:text-sm hover:file:bg-accent-400 file:cursor-pointer">
        </div>
        @error('photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
</div>
