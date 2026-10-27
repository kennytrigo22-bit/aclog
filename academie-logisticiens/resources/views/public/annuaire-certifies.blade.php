@extends('layouts.public')

@section('title', 'Annuaire des certifiés - ' . $siteTitre)

@section('content')
<div class="pg show">
<div class="phead"><div class="c"><div class="bc"><a href="{{ route('home') }}">Accueil</a> › <a href="{{ route('a-propos') }}">À propos</a> › Annuaire des certifiés</div><h1>Annuaire des <em>certifiés</em></h1><p>Retrouvez tous les apprenants certifiés par le {{ $siteTitre }}, en ligne comme en présentiel, et vérifiez l'authenticité d'un certificat.</p></div></div>

<section class="alt" style="padding:56px 0"><div class="c"><div class="sh" style="margin-bottom:24px"><small>Vérification</small><h2>Vérifier un certificat</h2><p>Saisissez le code de vérification (ou le numéro d'enregistrement) imprimé sur le certificat.</p></div>
<form method="GET" action="{{ route('annuaire-certifies') }}" class="vf"><input type="text" name="code" value="{{ request('code') }}" placeholder="Ex : ABCD1234-EFGH5678" aria-label="Code de vérification"><button class="btn blue" type="submit">Vérifier</button></form>
@if(request('code'))
    @if($certificatRecherche)
    <div class="vres ok"><span style="font-size:2rem">✅</span><div><b style="color:#1E6B45">Certificat authentique</b><br>{{ $certificatRecherche->nom_complet }} — {{ $certificatRecherche->intitule_formation }} ({{ $certificatRecherche->issued_at?->format('d/m/Y') }}) · <a class="go" href="{{ route('certificat.verifier', $certificatRecherche->code_verification) }}">Voir le détail →</a></div></div>
    @else
    <div class="vres ko"><span style="font-size:2rem">❌</span><div><b style="color:#9B2C1F">Aucun certificat ne correspond à ce code.</b><br>Vérifiez la saisie ou contactez-nous.</div></div>
    @endif
@endif
</div></section>

@include('public.partials.annuaire', ['certifies' => $certifiesPublics, 'recherche' => true])
</div>
@include('public.partials.cta')
@endsection
