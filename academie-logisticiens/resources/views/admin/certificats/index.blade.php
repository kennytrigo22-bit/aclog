@extends('layouts.admin')
@section('page-title', 'Certificats')

@section('content')
@php
    $inp = 'w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500';
    $btn = 'px-6 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition whitespace-nowrap';
@endphp
<div class="space-y-6">

    {{-- Chiffres --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach([['Certificats délivrés', $stats['total'], 'text-primary-700 dark:text-white'], ['En ligne', $stats['en_ligne'], 'text-sky-600'], ['En présentiel', $stats['presentiel'], 'text-accent-500']] as [$libelle, $valeur, $couleur])
        <div class="bg-white dark:bg-surface-dark rounded-xl p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wider font-semibold text-gray-500">{{ $libelle }}</p>
            <p class="font-heading text-3xl font-bold {{ $couleur }} mt-1">{{ $valeur }}</p>
        </div>
        @endforeach
    </div>

    {{-- Generation pour le presentiel --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm" x-data="{ onglet: '{{ $errors->has('liste') || old('liste') ? 'lot' : 'un' }}' }">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b dark:border-gray-800 pb-3 mb-5">
            <div>
                <h2 class="font-heading font-bold text-primary-700 dark:text-white">Générer des certificats — formation en présentiel</h2>
                <p class="text-xs text-gray-500 mt-1">Le certifié apparaît dans la liste publique des certifiés (page À propos) et son certificat se vérifie par QR Code, comme pour la formation en ligne.</p>
            </div>
            <div class="flex rounded-lg bg-gray-100 dark:bg-gray-800 p-1 text-sm font-semibold">
                <button type="button" @click="onglet = 'un'" :class="onglet === 'un' ? 'bg-white dark:bg-surface-dark shadow text-primary-700 dark:text-white' : 'text-gray-500'" class="px-4 py-1.5 rounded-md transition">Un apprenant</button>
                <button type="button" @click="onglet = 'lot'" :class="onglet === 'lot' ? 'bg-white dark:bg-surface-dark shadow text-primary-700 dark:text-white' : 'text-gray-500'" class="px-4 py-1.5 rounded-md transition">Toute une promotion</button>
            </div>
        </div>

        <form x-show="onglet === 'un'" method="POST" action="{{ route('admin.certificats.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <h3 class="text-sm font-bold text-accent-600 uppercase tracking-wider">Formation suivie</h3>
            @include('admin.certificats._formation-fields', ['suffixe' => '-un'])
            <h3 class="text-sm font-bold text-accent-600 uppercase tracking-wider pt-2">Titulaire</h3>
            @include('admin.certificats._titulaire-fields')
            <div class="flex justify-end"><button type="submit" class="{{ $btn }}">Générer le certificat</button></div>
        </form>

        <form x-show="onglet === 'lot'" x-cloak method="POST" action="{{ route('admin.certificats.lot') }}" class="space-y-5">
            @csrf
            <h3 class="text-sm font-bold text-accent-600 uppercase tracking-wider">Formation suivie par la promotion</h3>
            @include('admin.certificats._formation-fields', ['suffixe' => '-lot'])
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Liste des apprenants — une ligne par personne</label>
                <p class="text-xs text-gray-500 mb-2">Format : <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">NOM ; Prénoms ; Pays ; date de naissance (JJ/MM/AAAA) ; lieu de naissance</code> — seul le nom est obligatoire. Vous pouvez coller directement les colonnes copiées depuis Excel.</p>
                <textarea name="liste" rows="8" class="{{ $inp }} font-mono" placeholder="MOUKALA ; Clarisse ; Congo ; 12/04/1998 ; Brazzaville&#10;SAMBA ; Jean-Paul ; Congo (RDC)">{{ old('liste') }}</textarea>
                @foreach($errors->get('liste') as $msgs)
                    @foreach((array) $msgs as $m)<p class="text-red-500 text-xs mt-1">{{ $m }}</p>@endforeach
                @endforeach
            </div>
            <div class="flex justify-end"><button type="submit" class="{{ $btn }}">Générer tous les certificats</button></div>
        </form>
    </div>

    {{-- Liste --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b dark:border-gray-800 pb-3 mb-5">
            <h2 class="font-heading font-bold text-primary-700 dark:text-white">Certificats délivrés ({{ $certificats->total() }})</h2>
            <form method="GET" class="flex flex-wrap gap-2">
                <select name="mode" onchange="this.form.submit()" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm">
                    <option value="">Tous les modes</option>
                    <option value="en_ligne" @selected(request('mode') === 'en_ligne')>En ligne</option>
                    <option value="presentiel" @selected(request('mode') === 'presentiel')>En présentiel</option>
                </select>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Nom, n°, formation…" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm">
                <button class="px-4 py-2 rounded-lg bg-primary-700 text-white text-sm font-semibold">Filtrer</button>
            </form>
        </div>

        @if($certificats->isEmpty())
        <p class="text-center py-10 text-gray-400">Aucun certificat pour le moment.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 border-b dark:border-gray-800">
                        <th class="py-2 pr-3">Titulaire</th><th class="py-2 pr-3">Formation</th><th class="py-2 pr-3">N° / Code</th><th class="py-2 pr-3">Mode</th><th class="py-2 pr-3">Délivré le</th><th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($certificats as $cert)
                    <tr class="border-b dark:border-gray-800 align-middle">
                        <td class="py-3 pr-3">
                            <div class="flex items-center gap-3">
                                @if($cert->photo_url)
                                <img src="{{ $cert->photo_url }}" alt="" class="w-10 h-10 rounded-full object-cover border-2 border-accent-500 shrink-0">
                                @else
                                <span class="w-10 h-10 rounded-full bg-primary-700 text-accent-400 flex items-center justify-center text-xs font-bold shrink-0">{{ $cert->initiales }}</span>
                                @endif
                                <div>
                                    <p class="font-semibold text-primary-700 dark:text-white">{{ $cert->nom_complet }}</p>
                                    <p class="text-xs text-gray-500">{{ $cert->pays ?: '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 pr-3 max-w-xs">{{ $cert->intitule_formation }}</td>
                        <td class="py-3 pr-3"><span class="font-mono text-xs">{{ $cert->numero_affiche }}</span><br><a href="{{ route('certificat.verifier', $cert->code_verification) }}" target="_blank" class="font-mono text-xs text-accent-600 hover:underline">{{ $cert->code_verification }}</a></td>
                        <td class="py-3 pr-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $cert->estPresentiel() ? 'bg-accent-100 text-accent-700' : 'bg-sky-100 text-sky-700' }}">{{ $cert->estPresentiel() ? 'Présentiel' : 'En ligne' }}</span>
                            @unless($cert->is_public)<span class="block text-[11px] text-gray-400 mt-1">Masqué de la liste</span>@endunless
                        </td>
                        <td class="py-3 pr-3 whitespace-nowrap">{{ $cert->issued_at?->format('d/m/Y') }}</td>
                        <td class="py-3 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('admin.certificats.pdf', $cert) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-primary-700 text-white hover:bg-primary-600">PDF</a>
                                @if($cert->estPresentiel())
                                <a href="{{ route('admin.certificats.edit', $cert) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-800 hover:bg-gray-200">Modifier</a>
                                @endif
                                <form method="POST" action="{{ route('admin.certificats.visibilite', $cert) }}">
                                    @csrf @method('PATCH')
                                    <button class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-800 hover:bg-gray-200" title="Afficher / masquer dans la liste publique des certifiés">{{ $cert->is_public ? 'Masquer' : 'Afficher' }}</button>
                                </form>
                                @if($cert->estPresentiel() && auth()->user()->role === 'admin')
                                <form method="POST" action="{{ route('admin.certificats.destroy', $cert) }}" onsubmit="return confirm('Supprimer définitivement ce certificat ? Il ne sera plus vérifiable.')">
                                    @csrf @method('DELETE')
                                    <button class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50">Supprimer</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $certificats->links() }}</div>
        @endif
    </div>
</div>
@endsection
