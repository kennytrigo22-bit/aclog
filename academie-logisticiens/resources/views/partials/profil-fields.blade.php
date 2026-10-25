@php
    use App\Support\ProfilOptions;
    $u = $user ?? null;
    $inp = 'w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500';
    $lbl = 'block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1';
    $val = fn ($k, $d = null) => old($k, $u?->{$k} ?? $d);
    $dn = old('date_naissance', $u?->date_naissance?->format('Y-m-d'));
@endphp

<div x-data="{ situation: '{{ $val('situation', '') }}' }" class="space-y-5">
    {{-- Naissance --}}
    <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-accent-600 mb-2">Naissance <span class="normal-case font-normal text-gray-400">(figure sur votre certificat)</span></h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="date_naissance" class="{{ $lbl }}">Date de naissance *</label>
                <input id="date_naissance" type="date" name="date_naissance" value="{{ $dn }}" max="{{ now()->subYears(10)->toDateString() }}" required class="{{ $inp }}">
                @error('date_naissance')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="lieu_naissance" class="{{ $lbl }}">Lieu de naissance *</label>
                <input id="lieu_naissance" type="text" name="lieu_naissance" value="{{ $val('lieu_naissance') }}" required placeholder="Ex: Brazzaville" class="{{ $inp }}">
                @error('lieu_naissance')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Situation --}}
    <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-accent-600 mb-2">Votre profil</h3>
        <label class="{{ $lbl }}">Vous êtes *</label>
        <div class="grid grid-cols-2 gap-2">
            @foreach(ProfilOptions::SITUATIONS as $k => $label)
            <label class="cursor-pointer">
                <input type="radio" name="situation" value="{{ $k }}" x-model="situation" class="peer hidden" required>
                <div class="px-3 py-2.5 rounded-lg border-2 border-gray-200 dark:border-gray-700 peer-checked:border-accent-500 peer-checked:bg-accent-500/10 text-sm font-semibold text-center text-gray-700 dark:text-gray-200 transition">{{ $label }}</div>
            </label>
            @endforeach
        </div>
        @error('situation')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

        {{-- Etudiant --}}
        <div x-show="situation === 'etudiant'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
            <div>
                <label for="niveau_etudes" class="{{ $lbl }}">Niveau d'études *</label>
                <select id="niveau_etudes" name="niveau_etudes" :disabled="situation !== 'etudiant'" class="{{ $inp }}">
                    <option value="">Choisir...</option>
                    @foreach(ProfilOptions::NIVEAUX_ETUDES as $k => $label)
                    <option value="{{ $k }}" {{ $val('niveau_etudes') === $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('niveau_etudes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="etablissement_etu" class="{{ $lbl }}">École / université</label>
                <input id="etablissement_etu" type="text" name="etablissement" :disabled="situation !== 'etudiant'" value="{{ $val('etablissement') }}" class="{{ $inp }}">
            </div>
        </div>

        {{-- Professionnel / autres --}}
        <div x-show="situation === 'professionnel' || situation === 'demandeur_emploi' || situation === 'autre'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
            <div>
                <label for="profession" class="{{ $lbl }}"><span x-text="situation === 'professionnel' ? 'Profession / poste *' : 'Profession / domaine visé'"></span></label>
                <input id="profession" type="text" name="profession" :disabled="situation === 'etudiant'" value="{{ $val('profession') }}" placeholder="Ex: Agent de transit" class="{{ $inp }}">
                @error('profession')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="secteur_activite" class="{{ $lbl }}">Secteur d'activité</label>
                <select id="secteur_activite" name="secteur_activite" :disabled="situation === 'etudiant'" class="{{ $inp }}">
                    <option value="">Choisir...</option>
                    @foreach(ProfilOptions::SECTEURS as $k => $label)
                    <option value="{{ $k }}" {{ $val('secteur_activite') === $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2" x-show="situation === 'professionnel'">
                <label for="etablissement_pro" class="{{ $lbl }}">Entreprise / employeur</label>
                <input id="etablissement_pro" type="text" name="etablissement" :disabled="situation !== 'professionnel'" value="{{ $val('etablissement') }}" class="{{ $inp }}">
            </div>
        </div>
    </div>

    {{-- Localisation & origine --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label for="ville" class="{{ $lbl }}">Ville de résidence</label>
            <input id="ville" type="text" name="ville" value="{{ $val('ville') }}" placeholder="Ex: Pointe-Noire" class="{{ $inp }}">
        </div>
        <div>
            <label for="pays" class="{{ $lbl }}">Pays de résidence *</label>
            <select id="pays" name="pays" required class="{{ $inp }}">
                @foreach(array_keys(ProfilOptions::PAYS) as $p)
                <option value="{{ $p }}" {{ $val('pays', 'Congo') === $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
            @error('pays')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label for="source_decouverte" class="{{ $lbl }}">Comment avez-vous connu l'Académie ?</label>
            <select id="source_decouverte" name="source_decouverte" class="{{ $inp }}">
                <option value="">Choisir...</option>
                @foreach(ProfilOptions::SOURCES as $k => $label)
                <option value="{{ $k }}" {{ $val('source_decouverte') === $k ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <label class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
        <input type="hidden" name="accept_marketing" value="0">
        <input type="checkbox" name="accept_marketing" value="1" {{ $val('accept_marketing', false) ? 'checked' : '' }} class="mt-0.5 rounded border-gray-300 text-accent-500 focus:ring-accent-500">
        <span>J'accepte de recevoir des nouvelles et offres de formation de l'Académie.</span>
    </label>
</div>
