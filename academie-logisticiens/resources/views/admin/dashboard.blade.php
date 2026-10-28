@extends('layouts.admin')
@section('page-title', 'Dashboard')

@php
    $carte = 'bg-white dark:bg-surface-dark rounded-xl shadow-sm';
    $titre = 'font-heading font-bold text-primary-700 dark:text-white';
    $kpis = [
        ['cle' => 'visiteurs', 'libelle' => 'Visiteurs uniques', 'format' => 'nombre', 'delta' => true],
        ['cle' => 'pages_vues', 'libelle' => 'Pages vues', 'format' => 'nombre', 'delta' => true],
        ['cle' => 'nouveaux_comptes', 'libelle' => 'Nouveaux comptes', 'format' => 'nombre', 'delta' => true],
        ['cle' => 'ventes', 'libelle' => 'Ventes validées', 'format' => 'nombre', 'delta' => true],
        ['cle' => 'chiffre_affaires', 'libelle' => "Chiffre d'affaires", 'format' => 'fcfa', 'delta' => true],
        ['cle' => 'conversion', 'libelle' => 'Taux de conversion', 'format' => 'pourcent', 'aide' => 'ventes ÷ visiteurs uniques'],
        ['cle' => 'panier_moyen', 'libelle' => 'Panier moyen', 'format' => 'fcfa'],
        ['cle' => 'certificats', 'libelle' => 'Certificats délivrés', 'format' => 'nombre'],
    ];
    $etat = [
        ['cle' => 'comptes_total', 'libelle' => 'Comptes apprenants', 'lien' => 'admin.utilisateurs.index'],
        ['cle' => 'inscriptions_actives', 'libelle' => 'Inscriptions actives', 'lien' => null],
        ['cle' => 'paiements_attente', 'libelle' => 'Paiements en attente', 'lien' => 'admin.paiements.index'],
        ['cle' => 'tickets_ouverts', 'libelle' => 'Tickets ouverts', 'lien' => 'admin.tickets.index'],
    ];
    $graphiques = [
        ['id' => 'g-audience', 'titre' => 'Audience', 'sous' => 'Visiteurs uniques et pages vues', 'large' => true],
        ['id' => 'g-ca', 'titre' => "Chiffre d'affaires", 'sous' => 'Paiements validés, en FCFA'],
        ['id' => 'g-comptes', 'titre' => 'Nouveaux comptes', 'sous' => 'Inscriptions sur la plateforme'],
        ['id' => 'g-vendues', 'titre' => 'Formations les plus vendues', 'sous' => 'Nombre de ventes validées'],
        ['id' => 'g-consultees', 'titre' => 'Formations les plus consultées', 'sous' => 'Pages vues de la fiche formation'],
        ['id' => 'g-pays', 'titre' => 'Pays des visiteurs', 'sous' => 'Visiteurs uniques'],
        ['id' => 'g-appareils', 'titre' => 'Appareils', 'sous' => 'Visiteurs uniques'],
        ['id' => 'g-sources', 'titre' => "Provenance", 'sous' => 'Site d\'où arrivent les visiteurs'],
        ['id' => 'g-pages', 'titre' => 'Pages les plus vues', 'sous' => 'Pages vues'],
    ];
@endphp

