@extends('layouts.public')

@section('title', 'Contact - ' . $siteTitre)

@section('content')
@php $tel = preg_replace('/\D+/', '', $telContact); @endphp
<div class="pg show" id="pg-contact"><div class="phead"><div class="c"><div class="bc"><a href="{{ route('home') }}">Accueil</a> › Contact</div><h1>Parlons de votre <em>projet de formation</em></h1><p>Une question, un devis, une inscription ? L'équipe du {{ $siteTitre }} vous répond.</p></div></div>
<section><div class="c">
@if(session('success'))<div class="flash ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="flash ko">{{ session('error') }}</div>@endif
<div class="g4">
<div class="card ct"><div class="ico">💬</div><h3>WhatsApp</h3><p>Réponse simple et rapide</p><a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">Ouvrir WhatsApp →</a></div>
<div class="card ct"><div class="ico">📞</div><h3>Téléphone</h3><p>{{ $telContact }}</p><a href="tel:+{{ $tel }}">Appeler maintenant →</a></div>
<div class="card ct"><div class="ico">✉️</div><h3>E-mail</h3><p>{{ $emailContact }}</p><a href="mailto:{{ $emailContact }}">Envoyer un e-mail →</a></div>
<div class="card ct"><div class="ico">📍</div><h3>Brazzaville</h3><p>4 Rue Abila, Talangai<br>République du Congo</p><a href="https://www.google.com/maps/search/?api=1&query=4+Rue+Abila+Talangai+Brazzaville" target="_blank" rel="noopener">Voir sur la carte →</a></div></div>
<form class="fm" id="cf" onsubmit="return false"><h3 style="margin:0;color:var(--bd);font-size:1.4rem">Envoyez-nous un message</h3>
<label for="cn">Nom complet</label><input id="cn" placeholder="Votre nom" required>
<label for="cm">Message</label><textarea id="cm" rows="4" placeholder="Décrivez votre besoin"></textarea>
<div class="acts"><button type="button" class="btn wa" id="cw">Envoyer sur WhatsApp</button><button type="button" class="btn blue" id="ce">Envoyer par e-mail</button></div></form></div></section>
{{-- FAQ (fin de la page Contact) --}}
<section class="faq"><div class="c"><div class="faq-h"><small>Questions fréquentes</small><h2>Vos questions, nos réponses</h2></div>
<div class="faq-l">
<details class="fq"><summary>Comment m'inscrire à une formation ?<span class="pl" aria-hidden="true"></span></summary><div class="fa"><p>Choisissez votre formation dans le <a href="{{ route('formations.index') }}">catalogue</a>, puis cliquez sur « S'inscrire et payer ». Créez votre compte sur <a href="{{ route('register') }}">{{ preg_replace('#^https?://#', '', route('register')) }}</a>, réglez les frais par Mobile Money et votre accès est activé. Vous préférez être accompagné ? Écrivez-nous sur <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">WhatsApp</a>, nous vous inscrivons pas à pas.</p></div></details>
<details class="fq"><summary>Quels moyens de paiement sont disponibles ?<span class="pl" aria-hidden="true"></span></summary><div class="fa"><p>Vous pouvez payer par <strong>MTN Mobile Money</strong> et <strong>Airtel Money</strong> (+242). Les <strong>jetons FCFA</strong> gagnés lors de nos challenges peuvent aussi être utilisés pour réduire le prix de votre formation ou de votre certificat.</p></div></details>
<details class="fq"><summary>Combien de temps dure une formation ?<span class="pl" aria-hidden="true"></span></summary><div class="fa"><p>En moyenne, une formation se déroule sur <strong>2 mois</strong>. Le volume horaire dépend du programme : par exemple, « Procédures Douanières et Transit CEMAC » compte 50 heures réparties en 10 modules. Le détail est indiqué sur la fiche de chaque formation.</p></div></details>
<details class="fq"><summary>Les formations sont-elles certifiantes ?<span class="pl" aria-hidden="true"></span></summary><div class="fa"><p>Oui. Chaque formation réussie donne droit à un <strong>Certificat d'Aptitude Professionnelle</strong> délivré par le Cabinet-ACLOG. Il comporte un numéro d'enregistrement et un <strong>QR Code</strong> qui permet à tout employeur d'en vérifier l'authenticité.</p></div></details>
<details class="fq"><summary>Les cours sont-ils en ligne ou en présentiel ?<span class="pl" aria-hidden="true"></span></summary><div class="fa"><p>Les deux. Vous pouvez suivre les cours <strong>en ligne</strong>, à votre rythme, ou <strong>en présentiel</strong> dans nos salles de formation à Brazzaville (4 Rue Abila, Talangai). Dans les deux cas, les cours sont dispensés par des experts de terrain.</p></div></details>
<details class="fq"><summary>Comment obtenir un devis pour mon entreprise ?<span class="pl" aria-hidden="true"></span></summary><div class="fa"><p>Pour former une équipe, contactez-nous par <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">WhatsApp</a>, au <a href="tel:+{{ $tel }}">{{ $telContact }}</a> ou par e-mail à <a href="mailto:{{ $emailContact }}">{{ $emailContact }}</a>. Précisez le nombre de participants et la formation souhaitée : nous vous adressons une proposition adaptée.</p></div></details>
</div></div></section></div>
@endsection
