@extends('layouts.public')
@section('title', 'Conditions Générales de Vente - ' . $siteTitre)

@section('content')
<div class="phead"><div class="c"><div class="bc"><a href="{{ route('home') }}">Accueil</a> › Conditions générales</div><h1>Conditions Générales de Vente</h1><p>Dernière mise à jour : 1er octobre 2026</p></div></div>

<section class="py-10 lg:py-16 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-4xl mx-auto px-4 lg:px-6">
        <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 lg:p-10 shadow-sm prose prose-sm dark:prose-invert max-w-none prose-headings:font-heading prose-headings:text-primary-700 dark:prose-headings:text-white prose-a:text-accent-500">

            <h2>Article 1 - Objet</h2>
            <p>Les présentes Conditions Générales de Vente (CGV) régissent l'ensemble des relations contractuelles entre {{ $siteTitre }} (ci-après « ACLOG »), sise au 4 Rue Abila, Talangai, Brazzaville, République du Congo, et toute personne physique ou morale (ci-après « l'Apprenant ») souscrivant à une formation sur la plateforme en ligne accessible à l'adresse www.cabinet-aclog.com.</p>

            <h2>Article 2 - Formations proposées</h2>
            <p>ACLOG propose des formations certifiantes en ligne dans les domaines suivants :</p>
            <ul>
                <li>Logistique et Supply Chain</li>
                <li>Commerce International et Incoterms</li>
                <li>Douane, Transit et Réglementation CEMAC</li>
                <li>Transport et Distribution</li>
            </ul>
            <p>Chaque formation comprend des modules vidéo, des supports textuels, des quiz de chapitre, un examen final et, en cas de réussite, un certificat vérifiable par QR code.</p>

            <h2>Article 3 - Inscription et accès</h2>
            <p>L'inscription à une formation est subordonnée à :</p>
            <ul>
                <li>La création d'un compte utilisateur sur la plateforme</li>
                <li>La validation du paiement intégral du prix de la formation</li>
                <li>L'acceptation des présentes CGV</li>
            </ul>
            <p>L'accès aux contenus est activé dans un délai maximum de 30 minutes après validation du paiement par l'équipe ACLOG. L'apprenant dispose d'un accès illimité dans le temps aux contenus de la formation souscrite.</p>

            <h2>Article 4 - Tarifs et paiement</h2>
            <p>Les prix des formations sont indiqués en Francs CFA (FCFA) et sont susceptibles de modification à tout moment. Le prix applicable est celui affiché au moment de la souscription.</p>
            <p>Les modes de paiement acceptés sont :</p>
            <ul>
                <li><strong>Mobile Money MTN</strong> : transfert vers le +242 068 287 915 (Didi Chance AMBIA)</li>
                <li><strong>Mobile Money Airtel</strong> : transfert vers le +242 068 287 915 (Didi Chance AMBIA)</li>
            </ul>
            <p>L'apprenant doit joindre une capture d'écran du reçu de paiement lors de la confirmation. Le paiement est vérifié manuellement par l'équipe administrative.</p>

            <h2>Article 5 - Codes promotionnels</h2>
            <p>ACLOG peut proposer des codes promotionnels offrant des réductions en pourcentage ou en montant fixe. Ces codes sont soumis à des conditions de validité (dates, nombre d'utilisations, périmètre de formation). Un seul code promotionnel peut être appliqué par transaction.</p>

            <h2>Article 6 - Système de points et jetons</h2>
            <p>L'apprenant accumule des points selon les activités réalisées :</p>
            <ul>
                <li><strong>+2 points</strong> par test de niveau réussi</li>
                <li><strong>+5 points</strong> par flag CTF capturé</li>
            </ul>
            <p>Les points sont convertibles en jetons FCFA au taux de <strong>100 points = 5 000 FCFA</strong>. Les jetons sont utilisables comme réduction sur les prochaines formations. Les points et jetons ne sont ni remboursables, ni transférables, ni échangeables contre des espèces.</p>

            <h2>Article 7 - Certificats</h2>
            <p>Un certificat est délivré à l'apprenant ayant obtenu un score minimum de 70% à l'examen final de la formation. Ce certificat est vérifiable en ligne via un QR code unique et un code de vérification alphanumérique. ACLOG ne garantit pas la reconnaissance de ses certificats par des organismes tiers ou des employeurs.</p>

            <h2>Article 8 - Droit de rétractation</h2>
            <p>Conformément à la nature des services numériques, aucun remboursement n'est possible une fois l'accès à la formation activé. L'apprenant reconnaît renoncer expressément à son droit de rétractation dès le début de la consommation du contenu numérique.</p>
            <p>En cas de non-validation du paiement (rejet), le montant est remboursé dans un délai de 5 jours ouvrables via le même canal de paiement.</p>

            <h2>Article 9 - Propriété intellectuelle</h2>
            <p>L'ensemble des contenus de la plateforme (textes, vidéos, images, logos, quiz, examens) est la propriété exclusive d'ACLOG. Toute reproduction, diffusion ou utilisation commerciale sans autorisation écrite préalable est interdite et constitue une contrefaçon sanctionnée par la loi congolaise.</p>

            <h2>Article 10 - Responsabilité</h2>
            <p>ACLOG s'engage à fournir un service de qualité et à assurer la disponibilité de la plateforme. Toutefois, ACLOG ne saurait être tenue responsable :</p>
            <ul>
                <li>Des interruptions temporaires pour maintenance</li>
                <li>Des problèmes de connexion Internet de l'apprenant</li>
                <li>De l'utilisation faite par l'apprenant des connaissances acquises</li>
            </ul>

            <h2>Article 11 - Support et réclamations</h2>
            <p>L'apprenant peut contacter le support via :</p>
            <ul>
                <li>Le système de tickets intégré à la plateforme</li>
                <li>Le chatbot en ligne (Assistant ACLOG)</li>
                <li>Par email à : {{ $emailContact }}</li>
                <li>Par téléphone au : +242 06 828 79 15</li>
            </ul>

            <h2>Article 12 - Loi applicable et juridiction</h2>
            <p>Les présentes CGV sont régies par le droit de la République du Congo. En cas de litige, les parties s'engagent à rechercher une solution amiable. À défaut, le litige sera soumis aux tribunaux compétents de Brazzaville.</p>

            <h2>Article 13 - Modification des CGV</h2>
            <p>ACLOG se réserve le droit de modifier les présentes CGV à tout moment. Les modifications entrent en vigueur dès leur publication sur la plateforme. L'utilisation continue de la plateforme vaut acceptation des nouvelles conditions.</p>

        </div>
    </div>
</section>
@endsection
