@extends('layouts.public')

@section('title', $formation->titre . ' - ' . $siteTitre)
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $formation->description), 160))

@section('content')
@php
    $nbLecons = $formation->modules->sum(fn ($m) => $m->lecons->count());
    // Le plan detaille importe depuis Word est du HTML : on l'affiche assaini, jamais echappe (sinon les balises apparaissent en clair).
    $programme = (string) $formation->programme;
    $planHtml = $programme === '' ? '' : (preg_match('/<\/?[a-z][a-z0-9]*[\s>\/]/i', $programme)
        ? \App\Support\HtmlSanitizer::clean($programme)
        : nl2br(e($programme)));
@endphp
<div class="pg show">
<div class="phead"><div class="c fx">
<div>
    <div class="bc"><a href="{{ route('home') }}">Accueil</a> › <a href="{{ route('formations.index') }}">Formations</a> › {{ $formation->titre }}</div>
    <span class="kick">{{ ucfirst($formation->categorie) }} · {{ ucfirst($formation->niveau) }}@if($formation->is_featured) · Populaire @endif</span>
    <h1 style="margin-top:14px">{{ $formation->titre }}</h1>
    <p>{{ $formation->description }}</p>
    <div class="meta-l">
        <span>⏱ {{ $formation->duree_heures ? $formation->duree_heures . ' heures' : '2 mois' }}</span>
        <span>📚 {{ $formation->modules->count() }} module{{ $formation->modules->count() > 1 ? 's' : '' }}</span>
        <span>🎬 {{ $nbLecons }} leçon{{ $nbLecons > 1 ? 's' : '' }}</span>
        @if($formation->examen)<span>📝 Examen final</span>@endif
        <span>🏅 Certificat vérifiable par QR Code</span>
    </div>
</div>
<div class="buy">
    <img src="{{ $formation->image_url }}" alt="{{ $formation->titre }}">
    <div><span class="pz">{{ number_format($formation->prix_effectif, 0, ',', ' ') }} <small>FCFA</small></span>@if($formation->promo_active && $formation->prix_promo)<span class="old">{{ number_format($formation->prix, 0, ',', ' ') }}</span>@endif</div>
    <ul><li>Accès à vie aux cours</li><li>Certificat vérifiable par QR Code</li><li>Support pédagogique</li><li>Paiement MTN / Airtel Money</li></ul>
    @auth
    <a class="btn gold" href="{{ route('apprenant.paiement.initier', $formation) }}">💳 S'inscrire à cette formation</a>
    @else
    <a class="btn gold" href="{{ route('register') }}">💳 Créer un compte pour s'inscrire</a>
    <a class="btn blue" href="{{ route('login') }}">Déjà inscrit ? Se connecter</a>
    @endauth
</div>
</div></div>

<section class="alt"><div class="c fx">
<div>
    <h2 class="ttl">Programme de la formation</h2>
    <div class="mods">
        @forelse($formation->modules as $index => $module)
        <details class="fq"@if($loop->first) open @endif><summary><span class="mt"><span class="mn">{{ $index + 1 }}</span><span>{{ $module->titre }}<small>{{ $module->lecons->count() }} leçon{{ $module->lecons->count() > 1 ? 's' : '' }}</small></span></span><span class="pl" aria-hidden="true"></span></summary>
            <div class="fa">
                @if($module->description)<p style="border:0;padding:0 0 10px">{{ $module->description }}</p>@endif
                <ul>
                    @foreach($module->lecons as $lecon)
                    <li><span>{{ $lecon->titre }}</span>@if($lecon->duree_minutes)<small>{{ $lecon->duree_minutes }} min</small>@endif</li>
                    @endforeach
                </ul>
            </div>
        </details>
        @empty
        <p style="color:var(--grey)">Le programme détaillé sera bientôt publié.</p>
        @endforelse
    </div>

    @if($planHtml !== '')
    <h2 class="ttl" style="margin-top:44px">Plan de la formation</h2>
    <div class="plan contenu-cours">{!! $planHtml !!}</div>
    @endif
</div>

<aside class="fx-side">
    <div class="card info"><div class="ico">ℹ️</div><h3>Informations</h3>
        <dl>
            <div><dt>Catégorie</dt><dd>{{ $formation->categorie }}</dd></div>
            <div><dt>Niveau</dt><dd>{{ $formation->niveau }}</dd></div>
            <div><dt>Durée</dt><dd>{{ $formation->duree_heures ? $formation->duree_heures . ' h' : '2 mois' }}</dd></div>
            <div><dt>Modules</dt><dd>{{ $formation->modules->count() }}</dd></div>
            <div><dt>Leçons</dt><dd>{{ $nbLecons }}</dd></div>
            @if($formation->examen)<div><dt>Examen final</dt><dd>Oui · {{ $formation->examen->nb_questions }} questions</dd></div>@endif
            <div><dt>Certificat</dt><dd>PDF + QR Code</dd></div>
        </dl>
    </div>
    <div class="card"><div class="ico">📲</div><h3>Paiement sécurisé</h3><p>Réglez par <strong>MTN Mobile Money</strong> ou <strong>Airtel Money</strong>. Une question ? <a class="go" href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('Bonjour CABINET ACLOG, une question sur la formation « ' . $formation->titre . ' ».') }}" target="_blank" rel="noopener">Écrivez-nous sur WhatsApp →</a></p></div>
</aside>
</div></section>
</div>

@include('public.partials.cta')
@endsection
