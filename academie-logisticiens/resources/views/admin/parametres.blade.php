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
@endsection
