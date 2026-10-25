@extends('layouts.apprenant')
@section('page-title', 'Mes Formations')

@section('content')
@if($inscriptions->isEmpty())
<div class="bg-white dark:bg-surface-dark rounded-2xl p-12 text-center shadow-sm">
    <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
    </div>
    <h3 class="font-heading text-lg font-bold text-primary-700 dark:text-white mb-2">Aucune formation en cours</h3>
    <p class="text-sm text-gray-500 mb-4">Commencez votre parcours d'apprentissage des aujourd'hui.</p>
    <a href="{{ route('formations.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        Parcourir le catalogue
    </a>
</div>
@else

@php
    $progressService = app(\App\Services\FormationProgressService::class);
    $ctx = $progressService->contexte(auth()->user());
    $done = $ctx['done'];
    $totalFormations = $inscriptions->count();
    $totalLeconsAll = 0; $totalCompletedAll = 0;
    foreach ($inscriptions as $ins) {
        $ids = $ins->formation->modules->flatMap->lecons->pluck('id');
        $totalLeconsAll += $ids->count();
        $totalCompletedAll += $ids->filter(fn ($i) => isset($done[$i]))->count();
    }
    $globalPct = $totalLeconsAll > 0 ? round(($totalCompletedAll / $totalLeconsAll) * 100) : 0;
@endphp

