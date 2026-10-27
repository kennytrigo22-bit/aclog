@extends('layouts.public')

@section('content')
<div class="pg show" id="pg-accueil">
<div class="hero"><div class="c"><div class="hg"><div>
<span class="kick">Hub Digital Panafricain • Session {{ date('Y') }} Ouverte</span>
<h1>Propulsez votre carrière en <em>Logistique, Douane &amp; Commerce International</em></h1>
<p class="lead">Le 1er cabinet académique digital de référence en Afrique Centrale. Formations certifiantes, examens immersifs et parcours gamifiés avec récompenses convertibles en jetons FCFA.</p>
<div class="acts"><a class="btn gold" href="{{ route('formations.index') }}">Explorer le Catalogue</a><a class="btn reg" href="{{ auth()->check() ? route('formations.index') : route('register') }}">💳 S'inscrire et payer</a></div>
<div class="trust"><span>🔒 Certifications vérifiables par QR Code</span><span>📲 Mobile Money MTN &amp; Airtel (+242)</span><span>🎓 Experts terrain CEMAC</span></div></div>
<div class="cert"><img src="{{ asset('assets/site/certificat.jpg') }}" alt="Certificat de fin de formation ACLOG" width="988" height="703"><span class="cb cb-top">✓ Avec Certificat</span><span class="cb cb-bot">🏅 Délivré à chaque formation</span></div></div>
</div></div>

{{-- Bande statistiques --}}
<div class="stx"><div class="c"><div class="stx-p g4">
<div class="sc" style="--k:#2F6DB5"><span class="ic">🎓</span><b>{{ $nbFormations }}</b><small>Formations certifiantes</small></div>
<div class="sc" style="--k:#C0392B"><span class="ic">🧭</span><b>4</b><small>Filières d'expertise</small></div>
<div class="sc" style="--k:#8E5CC2"><span class="ic">⏱️</span><b>2</b><small>Mois par formation</small></div>
<div class="sc" style="--k:#2A9DB0"><span class="ic">🏅</span><b>100%</b><small>Avec certificat</small></div>
</div></div></div>
</div>

@if($aLaUne)
<section class="hm soft"><div class="c"><div class="hm-feat"><div class="im" style="background-image:linear-gradient(rgba(10,42,82,.2),rgba(10,42,82,.2)),url('{{ $aLaUne->image_url }}'),linear-gradient(135deg,#0E3A6B,#3F7BB5)"></div>
<div><span class="eyebrow">Formation à la une</span><h2>{{ $aLaUne->titre }}</h2><p class="meta">{{ $aLaUne->duree_heures ? $aLaUne->duree_heures . ' h · ' : '' }}{{ $aLaUne->modules_count }} module{{ $aLaUne->modules_count > 1 ? 's' : '' }} · Certification incluse</p><div class="pr">{{ number_format($aLaUne->prix_effectif, 0, ',', ' ') }} <small>FCFA</small></div><div class="acts" style="align-items:center;margin:0"><a class="btn blue" href="{{ route('formations.show', $aLaUne) }}">Voir le programme</a><a class="lnk" href="{{ route('formations.index') }}">Tout le catalogue →</a></div></div></div></div></section>
@endif

<section class="hm"><div class="c"><div class="hm-why">
<div><span>🔒</span><h3>Certificats vérifiables</h3><p>QR Code intégré à chaque certificat</p></div>
<div><span>📲</span><h3>Paiement Mobile Money</h3><p>MTN et Airtel (+242)</p></div>
<div><span>🎓</span><h3>Experts terrain</h3><p>Formateurs du secteur CEMAC</p></div>
<div><span>💻</span><h3>En ligne ou en salle</h3><p>Apprenez à votre rythme</p></div></div></div></section>

<section class="hm" style="padding-top:0"><div class="c"><div class="hm-pil"><div class="hm-h"><span class="eyebrow" style="color:#E6C26E">Nos filières</span><h2 style="color:#fff">Trois piliers pour maîtriser votre métier</h2><p style="color:#CFE0F3">Des parcours conçus avec les armateurs, les manutentionnaires de Pointe-Noire et les autorités douanières congolaises.</p></div>
<div class="hm-fil">
<a class="hf dk" href="{{ route('formations.index') }}"><span class="n">01</span><span class="ic">📦</span><h3>Supply Chain &amp; Logistique</h3><p>Stocks, flotte, flux et coûts logistiques, du corridor Brazzaville–Pointe-Noire au fleuve Congo.</p><span class="more">6 modules certifiants →</span></a>
<a class="hf dk" href="{{ route('formations.index') }}"><span class="n">02</span><span class="ic">🛃</span><h3>Douane, Transit &amp; CEMAC</h3><p>Régimes douaniers, SYDONIA World, classification tarifaire SH et transit inter-États.</p><span class="more">8 modules pratiques →</span></a>
<a class="hf dk" href="{{ route('formations.index') }}"><span class="n">03</span><span class="ic">🌍</span><h3>Commerce International &amp; Fret</h3><p>Fret maritime et aérien, assurance cargo, crédit documentaire et Incoterms® 2020.</p><span class="more">5 modules internationaux →</span></a></div></div></div></section>

@include('public.partials.temoignages')

@include('public.partials.cta')
@endsection
