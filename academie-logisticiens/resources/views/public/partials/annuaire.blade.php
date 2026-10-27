{{-- Annuaire des certifies : $certifies (collection ou pagination), $statsAnnuaire, $recherche (formulaire serveur au lieu de la recherche instantanee) --}}
<section class="ann" id="annuaire"><div class="c"><div class="ann-h"><small>Annuaire des certifiés</small><h2>Nos étudiants certifiés</h2></div>
<div class="ann-s"><div><b id="an-e">{{ $statsAnnuaire['etudiants'] }}</b><small>Étudiants certifiés</small></div><div><b id="an-p">{{ $statsAnnuaire['pays'] }}</b><small>Pays représentés</small></div><div><b id="an-f">{{ $statsAnnuaire['formations'] }}</b><small>Formations suivies</small></div></div>
<div class="ann-c" id="an-box">
@if($certifies->isEmpty() && !request('q'))
<div class="ann-v"><span class="ann-ic">🎓</span><h3>La liste des étudiants certifiés sera publiée ici</h3><p>Chaque étudiant certifié par le Cabinet ACLOG y apparaîtra avec son nom, ses prénoms, son pays et la formation suivie.</p></div>
@else
@if(!empty($recherche))
<form method="GET" action="{{ route('annuaire-certifies') }}#annuaire"><input class="ann-q" type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un nom, un pays, une formation…" aria-label="Rechercher un certifié"></form>
@else
<input class="ann-q" data-live type="search" placeholder="Rechercher un nom, un pays, une formation…" aria-label="Rechercher un certifié">
@endif
<div class="ann-w"><table class="ann-t"><thead><tr><th>Nom</th><th>Prénoms</th><th>Pays</th><th>Formation suivie</th><th>Année</th><th>Certificat</th></tr></thead><tbody>
@forelse($certifies as $c)
<tr data-s="{{ mb_strtolower($c->nom . ' ' . $c->prenom . ' ' . $c->pays . ' ' . $c->intitule_formation) }}">
<td><span class="ann-n">@if($c->photo_url)<img class="ann-ph" src="{{ $c->photo_url }}" alt="Photo de {{ $c->nom_complet }}" loading="lazy" width="42" height="42">@else<span class="ann-ph ini" aria-hidden="true">{{ $c->initiales }}</span>@endif<b>{{ $c->nom }}</b></span></td>
<td>{{ $c->prenom }}</td>
<td>{{ $c->pays ?: '—' }}</td>
<td>{{ $c->intitule_formation }}<br><span class="ann-md{{ $c->estPresentiel() ? ' pr' : '' }}">{{ $c->estPresentiel() ? 'Présentiel' : 'En ligne' }}</span></td>
<td>{{ $c->issued_at?->year }}</td>
<td><a class="ann-ok" href="{{ route('certificat.verifier', $c->code_verification) }}">✓ Vérifier</a></td>
</tr>
@empty
<tr><td colspan="6" style="text-align:center;color:var(--grey)">Aucun résultat</td></tr>
@endforelse
@if(empty($recherche))<tr class="ann-none" style="display:none"><td colspan="6" style="text-align:center;color:var(--grey)">Aucun résultat</td></tr>@endif
</tbody></table></div>
@if($certifies instanceof \Illuminate\Contracts\Pagination\Paginator && $certifies->hasPages())<div class="ann-pg">{{ $certifies->fragment('annuaire')->links() }}</div>@endif
@endif
</div></div></section>
