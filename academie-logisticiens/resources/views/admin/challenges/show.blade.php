@extends('layouts.admin')
@section('page-title', $parcours->titre)

@section('content')
@php $min = \App\Models\Challenge::MIN_QUESTIONS; @endphp
<div class="max-w-4xl space-y-6">
    <a href="{{ route('admin.challenges.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-primary-700 dark:hover:text-accent-400 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Retour aux parcours
    </a>

    <div class="bg-gradient-to-r from-primary-700 to-primary-800 text-white rounded-xl p-6">
        <h2 class="font-heading text-xl font-bold">{{ $parcours->titre }}</h2>
        <div class="flex flex-wrap items-center gap-3 mt-1 text-sm text-white/70">
            <span class="capitalize">{{ str_replace('_', ' ', $parcours->domaine) }}</span><span>&bull;</span>
            <span class="capitalize">{{ $parcours->niveau }}</span><span>&bull;</span>
            <span>{{ $parcours->challenges->count() }} etape(s)</span><span>&bull;</span>
            <span>{{ $parcours->challenges->sum('points') }} points</span>
        </div>
        <p class="text-xs text-white/60 mt-3">Chaque etape = un theme + un drapeau. Une etape doit avoir au moins {{ $min }} questions pour etre jouable. L'apprenant capture le drapeau avec 70% de bonnes reponses.</p>
    </div>

    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm" x-data="{ open: {{ $parcours->challenges->isEmpty() ? 'true' : 'false' }} }">
        <button @click="open = !open" class="flex items-center justify-between w-full">
            <h2 class="font-heading font-bold text-primary-700 dark:text-white">Ajouter une etape</h2>
            <svg class="w-5 h-5 text-gray-400 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <form method="POST" action="{{ route('admin.challenges.store', $parcours) }}" class="mt-5 space-y-4" x-show="open" x-collapse>
            @csrf
            @include('admin.challenges._etape-fields', ['challenge' => null])
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition">Creer l'etape et importer ses questions</button>
        </form>
    </div>

    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <h2 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-5">Etapes ({{ $parcours->challenges->count() }})</h2>

        @if($parcours->challenges->isEmpty())
        <p class="text-center py-8 text-gray-400">Aucune etape. Ajoutez la premiere ci-dessus.</p>
        @else
        <div class="space-y-4">
            @foreach($parcours->challenges as $challenge)
            @php $ok = $challenge->questions_count >= $min; @endphp
            <div class="border dark:border-gray-800 rounded-xl p-4 {{ $challenge->is_active ? '' : 'opacity-60' }}" x-data="{ editing: false }">
                <div class="flex items-start justify-between gap-3" x-show="!editing">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-accent-500/20 text-accent-600 flex items-center justify-center text-xs font-bold">{{ $loop->iteration }}</span>
                            <h4 class="font-bold text-primary-700 dark:text-white">{{ $challenge->titre }}</h4>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 text-xs font-bold">{{ $challenge->points }} pts</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $ok ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600' : 'bg-red-100 dark:bg-red-900/30 text-red-600' }}">{{ $challenge->questions_count }} question(s){{ $ok ? '' : ' - minimum ' . $min }}</span>
                            @if(!$challenge->is_active)<span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 text-xs font-bold">Inactif</span>@endif
                        </div>
                        @if($challenge->theme)<p class="text-xs text-gray-500 mt-1">Theme : {{ $challenge->theme }}</p>@endif
                        @if($challenge->description)<p class="text-sm text-gray-500 mt-1">{{ Str::limit($challenge->description, 140) }}</p>@endif
                        <p class="text-xs text-gray-400 mt-2">Drapeau : <span class="font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">{{ $challenge->flag_attendu }}</span></p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-1 shrink-0">
                        <a href="{{ route('admin.qcm-import.index', ['cible' => 'etape:' . $challenge->id]) }}" class="px-3 py-1.5 rounded-lg bg-violet-600 text-white text-xs font-bold hover:bg-violet-500 transition">Importer des questions</a>
                        <button @click="editing = true" class="p-2 rounded-lg text-accent-500 hover:bg-accent-500/10 transition" title="Modifier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.challenges.challenge.destroy', $challenge) }}" onsubmit="return confirm('Supprimer cette etape et ses questions ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.challenges.challenge.update', $challenge) }}" x-show="editing" x-cloak class="space-y-3">
                    @csrf @method('PUT')
                    @include('admin.challenges._etape-fields', ['challenge' => $challenge])
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent-500 text-primary-700 font-bold text-sm hover:bg-accent-400 transition">Sauvegarder</button>
                        <button type="button" @click="editing = false" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-sm transition">Annuler</button>
                    </div>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    @if($captures->isNotEmpty())
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <h2 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-5">Drapeaux captures recemment</h2>
        <div class="space-y-2">
            @foreach($captures as $c)
            <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/10 text-sm">
                <span><strong class="text-emerald-700 dark:text-emerald-400">{{ $c->user->prenom ?? '' }} {{ $c->user->name }}</strong> <span class="text-xs text-gray-400 ml-2">{{ $c->challenge->titre }}</span></span>
                <span class="text-xs text-gray-400">{{ $c->captured_at?->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
