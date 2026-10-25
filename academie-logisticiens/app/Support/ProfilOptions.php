<?php

namespace App\Support;

class ProfilOptions
{
    public const SITUATIONS = [
        'etudiant' => 'Étudiant(e)',
        'professionnel' => 'Professionnel(le) en activité',
        'demandeur_emploi' => 'En recherche d\'emploi',
        'autre' => 'Autre',
    ];

    public const NIVEAUX_ETUDES = [
        'secondaire' => 'Secondaire / Bac',
        'bac+2' => 'Bac+2 (BTS, DUT)',
        'licence' => 'Licence (Bac+3)',
        'master' => 'Master (Bac+5)',
        'doctorat' => 'Doctorat',
        'autre' => 'Autre',
    ];

    public const SECTEURS = [
        'transport' => 'Transport',
        'logistique' => 'Logistique / Supply Chain',
        'commerce' => 'Commerce international',
        'douane' => 'Douane / Transit',
        'industrie' => 'Industrie / Production',
        'banque' => 'Banque / Assurance',
        'administration' => 'Administration publique',
        'autre' => 'Autre',
    ];

    public const SOURCES = [
        'reseaux_sociaux' => 'Réseaux sociaux',
        'bouche_a_oreille' => 'Bouche-à-oreille',
        'recherche_web' => 'Recherche sur internet',
        'employeur' => 'Mon employeur / mon école',
        'evenement' => 'Événement / salon',
        'autre' => 'Autre',
    ];

    /** Nom du pays => code ISO (utilise dans le numero du certificat). */
    public const PAYS = [
        'Congo' => 'CG', 'Congo (RDC)' => 'CD', 'Cameroun' => 'CM', 'Gabon' => 'GA', 'Tchad' => 'TD',
        'Centrafrique' => 'CF', 'Guinée équatoriale' => 'GQ', 'Angola' => 'AO', 'Burundi' => 'BI', 'Rwanda' => 'RW',
        'Sénégal' => 'SN', "Côte d'Ivoire" => 'CI', 'Bénin' => 'BJ', 'Togo' => 'TG', 'Mali' => 'ML',
        'Burkina Faso' => 'BF', 'Niger' => 'NE', 'Guinée' => 'GN', 'Guinée-Bissau' => 'GW', 'Mauritanie' => 'MR',
        'Gambie' => 'GM', 'Ghana' => 'GH', 'Nigeria' => 'NG', 'Libéria' => 'LR', 'Sierra Leone' => 'SL',
        'Cabo Verde' => 'CV', 'Maroc' => 'MA', 'Algérie' => 'DZ', 'Tunisie' => 'TN', 'Libye' => 'LY',
        'Égypte' => 'EG', 'Soudan' => 'SD', 'Soudan du Sud' => 'SS', 'Éthiopie' => 'ET', 'Érythrée' => 'ER',
        'Djibouti' => 'DJ', 'Somalie' => 'SO', 'Kenya' => 'KE', 'Ouganda' => 'UG', 'Tanzanie' => 'TZ',
        'Mozambique' => 'MZ', 'Madagascar' => 'MG', 'Maurice' => 'MU', 'Seychelles' => 'SC', 'Comores' => 'KM',
        'Zambie' => 'ZM', 'Zimbabwe' => 'ZW', 'Malawi' => 'MW', 'Botswana' => 'BW', 'Namibie' => 'NA',
        'Afrique du Sud' => 'ZA', 'Lesotho' => 'LS', 'Eswatini' => 'SZ', 'Sao Tomé-et-Principe' => 'ST',
        'France' => 'FR', 'Belgique' => 'BE', 'Suisse' => 'CH', 'Canada' => 'CA', 'États-Unis' => 'US',
        'Royaume-Uni' => 'GB', 'Allemagne' => 'DE', 'Italie' => 'IT', 'Espagne' => 'ES', 'Portugal' => 'PT',
        'Chine' => 'CN', 'Inde' => 'IN', 'Brésil' => 'BR', 'Émirats arabes unis' => 'AE', 'Turquie' => 'TR',
        'Autre' => 'XX',
    ];

    public static function codePays(?string $pays): string
    {
        return self::PAYS[$pays ?? ''] ?? 'XX';
    }

    /** Regles de validation communes inscription / profil. */
    public static function regles(): array
    {
        return [
            'date_naissance' => ['required', 'date', 'before:-10 years', 'after:1920-01-01'],
            'lieu_naissance' => ['required', 'string', 'max:255'],
            'situation' => ['required', 'in:' . implode(',', array_keys(self::SITUATIONS))],
            'niveau_etudes' => ['nullable', 'required_if:situation,etudiant', 'in:' . implode(',', array_keys(self::NIVEAUX_ETUDES))],
            'profession' => ['nullable', 'required_if:situation,professionnel', 'string', 'max:255'],
            'secteur_activite' => ['nullable', 'in:' . implode(',', array_keys(self::SECTEURS))],
            'etablissement' => ['nullable', 'string', 'max:255'],
            'ville' => ['nullable', 'string', 'max:255'],
            'pays' => ['required', 'in:' . implode(',', array_keys(self::PAYS))],
            'source_decouverte' => ['nullable', 'in:' . implode(',', array_keys(self::SOURCES))],
            'accept_marketing' => ['nullable', 'boolean'],
        ];
    }

    public static function messages(): array
    {
        return [
            'date_naissance.required' => 'La date de naissance est obligatoire (elle figure sur votre certificat).',
            'date_naissance.before' => 'Veuillez saisir une date de naissance valide.',
            'lieu_naissance.required' => 'Le lieu de naissance est obligatoire (il figure sur votre certificat).',
            'situation.required' => 'Indiquez si vous êtes étudiant(e) ou professionnel(le).',
            'niveau_etudes.required_if' => 'Indiquez votre niveau d\'études.',
            'profession.required_if' => 'Indiquez votre profession.',
        ];
    }

    public static function profilIncomplet($user): bool
    {
        return !$user->date_naissance || !$user->lieu_naissance || !$user->situation;
    }
}