<div class="grid grid-cols-3 gap-3 mb-6">
    <div class="bg-white dark:bg-surface-dark rounded-xl p-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-primary-700 dark:text-white">{{ $totalFormations }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Formation(s)</p>
    </div>
    <div class="bg-white dark:bg-surface-dark rounded-xl p-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $totalCompletedAll }}<span class="text-sm text-gray-400">/{{ $totalLeconsAll }}</span></p>
        <p class="text-xs text-gray-500 mt-0.5">Lecons</p>
    </div>
    <div class="bg-white dark:bg-surface-dark rounded-xl p-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-accent-600">{{ $globalPct }}%</p>
        <p class="text-xs text-gray-500 mt-0.5">Progression</p>
    </div>
</div>

<div class="space-y-6">
    @foreach($inscriptions as $inscription)
    @php
        $formation = $inscription->formation;
        $totalLecons = $formation->modules->flatMap->lecons->count();
        $leconIds = $formation->modules->flatMap->lecons->pluck('id');
        $completees = $leconIds->filter(fn ($i) => isset($done[$i]))->count();
        $pct = $totalLecons > 0 ? round(($completees / $totalLecons) * 100) : 0;
        $nextLecon = $formation->modules->flatMap->lecons->first(fn ($l) => !isset($done[$l->id]));
        $st = $progressService->status(auth()->user(), $formation, $ctx);
        $coursTermine = $st['unlocked'];
        $lockReason = $st['lock_reason'];
        $passedQuizIds = $st['passed_quiz_ids'];
    @endphp
    <div class="bg-white dark:bg-surface-dark rounded-2xl shadow-sm overflow-hidden" x-data="{ expanded: false }">
        {{-- Card header avec image --}}
        <div class="relative">
            <div class="h-32 bg-cover bg-center" style="background-image: url('{{ $formation->image_url }}')">
                <div class="absolute inset-0 bg-gradient-to-t from-primary-900/90 via-primary-900/50 to-transparent"></div>
            </div>

            <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-accent-500/90 text-primary-900">{{ $formation->categorie }}</span>
                            @if($pct === 100)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/90 text-white">Termine</span>
                            @endif
                        </div>
                        <h3 class="font-heading font-bold text-base leading-tight">{{ $formation->titre }}</h3>
                    </div>
                    <div class="relative w-14 h-14 ml-3 shrink-0">
                        <svg class="w-14 h-14 -rotate-90" viewBox="0 0 36 36">
                            <path class="text-white/20" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845a 15.9155 15.9155 0 0 1 0 31.831 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path class="{{ $pct === 100 ? 'text-emerald-400' : 'text-accent-400' }}" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="{{ $pct }}, 100" d="M18 2.0845a 15.9155 15.9155 0 0 1 0 31.831 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white">{{ $pct }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barre de progression --}}
        <div class="h-1 bg-gray-100 dark:bg-gray-800">
            <div class="h-full transition-all duration-500 {{ $pct === 100 ? 'bg-emerald-500' : 'bg-accent-500' }}" style="width: {{ $pct }}%"></div>
        </div>

        {{-- Info rapide + CTA --}}
        <div class="p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        {{ $formation->modules->count() }} modules
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        {{ $completees }}/{{ $totalLecons }} lecons
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $formation->duree_heures }}h
                    </span>
                </div>
            </div>

            <div class="flex gap-2">
                @if($nextLecon)
                <a href="{{ route('apprenant.cours.lecon', [$inscription, $nextLecon]) }}" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-accent-500 text-primary-700 font-bold text-sm hover:bg-accent-400 transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                    {{ $pct > 0 ? 'Continuer' : 'Commencer' }}
                </a>
                @else
                <span class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold text-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Cours termine
                </span>
                @endif
                <button @click="expanded = !expanded" class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
        </div>

        {{-- Contenu detaille (accordeon) --}}
        <div x-show="expanded" x-transition x-cloak>
            {{-- Modules --}}
            <div class="border-t dark:border-gray-800" x-data="{ openModule: -1 }">
                @foreach($formation->modules->sortBy('ordre') as $mi => $module)
                @php
                    $moduleLeconIds = $module->lecons->pluck('id');
                    $moduleDone = $moduleLeconIds->filter(fn ($i) => isset($done[$i]))->count();
                    $moduleTotal = $module->lecons->count();
                    $modulePct = $moduleTotal > 0 ? round(($moduleDone / $moduleTotal) * 100) : 0;
                @endphp
                <div class="border-b dark:border-gray-800 last:border-b-0">
                    <button @click="openModule = openModule === {{ $mi }} ? -1 : {{ $mi }}" class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            @if($modulePct === 100)
                            <div class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </div>
                            @elseif($moduleDone > 0)
                            <div class="w-7 h-7 rounded-full border-2 border-accent-500 flex items-center justify-center shrink-0">
                                <span class="text-[10px] font-bold text-accent-500">{{ $modulePct }}%</span>
                            </div>
                            @else
                            <div class="w-7 h-7 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center shrink-0">
                                <span class="text-[10px] font-bold text-gray-400">{{ $mi + 1 }}</span>
                            </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 truncate">{{ $module->titre }}</p>
                                <p class="text-[11px] text-gray-400">{{ $moduleTotal }} lecons &middot; {{ $moduleDone }} terminees</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform shrink-0 ml-2" :class="{ 'rotate-180': openModule === {{ $mi }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openModule === {{ $mi }}" x-transition x-cloak class="px-4 pb-3 space-y-0.5">
                        @foreach($module->lecons as $lecon)
                        @php $prog = isset($done[$lecon->id]); @endphp
                        <a href="{{ route('apprenant.cours.lecon', [$inscription, $lecon]) }}" class="flex items-center gap-3 py-2 px-3 rounded-lg text-sm transition {{ $prog ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/10' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                            @if($prog)
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @else
                            <div class="w-4 h-4 shrink-0 rounded-full border-2 border-gray-300 dark:border-gray-600"></div>
                            @endif
                            <span class="flex-1 truncate">{{ $lecon->titre }}</span>
                            @if($lecon->duree_minutes)<span class="text-[11px] text-gray-400 shrink-0">{{ $lecon->duree_minutes }} min</span>@endif
                        </a>
                        @endforeach
                        @if($module->quiz)
                        @php $quizOk = in_array($module->quiz->id, $passedQuizIds); @endphp
                        <a href="{{ route('apprenant.quiz', $module->quiz) }}" class="flex items-center gap-3 py-2 px-3 rounded-lg text-sm font-semibold transition {{ $quizOk ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/10' : 'text-violet-600 dark:text-violet-400 hover:bg-violet-50 dark:hover:bg-violet-900/10' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Quiz du module
                            <span class="ml-auto text-[11px] font-bold">{{ $quizOk ? 'Valide' : 'A valider (min. ' . app(\App\Services\FormationProgressService::class)->seuilQuiz() . '%)' }}</span>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Ressources --}}
            @if($formation->ressources->isNotEmpty())
            <div class="border-t dark:border-gray-800 p-4">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Ressources</h4>
                <div class="space-y-2">
                    @foreach($formation->ressources as $ressource)
                    <a href="{{ route('apprenant.ressources.show', $ressource) }}" class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/20 transition">
                        <div class="w-10 h-10 rounded-lg bg-accent-500 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-primary-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-amber-800 dark:text-amber-300">{{ $ressource->titre }}</p>
                            <p class="text-[11px] text-amber-600/80">Lire en ligne ou télécharger</p>
                        </div>
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Examens --}}
            @if($formation->examenTest || $formation->examenFinal)
            <div class="border-t dark:border-gray-800 p-4">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Examens</h4>
                <div class="space-y-2">
                    @if($formation->examenTest)
                    @if($coursTermine)
                    <a href="{{ route('apprenant.examen', $formation->examenTest) }}" class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition">
                        <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-blue-700 dark:text-blue-300">Examen Test (Blanc)</p>
                            <p class="text-[11px] text-blue-500/70">Entrainement illimite - {{ $formation->examenTest->nb_questions }} questions</p>
                        </div>
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @else
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 opacity-60">
                        <div class="w-10 h-10 rounded-lg bg-gray-300 dark:bg-gray-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-gray-500">Examen Test (Blanc)</p>
                            <p class="text-[11px] text-gray-400">{{ $lockReason }}</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    @endif

                    @endif
                    @if($formation->examenFinal)
                    @if($coursTermine)
                    <a href="{{ route('apprenant.examen', $formation->examenFinal) }}" class="flex items-center gap-3 p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 hover:bg-primary-100 dark:hover:bg-primary-900/30 transition">
                        <div class="w-10 h-10 rounded-lg bg-primary-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-primary-700 dark:text-primary-300">Examen Final Certifiant</p>
                            <p class="text-[11px] text-primary-500/70">{{ $formation->examenFinal->nb_questions }} questions - Certificat a la cle</p>
                        </div>
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @else
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 opacity-60">
                        <div class="w-10 h-10 rounded-lg bg-gray-300 dark:bg-gray-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-gray-500">Examen Final Certifiant</p>
                            <p class="text-[11px] text-gray-400">{{ $lockReason }}</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
