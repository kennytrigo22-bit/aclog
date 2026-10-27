@extends('layouts.public')

@section('title', 'Formations & Catalogue - ' . $siteTitre)

@section('content')
@php $total = collect($filieres)->sum(fn ($g) => count($g['items'])); @endphp
<div class="pg show" id="pg-formations"><div class="phead"><div class="c"><div class="bc"><a href="{{ route('home') }}">Accueil</a> › Formations</div><h1>Formations &amp; <em>Catalogue</em></h1><p>Des programmes certifiants en logistique, douane, transit et commerce international, payables par Mobile Money ou jetons FCFA.</p></div></div></div>

<section><div class="c"><div class="head"><div class="sh"><small>Nos filières</small><h2>3 Piliers d'Excellence pour Dominer le Marché</h2><p>Conçus avec les armateurs, manutentionnaires de Pointe-Noire et les autorités douanières congolaises.</p></div><a class="btn blue" href="#catalogue">Toutes les spécialisations</a></div>
<div class="g3">
<div class="card"><span class="pil">PILIER I</span><h3>Supply Chain &amp; Logistique Multimodale</h3><p>Gestion opérationnelle des stocks, corridors routiers Brazzaville-Pointe-Noire et logistique fluviale sur le fleuve Congo.</p><ul><li>WMS &amp; Optimisation des flux</li><li>Gestion de flotte &amp; Télématique</li><li>KPI de rupture et coûts logistiques</li></ul><span class="mod">6 Modules Certifiants</span></div>
<div class="card"><span class="pil">PILIER II</span><h3>Douane, Transit &amp; Législation CEMAC</h3><p>Maîtrise des régimes douaniers suspensifs, contentieux douanier, SYDONIA World et classification tarifaire SH.</p><ul><li>Déclarations en douane &amp; D24</li><li>Incoterms® 2020 &amp; Valeur en douane</li><li>Transit corridor RCA &amp; Tchad</li></ul><span class="mod">8 Modules Pratiques</span></div>
<div class="card"><span class="pil">PILIER III</span><h3>Commerce International &amp; Fret</h3><p>Négociation maritime, fret aérien IATA, assurance transport cargo et instruments de paiement documentaire.</p><ul><li>Connaissement maritime (B/L) &amp; LTA</li><li>Crédit documentaire &amp; Lettre de crédit</li><li>Surestaries et détention au port</li></ul><span class="mod">5 Modules Internationaux</span></div></div></div></section>

@if($vedette)
<section class="alt"><div class="c"><div class="sh"><small>Programmes à impact immédiat</small><h2>Formations Phares &amp; Populaires</h2><p>Inscrivez-vous directement avec MTN Mobile Money, Airtel Money ou vos jetons FCFA.</p></div>
<div class="feat"><div class="im" style="background-image:linear-gradient(rgba(10,42,82,.25),rgba(10,42,82,.25)),url('{{ $vedette->image_url }}'),linear-gradient(135deg,#0E3A6B,#3F7BB5)"></div>
<div class="bd">@if($vedette->is_featured)<span class="pill">Populaire</span>@endif<span class="pill" style="background:var(--gs);color:#6B4A10">{{ $vedette->duree_heures ? $vedette->duree_heures . 'h • ' : '' }}{{ $vedette->modules_count }} module{{ $vedette->modules_count > 1 ? 's' : '' }}</span><h3>{{ $vedette->titre }}</h3><p style="color:var(--grey)">{{ ucfirst($vedette->categorie) }} · Certification incluse</p><div class="price">{{ number_format($vedette->prix_effectif, 0, ',', ' ') }} FCFA <small>· certification incluse</small></div>
<a class="btn blue" href="{{ route('formations.show', $vedette) }}">Voir programme</a> <a class="btn gold" href="{{ auth()->check() ? route('apprenant.paiement.initier', $vedette) : route('register') }}">Acheter</a></div></div></div></section>
@endif

<section id="catalogue"><div class="c"><div class="sh"><small>Catalogue complet</small><h2>Toutes nos formations certifiantes</h2><p>Quatre filières, {{ $total }} formations avec certificat d'aptitude professionnelle. Choisissez votre filière ou recherchez un thème.</p></div>
<div class="tabs" id="ctabs"><button type="button" data-g="all">Toutes ({{ $total }})</button>@foreach($filieres as $gi => $g)<button type="button" data-g="{{ $gi }}">{{ $g['nom'] }} ({{ count($g['items']) }})</button>@endforeach</div>
<div class="srch"><span>🔎</span><input id="cq" type="search" placeholder="Rechercher une formation (ex. stocks, douane, Incoterms…)" aria-label="Rechercher une formation"></div>
<div class="cat" id="cgrid">
@foreach($filieres as $gi => $g)
@foreach($g['items'] as $ti => $f)
<article class="fc" style="--k:{{ $g['couleur'] }}" data-g="{{ $gi }}" data-s="{{ mb_strtolower($f['titre'] . ' ' . $f['description'] . ' ' . $g['nom']) }}"><figure class="fim"><img loading="lazy" decoding="async" alt="{{ $f['titre'] }}" src="{{ $f['image'] }}" onerror="this.parentNode.classList.add('noimg');this.remove()"><span class="fdur">{{ $f['badge'] }}</span></figure><div class="tp"><span class="tg">{{ $g['nom'] }}</span><span class="ix">{{ str_pad($ti + 1, 2, '0', STR_PAD_LEFT) }}</span></div><h4>{{ $f['titre'] }}</h4><p>{{ $f['description'] }}</p><div class="mt">{{ $f['meta'] }}</div><div class="bt"><span class="pr">{{ number_format($f['prix'], 0, ',', ' ') }} <small>FCFA</small></span><a class="btn blue" href="{{ $f['url'] }}" @if($f['externe']) target="_blank" rel="noopener" @endif>{{ $f['bouton'] }}</a></div></article>
@endforeach
@endforeach
<div class="nores" style="display:none">Aucune formation ne correspond à votre recherche.</div>
</div></div></section>

@include('public.partials.cta')
@endsection
