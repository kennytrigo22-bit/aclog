@extends('layouts.public')

@section('title', 'Vérification du Certificat - ' . $siteTitre)

@section('content')
<div class="pg show">
<div class="phead"><div class="c"><div class="bc"><a href="{{ route('home') }}">Accueil</a> › Vérification de certificat</div><h1>Vérification du <em>certificat</em></h1><p>Authenticité des certificats d'aptitude professionnelle délivrés par le {{ $siteTitre }}.</p></div></div>
<section class="alt"><div class="c">
@if($certificat)
<div class="dg verif">
    <figure class="ph" style="width:150px;height:186px">@if($certificat->photo_url)<img src="{{ $certificat->photo_url }}" alt="Photo de {{ $certificat->nom_complet }}">@else<span class="ini">{{ $certificat->initiales }}</span>@endif</figure>
    <div>
        <span class="ann-ok verif-ok">✅ Certificat Authentique</span>
        <h3 style="margin-top:6px">{{ $certificat->nom_complet }}</h3>
        <small>Ce certificat est valide et vérifié.</small>
        <div class="info verif-info"><dl>
            <div><dt>Formation</dt><dd style="text-transform:none">{{ $certificat->intitule_formation }}</dd></div>
            <div><dt>Modalité</dt><dd>{{ $certificat->estPresentiel() ? 'En présentiel' : 'En ligne' }}</dd></div>
            @if($certificat->pays)<div><dt>Pays</dt><dd>{{ $certificat->pays }}</dd></div>@endif
            @unless($certificat->estPresentiel())<div><dt>Score</dt><dd>{{ $certificat->score_obtenu }}%</dd></div>@endunless
            <div><dt>Délivré le</dt><dd>{{ $certificat->issued_at?->format('d/m/Y') }}</dd></div>
            <div><dt>N° d'enregistrement</dt><dd style="text-transform:none">{{ $certificat->numero_affiche }}</dd></div>
            <div><dt>Code</dt><dd style="text-transform:none;font-family:monospace">{{ $certificat->code_verification }}</dd></div>
        </dl></div>
    </div>
</div>
@else
<div class="dg" style="grid-template-columns:1fr;text-align:center;border-left-color:#C0392B">
    <div><span style="font-size:3rem">❌</span><h3>Certificat non trouvé</h3><p>Le code <span style="font-family:monospace;font-weight:700">{{ $code }}</span> ne correspond à aucun certificat dans notre base de données.</p><p style="margin-top:14px"><a class="btn blue" href="{{ route('annuaire-certifies') }}">Voir l'annuaire des certifiés</a></p></div>
</div>
@endif
</div></section>
</div>
@endsection
