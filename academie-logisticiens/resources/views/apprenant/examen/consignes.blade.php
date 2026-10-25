@extends('layouts.apprenant')
@section('page-title', $examen->titre)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('apprenant.cours.index') }}" class="hover:text-primary-700 dark:hover:text-accent-400 transition">Mes Formations</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span>{{ $examen->formation->titre }}</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary-700 dark:text-accent-400">{{ $examen->titre }}</span>
    </div>

    <div class="bg-white dark:bg-surface-dark rounded-2xl shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="p-6 {{ $examen->type === 'test' ? 'bg-gradient-to-r from-blue-600 to-blue-700' : 'bg-gradient-to-r from-primary-700 to-primary-800' }} text-white">
            <div class="flex items-center gap-3 mb-3">
                @if($examen->type === 'test')
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                @else
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                @endif
                <div>
                    <h1 class="font-heading text-xl font-bold">{{ $examen->titre }}</h1>
                    <p class="text-sm text-white/70">{{ $examen->formation->titre }}</p>
                </div>
            </div>
        </div>

        {{-- Consignes --}}
        <div class="p-6">
            <h2 class="font-heading text-lg font-bold text-primary-700 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                Consignes avant de commencer
            </h2>

            <div class="space-y-3 mb-6">
                <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-700 dark:text-gray-300">Duree : {{ $examen->duree_minutes }} minutes</p>
                        <p class="text-xs text-gray-500">Le chronometre demarre des que vous cliquez sur "Commencer". A la fin du temps, l'examen est soumis automatiquement.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                    <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ min($examen->nb_questions, $nbQuestionsPool) }} questions (tirees au hasard dans une banque de {{ $nbQuestionsPool }})</p>
                        <p class="text-xs text-gray-500">Les questions sont selectionnees aleatoirement. Chaque tentative aura des questions differentes.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-700 dark:text-gray-300">Score minimum : {{ $examen->score_minimum }}%</p>
                        <p class="text-xs text-gray-500">Vous devez obtenir au moins {{ $examen->score_minimum }}% pour reussir.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        @if($examen->type === 'test')
                        <p class="text-sm font-bold text-gray-700 dark:text-gray-300">Tentatives illimitees</p>
                        <p class="text-xs text-gray-500">C'est un examen d'entrainement. Vous pouvez le repasser autant de fois que vous le souhaitez.</p>
                        @else
                        <p class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $examen->nb_tentatives_max }} tentative(s) autorisee(s)</p>
                        <p class="text-xs text-gray-500">Tentative {{ $tentativesCount + 1 }} sur {{ $examen->nb_tentatives_max }}. En cas d'echec, contactez l'administration pour une reactivation.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Regles --}}
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 mb-6">
                <h3 class="text-sm font-bold text-red-700 dark:text-red-400 mb-2">Regles importantes</h3>
                <ul class="text-xs text-red-600 dark:text-red-400/80 space-y-1">
                    <li class="flex items-start gap-2"><span class="shrink-0 mt-0.5">1.</span> Ne quittez pas la page pendant l'examen</li>
                    <li class="flex items-start gap-2"><span class="shrink-0 mt-0.5">2.</span> Repondez a toutes les questions avant la fin du temps</li>
                    <li class="flex items-start gap-2"><span class="shrink-0 mt-0.5">3.</span> Une seule reponse correcte par question</li>
                    <li class="flex items-start gap-2"><span class="shrink-0 mt-0.5">4.</span> Les questions sans reponse comptent comme incorrectes</li>
                    @if($examen->isFinal())
                    <li class="flex items-start gap-2"><span class="shrink-0 mt-0.5">5.</span> L'examen final est certifiant - un certificat est delivre en cas de reussite</li>
                    @endif
                </ul>
            </div>

            {{-- Bouton --}}
            <form method="POST" action="{{ route('apprenant.examen.demarrer', $examen) }}">
                @csrf
                <button type="submit" class="w-full py-4 rounded-xl {{ $examen->type === 'test' ? 'bg-blue-600 hover:bg-blue-500' : 'bg-primary-700 hover:bg-primary-600' }} text-white font-bold text-lg transition flex items-center justify-center gap-3">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                    Commencer l'examen
                </button>
            </form>
        </div>

        {{-- Historique --}}
        @if($historique->isNotEmpty())
        <div class="border-t dark:border-gray-800 p-6">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3">Historique des tentatives</h3>
            <div class="space-y-2">
                @foreach($historique as $t)
                <div class="flex items-center justify-between py-2 px-3 rounded-lg {{ $t->is_passed ? 'bg-emerald-50 dark:bg-emerald-900/10' : 'bg-red-50 dark:bg-red-900/10' }}">
                    <div class="flex items-center gap-3">
                        @if($t->is_passed)
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @else
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        @endif
                        <div>
                            <span class="text-sm font-semibold {{ $t->is_passed ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">{{ $t->score }}%</span>
                            <span class="text-xs text-gray-400 ml-2">Tentative #{{ $t->numero_tentative }}</span>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">{{ $t->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
