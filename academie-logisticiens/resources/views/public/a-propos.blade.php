@extends('layouts.public')

@section('title', 'À propos - ' . $siteTitre)

@section('content')
@php
    $nbApprenants = max(23, \App\Models\User::where('role', 'apprenant')->count());
    $nbFormationsEnLigne = \App\Models\Formation::where('is_published', true)->count();
@endphp
<div class="pg show" id="pg-apropos">
<div class="phead"><div class="c"><div class="bc"><a href="{{ route('home') }}">Accueil</a> › À propos</div><h1>À propos du <em>{{ $siteTitre }}</em></h1><p>Académie des Logisticiens — « {{ $siteSlogan }} » Le hub digital qui forme les professionnels de la logistique, de la douane et du commerce international en Afrique Centrale.</p></div></div>
<section><div class="c split"><div><span class="eyebrow">Qui sommes-nous</span><h2>Le 1er cabinet académique digital de référence en Afrique Centrale</h2>
<p>Le {{ $siteTitre }} est un cabinet international d'expertise en gestion logistique et transport, basé à Brazzaville (4 Rue Abila, Talangai). Il est spécialisé dans les formations certifiantes en logistique, transport, commerce international, douane et transit.</p>
<p>Nos programmes sont conçus avec les armateurs, les manutentionnaires de Pointe-Noire et les autorités douanières congolaises, pour former des professionnels immédiatement opérationnels sur le terrain.</p>
<div class="chips"><span>📦 Logistique &amp; Supply Chain</span><span>🌍 Commerce International</span><span>🛃 Douane &amp; Transit CEMAC</span><span>🚚 Transport &amp; Distribution</span></div></div>
<div class="showcase"><img src="{{ asset('assets/site/logo-aclog.png') }}" alt="Logo {{ $siteTitre }}"><h3>Académie des Logisticiens</h3><p>Formations certifiantes · Brazzaville, Congo</p></div></div></section>
<div class="band"><div class="c g4"><div><b>{{ $nbApprenants }}+</b><small>Apprenants actifs</small></div><div><b>98.4%</b><small>Taux de réussite</small></div><div><b>{{ $nbFormationsEnLigne }}+</b><small>Formations certifiantes</small></div><div><b>3</b><small>Piliers d'excellence</small></div></div></div>
<section class="alt"><div class="c"><div class="sh"><small>Notre identité</small><h2>Mission, vision &amp; engagement</h2><p>Trois principes qui guident chacune de nos formations.</p></div>
<div class="g3"><div class="card"><div class="ico">🎯</div><h3>Notre mission</h3><p>Former des professionnels opérationnels, capables d'évoluer dans les ports, les agences en douane, les entreprises de transport et les multinationales d'Afrique Centrale.</p></div>
<div class="card"><div class="ico">🔭</div><h3>Notre vision</h3><p>Devenir la référence académique digitale de la logistique, de la douane et du commerce international en Afrique Centrale.</p></div>
<div class="card"><div class="ico">🤝</div><h3>Notre engagement</h3><p>Des formations pratiques, des certificats vérifiables par QR Code et un paiement simple par Mobile Money MTN &amp; Airtel.</p></div></div></div></section>
<section class="pc"><div class="c"><h2 class="pc-t">Votre parcours chez ACLOG</h2>
<ol class="pc-l">
<li><span class="pc-n">1</span><span class="pc-i">📚</span><h3>Choisissez votre formation</h3><p>Parmi nos {{ $nbFormationsEnLigne }} formations certifiantes en logistique, transport, commerce international, douane et transit.</p></li>
<li><span class="pc-n">2</span><span class="pc-i">📲</span><h3>Inscrivez-vous et payez</h3><p>Réglez par MTN Mobile Money ou Airtel Money, puis confirmez votre paiement sur WhatsApp.</p></li>
<li><span class="pc-n">3</span><span class="pc-i">🎯</span><h3>Formez-vous pendant 2 mois</h3><p>Cours en ligne ou en présentiel, ateliers pratiques et accompagnement individualisé.</p></li>
<li><span class="pc-n">4</span><span class="pc-i">🏅</span><h3>Recevez votre certificat</h3><p>Chaque formation se conclut par un certificat d'aptitude professionnelle.</p></li>
</ol></div></section>
@include('public.partials.annuaire', ['certifies' => $certifies])
<section><div class="c"><div class="sh"><small>La direction</small><h2>Une expertise de terrain à votre service</h2></div>
<div class="dg"><figure class="ph" id="dgphoto"><span class="ini">DA</span><img src="{{ asset('assets/site/dg.jpg') }}" alt="Didi-Chance AMBIA, Directeur Général du Cabinet ACLOG" width="360" height="450"></figure><div><h3>Didi-Chance AMBIA</h3><small>Directeur Général</small><p>Expert en Gestion Logistique et Transport, il dirige le {{ $siteTitre }} et veille à la qualité et à la crédibilité de chaque certification délivrée.</p></div></div></div></section>
</div>

@include('public.partials.cta')
@endsection
