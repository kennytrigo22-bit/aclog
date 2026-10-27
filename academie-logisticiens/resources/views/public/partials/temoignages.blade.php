@php($temoignages = \App\Models\Temoignage::actifs()->get())
@if($temoignages->isNotEmpty())
<section><div class="c"><div class="sh"><small>Succès réels</small><h2>Ils témoignent de Brazzaville à Pointe-Noire</h2><p>Nos diplômés occupent des postes clés au sein des terminaux portuaires, agences en douane et multinationales.</p></div>
<div class="g3">
@foreach($temoignages as $t)
<div class="card"><p class="q">« {{ $t->citation }} »</p><div class="who">@if($t->photo_url)<img class="av" src="{{ $t->photo_url }}" alt="Photo de {{ $t->nom }}" loading="lazy" style="object-fit:cover">@else<span class="av">{{ $t->initiales }}</span>@endif<span><b>{{ $t->nom }}</b>@if($t->fonction)<small>{{ $t->fonction }}</small>@endif</span></div></div>
@endforeach
</div></div></section>
@endif
