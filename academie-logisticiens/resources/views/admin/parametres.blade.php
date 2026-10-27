@extends('layouts.admin')
@section('page-title', 'Paramètres')

@section('content')
@php
    $sections = [
        'Identité du site' => [
            ['cle' => 'site_titre', 'description' => 'Titre du site', 'defaut' => 'CABINET ACLOG', 'aide' => 'Affiché dans le menu, le pied de page, les onglets du navigateur et les e-mails.'],
            ['cle' => 'site_slogan', 'description' => 'Slogan', 'defaut' => 'La compétence, rien que la compétence.', 'aide' => 'Affiché juste sous le titre.'],
        ],
        'Informations légales (pied de page et certificats)' => [
            ['cle' => 'legal_rccm', 'description' => 'RCCM', 'defaut' => 'CG-BZV-01-2026-A10-01925'],
            ['cle' => 'legal_scien', 'description' => 'SCIEN', 'defaut' => '2173996'],
            ['cle' => 'legal_sciet', 'description' => 'SCIET', 'defaut' => '2173996017'],
            ['cle' => 'legal_niu', 'description' => 'NIU', 'defaut' => 'P230000004669493'],
        ],
        'Contact' => [
            ['cle' => 'telephone_contact_1', 'description' => 'Téléphone principal', 'defaut' => '+242 06 828 79 15'],
            ['cle' => 'telephone_contact_2', 'description' => 'Téléphone secondaire', 'defaut' => '+242 05 599 73 34'],
            ['cle' => 'email_contact', 'description' => 'Email de contact (reçoit les messages du formulaire)', 'defaut' => 'contact@cabinet-aclog.com'],
            ['cle' => 'adresse', 'description' => 'Adresse du siège', 'defaut' => '4 Rue Abila, Talangai, Brazzaville'],
            ['cle' => 'whatsapp_numero', 'description' => 'Numéro WhatsApp (chiffres uniquement, avec indicatif)', 'defaut' => '242068287915', 'aide' => 'Utilisé par les boutons WhatsApp du site.'],
            ['cle' => 'lien_youtube', 'description' => 'Lien de la chaîne YouTube', 'defaut' => 'https://www.youtube.com/@academiedeslogisticiensdid2613'],
            ['cle' => 'lien_facebook', 'description' => 'Lien de la page Facebook', 'defaut' => 'https://www.facebook.com/profile.php?id=100088676956387'],
        ],
        'Paiement Mobile Money' => [
            ['cle' => 'numero_paiement', 'description' => 'Numéro Mobile Money', 'defaut' => '+242 068 287 915'],
            ['cle' => 'nom_beneficiaire', 'description' => 'Nom du bénéficiaire', 'defaut' => 'Didi Chance AMBIA'],
            ['cle' => 'operateurs_mobile_money', 'description' => 'Opérateurs acceptés', 'defaut' => 'MTN et Airtel'],
            ['cle' => 'delai_validation_paiement', 'description' => 'Délai de validation d\'un paiement (minutes)', 'defaut' => '30'],
            ['cle' => 'delai_remboursement', 'description' => 'Délai de remboursement en cas de rejet (minutes)', 'defaut' => '5'],
            ['cle' => 'info_virement_bancaire', 'description' => 'Information sur le virement bancaire', 'defaut' => 'Paiement par virement bancaire possible. Contactez-nous pour les coordonnées bancaires.'],
        ],
        'Scores et récompenses' => [
            ['cle' => 'score_minimum_defaut', 'description' => 'Score minimum de réussite (%) - tests de niveau', 'defaut' => '70'],
            ['cle' => 'score_minimum_quiz', 'description' => 'Score minimum pour valider un quiz de module (%)', 'defaut' => '70'],
            ['cle' => 'score_minimum_challenge', 'description' => 'Score minimum pour capturer un drapeau CTF (%)', 'defaut' => '70'],
            ['cle' => 'points_test_reussi', 'description' => 'Points par test de niveau réussi', 'defaut' => '2'],
            ['cle' => 'points_flag_capture', 'description' => 'Points par défaut d\'un drapeau CTF', 'defaut' => '5'],
            ['cle' => 'seuil_jetons', 'description' => 'Seuil de points pour la conversion en jetons', 'defaut' => '100'],
            ['cle' => 'valeur_jetons_fcfa', 'description' => 'Valeur d\'une conversion (FCFA)', 'defaut' => '5000'],
        ],
    ];
    $i = 0;
    $inp = 'w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500';
@endphp

