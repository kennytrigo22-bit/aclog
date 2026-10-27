@extends('layouts.public')
@section('title', 'Politique de Confidentialité - ' . $siteTitre)

@section('content')
<div class="phead"><div class="c"><div class="bc"><a href="{{ route('home') }}">Accueil</a> › Confidentialité</div><h1>Politique de Confidentialité</h1><p>Dernière mise à jour : 1er octobre 2026</p></div></div>

<section class="py-10 lg:py-16 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-4xl mx-auto px-4 lg:px-6">
        <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 lg:p-10 shadow-sm prose prose-sm dark:prose-invert max-w-none prose-headings:font-heading prose-headings:text-primary-700 dark:prose-headings:text-white prose-a:text-accent-500">

            <h2>1. Responsable du traitement</h2>
            <p>{{ $siteTitre }} (ACLOG), dont le siège est situé au 4 Rue Abila, Talangai, Brazzaville, République du Congo, est responsable du traitement de vos données personnelles collectées via la plateforme www.cabinet-aclog.com.</p>
            <p><strong>Contact :</strong> {{ $emailContact }} | +242 06 828 79 15</p>

            <h2>2. Données collectées</h2>
            <p>Nous collectons les données suivantes dans le cadre de votre utilisation de la plateforme :</p>

            <h3>2.1 Données d'identification</h3>
            <ul>
                <li>Nom et prénom</li>
                <li>Adresse email</li>
                <li>Numéro de téléphone</li>
                <li>Date de naissance (optionnel)</li>
                <li>Ville et pays de résidence</li>
            </ul>

            <h3>2.2 Données de formation</h3>
            <ul>
                <li>Niveau d'études et profession</li>
                <li>Inscriptions aux formations</li>
                <li>Progression dans les cours (leçons suivies, quiz et examens passés)</li>
                <li>Scores, points et jetons accumulés</li>
                <li>Certificats obtenus</li>
                <li>Flags CTF capturés</li>
            </ul>

            <h3>2.3 Données de paiement</h3>
            <ul>
                <li>Montants des transactions</li>
                <li>Captures d'écran des reçus Mobile Money (pour vérification uniquement)</li>
                <li>Références de transaction</li>
            </ul>

            <h3>2.4 Données de navigation</h3>
            <ul>
                <li>Adresse IP</li>
                <li>Type de navigateur et système d'exploitation</li>
                <li>Pages visitées et durée de visite</li>
                <li>Cookies techniques nécessaires au fonctionnement du site</li>
            </ul>

            <h2>3. Finalités du traitement</h2>
            <p>Vos données sont traitées pour les finalités suivantes :</p>
            <ul>
                <li><strong>Gestion de votre compte</strong> : création, authentification et gestion de votre espace apprenant</li>
                <li><strong>Fourniture des formations</strong> : accès aux contenus, suivi de progression, évaluation et certification</li>
                <li><strong>Traitement des paiements</strong> : vérification et validation des transactions Mobile Money</li>
                <li><strong>Gamification</strong> : attribution des points, conversion en jetons, classement</li>
                <li><strong>Support</strong> : traitement de vos demandes via les tickets de support</li>
                <li><strong>Communication</strong> : envoi de notifications relatives à votre formation (paiement validé, certificat disponible)</li>
                <li><strong>Amélioration du service</strong> : analyse anonymisée de l'utilisation pour améliorer la plateforme</li>
            </ul>

            <h2>4. Base légale du traitement</h2>
            <p>Le traitement de vos données repose sur :</p>
            <ul>
                <li><strong>L'exécution contractuelle</strong> : pour la fourniture des services de formation souscrits</li>
                <li><strong>Le consentement</strong> : pour l'envoi de communications marketing (si vous y avez consenti)</li>
                <li><strong>L'intérêt légitime</strong> : pour la sécurité de la plateforme et l'amélioration des services</li>
            </ul>

            <h2>5. Partage des données</h2>
            <p>Vos données ne sont <strong>jamais vendues</strong> à des tiers. Elles peuvent être partagées uniquement avec :</p>
            <ul>
                <li>L'équipe pédagogique et administrative d'ACLOG (pour le suivi de votre formation)</li>
                <li>Les autorités compétentes (en cas d'obligation légale)</li>
            </ul>
            <p>Aucun transfert de données hors de la République du Congo n'est effectué, sauf nécessité technique liée à l'hébergement de la plateforme.</p>

            <h2>6. Durée de conservation</h2>
            <ul>
                <li><strong>Données de compte</strong> : conservées tant que votre compte est actif, puis 3 ans après la dernière connexion</li>
                <li><strong>Données de formation</strong> : conservées indéfiniment pour la vérification des certificats</li>
                <li><strong>Données de paiement</strong> : conservées 5 ans conformément aux obligations comptables</li>
                <li><strong>Données de navigation</strong> : conservées 13 mois maximum</li>
            </ul>

            <h2>7. Vos droits</h2>
            <p>Vous disposez des droits suivants sur vos données personnelles :</p>
            <ul>
                <li><strong>Droit d'accès</strong> : obtenir la confirmation que vos données sont traitées et en recevoir une copie</li>
                <li><strong>Droit de rectification</strong> : corriger vos données inexactes ou incomplètes via votre profil</li>
                <li><strong>Droit de suppression</strong> : demander la suppression de votre compte et données associées</li>
                <li><strong>Droit d'opposition</strong> : vous opposer au traitement de vos données à des fins de communication marketing</li>
                <li><strong>Droit à la portabilité</strong> : recevoir vos données dans un format structuré et lisible</li>
            </ul>
            <p>Pour exercer ces droits, contactez-nous à <strong>{{ $emailContact }}</strong> ou via le système de tickets de la plateforme. Nous répondrons dans un délai de 30 jours.</p>

            <h2>8. Sécurité des données</h2>
            <p>ACLOG met en oeuvre les mesures techniques et organisationnelles appropriées pour protéger vos données :</p>
            <ul>
                <li>Chiffrement des mots de passe (hachage bcrypt)</li>
                <li>Connexion sécurisée HTTPS</li>
                <li>Accès restreint aux données selon les rôles (apprenant, gestionnaire, administrateur)</li>
                <li>Sauvegardes régulières de la base de données</li>
            </ul>

            <h2>9. Cookies</h2>
            <p>La plateforme utilise uniquement des cookies techniques nécessaires au bon fonctionnement du site :</p>
            <ul>
                <li><strong>Cookie de session</strong> : authentification et navigation sécurisée</li>
                <li><strong>Cookie CSRF</strong> : protection contre les attaques de falsification de requêtes</li>
                <li><strong>Préférence de thème</strong> : mémorisation du mode clair/sombre (localStorage)</li>
            </ul>
            <p>Aucun cookie publicitaire ou de tracking tiers n'est utilisé.</p>

            <h2>10. Modifications</h2>
            <p>Cette politique de confidentialité peut être mise à jour. Toute modification significative sera notifiée par email ou via la plateforme. La date de dernière mise à jour est indiquée en haut de cette page.</p>

        </div>
    </div>
</section>
@endsection
