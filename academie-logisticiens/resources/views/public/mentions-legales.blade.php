@extends('layouts.public')
@section('title', 'Mentions Légales - ' . $siteTitre)

@section('content')
<div class="pt-28"></div>

<section class="bg-primary-700 text-white py-10 lg:py-14 relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/thumbs/14967.jpg') }}" alt="" class="w-full h-full object-cover opacity-15">
    </div>
    <div class="max-w-4xl mx-auto px-4 lg:px-6 relative z-10">
        <h1 class="font-heading text-3xl md:text-4xl font-bold">Mentions Légales</h1>
        <p class="text-gray-300 mt-2">Informations légales obligatoires</p>
    </div>
</section>

<section class="py-10 lg:py-16 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-4xl mx-auto px-4 lg:px-6">
        <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 lg:p-10 shadow-sm prose prose-sm dark:prose-invert max-w-none prose-headings:font-heading prose-headings:text-primary-700 dark:prose-headings:text-white prose-a:text-accent-500">

            <h2>1. Éditeur du site</h2>
            <table>
                <tbody>
                    <tr><td class="font-semibold pr-4">Dénomination</td><td>{{ $siteTitre }} (ACLOG)</td></tr>
                    <tr><td class="font-semibold pr-4">Forme juridique</td><td>Cabinet académique digital</td></tr>
                    <tr><td class="font-semibold pr-4">Siège social</td><td>4 Rue Abila, Talangai, Brazzaville, République du Congo</td></tr>
                    <tr><td class="font-semibold pr-4">Directeur</td><td>Didi Chance AMBIA</td></tr>
                    <tr><td class="font-semibold pr-4">Téléphone</td><td>+242 06 828 79 15 / +242 05 599 73 34</td></tr>
                    <tr><td class="font-semibold pr-4">Email</td><td>{{ $emailContact }}</td></tr>
                    <tr><td class="font-semibold pr-4">Site web</td><td>www.cabinet-aclog.com</td></tr>
                </tbody>
            </table>

            <h2>2. Directeur de la publication</h2>
            <p>Le directeur de la publication est <strong>Didi Chance AMBIA</strong>, en sa qualité de fondateur et directeur de {{ $siteTitre }}.</p>

            <h2>3. Hébergement</h2>
            <table>
                <tbody>
                    <tr><td class="font-semibold pr-4">Hébergeur</td><td>À définir</td></tr>
                    <tr><td class="font-semibold pr-4">Adresse</td><td>À définir</td></tr>
                    <tr><td class="font-semibold pr-4">Téléphone</td><td>À définir</td></tr>
                </tbody>
            </table>
            <p class="text-sm text-gray-500 italic">Ces informations seront mises à jour lors du déploiement en production.</p>

            <h2>4. Activité</h2>
            <p>{{ $siteTitre }} est un cabinet académique digital spécialisé dans la formation professionnelle certifiante en ligne, dans les domaines de :</p>
            <ul>
                <li>La logistique et la supply chain</li>
                <li>Le commerce international et les Incoterms</li>
                <li>La douane, le transit et la réglementation CEMAC</li>
                <li>Le transport et la distribution</li>
            </ul>
            <p>ACLOG est le 1er cabinet académique digital de référence en Afrique Centrale, basé à Brazzaville, République du Congo.</p>

            <h2>5. Propriété intellectuelle</h2>
            <p>L'ensemble du contenu de ce site (textes, images, vidéos, logos, icônes, sons, logiciels, bases de données, mises en page, graphismes) est protégé par les lois en vigueur en République du Congo et par les conventions internationales relatives à la propriété intellectuelle.</p>
            <p>La reproduction, représentation, modification, publication ou adaptation de tout ou partie du contenu du site, quel que soit le moyen ou le procédé utilisé, est interdite sans autorisation écrite préalable d'ACLOG.</p>
            <p>Le logo, la marque « {{ $siteTitre }} » et « ACLOG » sont des marques déposées. Toute utilisation non autorisée constitue une contrefaçon passible de sanctions pénales et civiles.</p>

            <h2>6. Limitation de responsabilité</h2>
            <p>ACLOG s'efforce d'assurer l'exactitude et la mise à jour des informations diffusées sur ce site. Toutefois, ACLOG ne peut garantir l'exactitude, la complétude ou l'actualité des informations publiées. En conséquence, l'utilisateur reconnaît utiliser ces informations sous sa responsabilité exclusive.</p>
            <p>ACLOG ne pourra être tenue responsable des dommages directs ou indirects résultant de :</p>
            <ul>
                <li>L'accès ou l'impossibilité d'accès au site</li>
                <li>L'utilisation des informations disponibles sur le site</li>
                <li>Du fait d'un tiers</li>
            </ul>

            <h2>7. Liens hypertextes</h2>
            <p>Le site peut contenir des liens vers des sites tiers. ACLOG n'exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu, leurs pratiques de confidentialité ou leur disponibilité.</p>

            <h2>8. Protection des données personnelles</h2>
            <p>ACLOG accorde une importance particulière à la protection de vos données personnelles. Pour en savoir plus sur la collecte, le traitement et la protection de vos données, veuillez consulter notre <a href="{{ route('confidentialite') }}">Politique de Confidentialité</a>.</p>

            <h2>9. Droit applicable</h2>
            <p>Les présentes mentions légales sont régies par le droit de la République du Congo. Tout litige relatif à l'utilisation du site sera soumis à la compétence exclusive des tribunaux de Brazzaville.</p>

            <h2>10. Contact</h2>
            <p>Pour toute question relative aux présentes mentions légales, vous pouvez nous contacter :</p>
            <ul>
                <li><strong>Email :</strong> {{ $emailContact }}</li>
                <li><strong>Téléphone :</strong> +242 06 828 79 15</li>
                <li><strong>Adresse :</strong> 4 Rue Abila, Talangai, Brazzaville, République du Congo</li>
            </ul>

        </div>
    </div>
</section>
@endsection
