<footer><div class="c"><div class="fg">
<div><div class="fl"><img src="{{ asset('assets/site/logo-aclog.png') }}" alt="Logo {{ $siteTitre }}"></div><br><b style="color:#fff;letter-spacing:.06em">{{ $siteTitre }}</b><p style="margin:6px 0 0">{{ $siteSlogan }}<br>Le 1er cabinet académique digital de référence en Afrique Centrale.</p>
<div class="soc"><a class="wa" href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="WhatsApp"><svg><use href="#i-wa"/></svg></a><a class="yt" href="{{ $lienYoutube }}" @if($lienYoutube !== '#') target="_blank" rel="noopener" @endif aria-label="YouTube"><svg><use href="#i-yt"/></svg></a><a class="fb" href="{{ $lienFacebook }}" @if($lienFacebook !== '#') target="_blank" rel="noopener" @endif aria-label="Facebook"><svg><use href="#i-fb"/></svg></a></div></div>
<div class="lg"><h4>Informations légales</h4><ul>
@foreach(collect($identifiantsLegaux)->filter() as $libelle => $valeur)
<li><b>{{ $libelle }}</b> : {{ $valeur }}</li>
@endforeach
</ul></div><div><h4>Liens rapides</h4><ul><li><a href="{{ route('home') }}">Accueil</a></li><li><a href="{{ route('a-propos') }}">À Propos</a></li><li><a href="{{ route('formations.index') }}">Formations</a></li><li><a href="{{ route('services') }}">Nos Services</a></li><li><a href="{{ route('contact') }}">Contact</a></li></ul></div>
<div><h4>Nos domaines</h4><ul><li>Logistique &amp; Supply Chain</li><li>Commerce International</li><li>Douane &amp; Transit CEMAC</li><li>Transport &amp; Distribution</li></ul></div>

<div><h4>Contact</h4><ul><li>4 Rue Abila, Talangai<br>Brazzaville, Congo</li><li>{{ $telContact }}</li><li>{{ $emailContact }}</li></ul></div></div>
<div class="legal">© {{ date('Y') }} {{ $siteTitre }}. Tous droits réservés.<br><a href="{{ route('cgv') }}">CGV</a><a href="{{ route('confidentialite') }}">Confidentialité</a><a href="{{ route('mentions-legales') }}">Mentions légales</a></div></div></footer>
