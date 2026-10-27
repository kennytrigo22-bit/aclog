@php
    $navLinks = [
        ['route' => 'home', 'label' => 'Accueil', 'actif' => ['home']],
        ['route' => 'a-propos', 'label' => 'À Propos', 'actif' => ['a-propos', 'annuaire-certifies']],
        ['route' => 'formations.index', 'label' => 'Formations', 'actif' => ['formations.*']],
        ['route' => 'services', 'label' => 'Nos Services', 'actif' => ['services']],
        ['route' => 'contact', 'label' => 'Contact', 'actif' => ['contact']],
    ];
@endphp
<div class="top"><div class="c"><div class="l"><a href="tel:+{{ preg_replace('/\D+/', '', $telContact) }}">📞 {{ $telContact }}</a><a href="mailto:{{ $emailContact }}">✉️ {{ $emailContact }}</a></div><div class="r"><span class="msg" data-nb="{{ \App\Models\Formation::where('is_published', true)->count() }}">Session {{ date('Y') }} ouverte — Gagnez des jetons FCFA sur vos formations !</span><div class="tsoc"><a class="wa" href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="WhatsApp"><svg><use href="#i-wa"/></svg></a><a class="yt" href="{{ $lienYoutube }}" @if($lienYoutube !== '#') target="_blank" rel="noopener" @endif aria-label="YouTube"><svg><use href="#i-yt"/></svg></a><a class="fb" href="{{ $lienFacebook }}" @if($lienFacebook !== '#') target="_blank" rel="noopener" @endif aria-label="Facebook"><svg><use href="#i-fb"/></svg></a></div></div></div></div>
<header class="site"><div class="c"><a class="brand" href="{{ route('home') }}"><img src="{{ asset('assets/site/logo-aclog.png') }}" alt="Logo {{ $siteTitre }}"><span><b>{{ $siteTitre }}</b><small>{{ $siteSlogan }}</small></span></a>
<nav aria-label="Navigation principale">
@foreach($navLinks as $link)
@php $on = request()->routeIs(...$link['actif']); @endphp
<a class="tab{{ $on ? ' on' : '' }}" href="{{ route($link['route']) }}" @if($on) aria-current="page" @endif>{{ $link['label'] }}</a>
@endforeach
</nav>
<div class="rt">
@auth
<a class="btn blue" href="{{ auth()->user()->isGestionnaire() ? route('admin.dashboard') : route('apprenant.dashboard') }}" style="padding:10px 20px">Mon espace</a>
@else
<a class="btn blue" href="{{ route('login') }}" style="padding:10px 20px">Connexion</a>
@endauth
</div></div></header>