@section('content')
<div id="tableau-stats" class="space-y-6"
     data-url-stats="{{ route('admin.dashboard.statistiques') }}"
     data-url-en-ligne="{{ route('admin.dashboard.en-ligne') }}">

    {{-- Filtres : une seule ligne au-dessus des graphiques --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex flex-wrap rounded-lg bg-white dark:bg-surface-dark shadow-sm p-1 text-sm font-semibold" role="group" aria-label="Période">
            @foreach($periodes as $cle => $libelle)
            <button type="button" data-periode="{{ $cle }}" aria-pressed="{{ $stats['periode'] === $cle ? 'true' : 'false' }}"
                    class="px-3 py-1.5 rounded-md transition text-gray-500 hover:text-primary-700 dark:hover:text-white aria-pressed:bg-primary-700 aria-pressed:text-white">
                {{ ['jour' => 'Jour', 'semaine' => 'Semaine', 'mois' => 'Mois', 'annee' => 'Année'][$cle] }}
            </button>
            @endforeach
        </div>
        <label class="flex items-center gap-2 text-sm">
            <span class="text-gray-500">Pays</span>
            <select id="filtre-pays" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-dark text-sm focus:ring-accent-500 focus:border-accent-500">
                <option value="">Tous les pays</option>
                @foreach($stats['pays_disponibles'] as $p)
                <option value="{{ $p['code'] }}" @selected($stats['pays'] === $p['code'])>{{ $p['nom'] }}</option>
                @endforeach
            </select>
        </label>
        <p class="text-xs text-gray-400 sm:ml-auto" aria-live="polite">
            <span id="periode-libelle">{{ $stats['periode_libelle'] }}</span> · mis à jour à <span id="maj">{{ $stats['mis_a_jour'] }}</span>
            <span id="chargement" class="hidden">· actualisation…</span>
        </p>
    </div>

    {{-- En ligne maintenant + etat de la plateforme --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="{{ $carte }} p-5">
            <div class="flex items-center gap-2 text-sm font-semibold text-gray-500">
                <span class="relative flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span></span>
                En ligne maintenant <span class="text-xs font-normal text-gray-400">(actualisé toutes les 15 s)</span>
            </div>
            <p class="mt-2 flex items-baseline gap-3">
                <span id="en-ligne-total" class="font-heading text-5xl font-bold text-primary-700 dark:text-white tabular-nums">{{ $stats['en_ligne']['total'] }}</span>
                <span class="text-sm text-gray-500">dont <strong id="en-ligne-connectes" class="text-primary-700 dark:text-white tabular-nums">{{ $stats['en_ligne']['connectes'] }}</strong> connecté(s)</span>
            </p>
            <ul id="en-ligne-pages" class="mt-3 space-y-1 text-xs text-gray-500">
                @foreach($stats['en_ligne']['pages'] as $p)
                <li class="flex justify-between gap-2"><span class="truncate">{{ $p['chemin'] }}</span><span class="tabular-nums">{{ $p['n'] }}</span></li>
                @endforeach
            </ul>
        </div>
        <div class="lg:col-span-2 grid grid-cols-2 gap-4">
            @foreach($etat as $e)
            @php $lienOk = $e['lien'] && Route::has($e['lien']) && ($e['lien'] !== 'admin.utilisateurs.index' || auth()->user()->role === 'admin'); @endphp
            <a @if($lienOk) href="{{ route($e['lien']) }}" @endif class="{{ $carte }} p-5 block {{ $lienOk ? 'hover:ring-2 hover:ring-accent-500/40 transition' : '' }}">
                <p class="text-xs font-semibold text-gray-500">{{ $e['libelle'] }}</p>
                <p class="font-heading text-3xl font-bold text-primary-700 dark:text-white mt-1 tabular-nums" data-kpi="{{ $e['cle'] }}" data-format="nombre">{{ number_format($stats['kpis'][$e['cle']]['valeur'], 0, ',', ' ') }}</p>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Indicateurs de la periode, compares a la periode precedente --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($kpis as $k)
        <div class="{{ $carte }} p-4">
            <p class="text-xs font-semibold text-gray-500">{{ $k['libelle'] }}</p>
            <p class="font-heading text-2xl font-bold text-primary-700 dark:text-white mt-1 tabular-nums" data-kpi="{{ $k['cle'] }}" data-format="{{ $k['format'] }}">—</p>
            @if(!empty($k['delta']))
            <p class="text-xs mt-1" data-delta="{{ $k['cle'] }}">&nbsp;</p>
            @elseif(!empty($k['aide']))
            <p class="text-xs text-gray-400 mt-1">{{ $k['aide'] }}</p>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Graphiques --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach($graphiques as $g)
        <figure class="{{ $carte }} p-5 m-0 {{ !empty($g['large']) ? 'lg:col-span-2' : '' }}">
            <figcaption>
                <h2 class="{{ $titre }}">{{ $g['titre'] }}</h2>
                <p class="text-xs text-gray-400">{{ $g['sous'] }}</p>
            </figcaption>
            <div class="relative mt-4 {{ !empty($g['large']) ? 'h-72' : 'h-64' }}">
                <canvas id="{{ $g['id'] }}" role="img" aria-label="{{ $g['titre'] }}"></canvas>
                <p class="vide hidden absolute inset-0 flex items-center justify-center text-sm text-gray-400">Aucune donnée sur cette période.</p>
            </div>
            <details class="mt-3 text-xs text-gray-500">
                <summary class="cursor-pointer select-none font-semibold">Voir les données en tableau</summary>
                <div class="overflow-x-auto mt-2"><table class="w-full" data-table="{{ $g['id'] }}"></table></div>
            </details>
        </figure>
        @endforeach
    </div>

    <p class="text-xs text-gray-400">Mesure d'audience interne : un visiteur = un navigateur. Les pays proviennent de Cloudflare quand le site passe par Cloudflare, sinon du profil des apprenants connectés. Les pages de l'administration et les visites de l'équipe ne sont pas comptées.</p>

    <script type="application/json" id="stats-initiales">@json($stats)</script>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-8">
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm">
        <div class="p-5 border-b dark:border-gray-800 flex items-center justify-between">
            <h2 class="font-heading font-bold text-primary-700 dark:text-white">Paiements en attente</h2>
            @if(Route::has('admin.paiements.index'))
            <a href="{{ route('admin.paiements.index') }}" class="text-xs text-accent-500 hover:underline font-semibold">Voir tout</a>
            @endif
        </div>
        @if($paiementsRecents->isEmpty())
        <div class="p-5 text-center text-gray-400 text-sm">Aucun paiement en attente</div>
        @else
        <div class="divide-y dark:divide-gray-800">
            @foreach($paiementsRecents as $paiement)
            <div class="p-4 flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold truncate">{{ $paiement->user->nom_complet ?? $paiement->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $paiement->formation->titre ?? '-' }} &bull; {{ $paiement->created_at->diffForHumans() }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-bold text-primary-700 dark:text-white">{{ number_format($paiement->montant, 0, ',', ' ') }} F</p>
                    <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-[10px] font-bold">En attente</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Tickets récents --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm">
        <div class="p-5 border-b dark:border-gray-800 flex items-center justify-between">
            <h2 class="font-heading font-bold text-primary-700 dark:text-white">Tickets récents</h2>
            @if(Route::has('admin.tickets.index'))
            <a href="{{ route('admin.tickets.index') }}" class="text-xs text-accent-500 hover:underline font-semibold">Voir tout</a>
            @endif
        </div>
        @if($ticketsRecents->isEmpty())
        <div class="p-5 text-center text-gray-400 text-sm">Aucun ticket ouvert</div>
        @else
        <div class="divide-y dark:divide-gray-800">
            @foreach($ticketsRecents as $ticket)
            <div class="p-4">
                <div class="flex items-center gap-2 mb-1">
                    @php
                        $sColors = ['ouvert' => 'bg-blue-100 text-blue-700', 'en_cours' => 'bg-yellow-100 text-yellow-700', 'resolu' => 'bg-emerald-100 text-emerald-700'];
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sColors[$ticket->statut] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $ticket->statut)) }}</span>
                    <span class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm font-semibold text-primary-700 dark:text-white">{{ $ticket->sujet }}</p>
                <p class="text-xs text-gray-500">{{ $ticket->user->nom_complet ?? $ticket->user->name }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/admin-stats.js')
@endpush