<form method="POST" action="{{ route('admin.parametres.update') }}" class="max-w-2xl space-y-6">
    @csrf

    @foreach($sections as $titre => $champs)
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm space-y-4">
        <h2 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3">{{ $titre }}</h2>
        @foreach($champs as $def)
        @php $param = $parametres->firstWhere('cle', $def['cle']); @endphp
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ $def['description'] }}</label>
            <input type="hidden" name="parametres[{{ $i }}][cle]" value="{{ $def['cle'] }}">
            <input type="text" name="parametres[{{ $i }}][valeur]" value="{{ old("parametres.$i.valeur", $param ? $param->valeur : $def['defaut']) }}" class="{{ $inp }}">
            @if(!empty($def['aide']))<p class="text-xs text-gray-400 mt-0.5">{{ $def['aide'] }}</p>@endif
        </div>
        @php $i++; @endphp
        @endforeach
    </div>
    @endforeach

    <button type="submit" class="px-6 py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition">Enregistrer les paramètres</button>
</form>

{{-- Temoignages de l'accueil (formulaires separes du formulaire des parametres) --}}
<div id="temoignages" class="max-w-2xl mt-8 bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm space-y-5 scroll-mt-20">
    <div class="border-b dark:border-gray-800 pb-3">
        <h2 class="font-heading font-bold text-primary-700 dark:text-white">Témoignages (page d'accueil)</h2>
        <p class="text-xs text-gray-400 mt-1">Les témoignages affichés s'enchaînent dans la section « Ils témoignent de Brazzaville à Pointe-Noire ». Si aucun n'est affiché, la section disparaît.</p>
    </div>

    @if($temoignages->isEmpty())
    <p class="text-sm text-gray-400 text-center py-4">Aucun témoignage pour le moment.</p>
    @else
    <ul class="space-y-3">
        @foreach($temoignages as $t)
        <li class="flex flex-col sm:flex-row sm:items-start gap-3 p-4 rounded-lg {{ $t->actif ? 'bg-surface-light dark:bg-surface-darker' : 'bg-gray-100 dark:bg-gray-800 opacity-60' }}">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                @if($t->photo_url)
                <img src="{{ $t->photo_url }}" alt="" class="w-11 h-11 rounded-full object-cover shrink-0">
                @else
                <span class="w-11 h-11 rounded-full bg-primary-700 text-accent-400 flex items-center justify-center text-sm font-bold shrink-0">{{ $t->initiales }}</span>
                @endif
                <div class="min-w-0">
                    <p class="text-sm italic text-gray-600 dark:text-gray-300">« {{ $t->citation }} »</p>
                    <p class="text-sm font-semibold text-primary-700 dark:text-white mt-1">{{ $t->nom }}@if($t->fonction)<span class="font-normal text-gray-500"> — {{ $t->fonction }}</span>@endif</p>
                    @unless($t->actif)<p class="text-xs text-gray-500 mt-0.5">Masqué de l'accueil</p>@endunless
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 shrink-0">
                <form method="POST" action="{{ route('admin.temoignages.toggle', $t) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-800 hover:bg-gray-200">{{ $t->actif ? 'Masquer' : 'Afficher' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.temoignages.destroy', $t) }}" onsubmit="return confirm('Supprimer ce témoignage ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Supprimer</button>
                </form>
            </div>
        </li>
        @endforeach
    </ul>
    @endif

    <form method="POST" action="{{ route('admin.temoignages.store') }}" enctype="multipart/form-data" class="space-y-4 border-t dark:border-gray-800 pt-5">
        @csrf
        <h3 class="text-sm font-bold text-accent-600 uppercase tracking-wider">Ajouter un témoignage</h3>
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Témoignage <span class="text-red-500">*</span></label>
            <textarea name="citation" rows="3" maxlength="600" required placeholder="Ce que dit l'apprenant (sans guillemets)" class="{{ $inp }}">{{ old('citation') }}</textarea>
            @error('citation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nom <span class="text-red-500">*</span></label>
                <input type="text" name="nom" maxlength="120" required value="{{ old('nom') }}" placeholder="Ex : Clarisse Moukala" class="{{ $inp }}">
                @error('nom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Fonction / ville</label>
                <input type="text" name="fonction" maxlength="160" value="{{ old('fonction') }}" placeholder="Ex : Superviseur Logistique • Brazzaville" class="{{ $inp }}">
                @error('fonction') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Photo (facultative — sinon les initiales sont affichées)</label>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="{{ $inp }} file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-accent-500 file:text-primary-700 file:font-semibold file:text-sm hover:file:bg-accent-400 file:cursor-pointer">
            @error('photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="px-6 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition">Ajouter le témoignage</button>
    </form>
</div>
@endsection
