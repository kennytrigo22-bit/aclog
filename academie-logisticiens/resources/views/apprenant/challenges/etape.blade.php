@extends('layouts.apprenant')
@section('page-title', $challenge->titre)

@section('content')
<div class="max-w-3xl mx-auto" x-data="{ repondues: 0, total: {{ $questions->count() }} }">
    <a href="{{ route('apprenant.challenges.parcours', $challenge->parcours) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-primary-700 dark:hover:text-accent-400 transition mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        {{ $challenge->parcours->titre }}
    </a>

    <div class="rounded-2xl bg-gradient-to-br from-[#0B1E36] to-[#1B3A5C] text-white p-6 mb-6 sticky top-0 z-20 shadow-lg">
        <span class="text-[11px] font-bold uppercase tracking-widest text-accent-400">Etape &bull; {{ $challenge->theme ?: 'Challenge' }}</span>
        <h2 class="font-heading text-xl font-bold mt-1">{{ $challenge->titre }}</h2>
        @if($challenge->description)<p class="text-sm text-white/70 mt-2">{{ $challenge->description }}</p>@endif
        <div class="mt-4 flex items-center gap-3">
            <div class="flex-1 h-2.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full bg-accent-500 rounded-full transition-all" :style="`width:${total ? repondues/total*100 : 0}%`"></div></div>
            <span class="text-xs font-bold text-accent-400" x-text="repondues + '/' + total"></span>
        </div>
        <p class="text-[11px] text-white/50 mt-2">Obtenez au moins 70% de bonnes reponses pour capturer le drapeau (+{{ $challenge->points }} pts){{ $deja ? ' - drapeau deja capture, vous rejouez pour le plaisir' : '' }}.</p>
    </div>

    <form method="POST" action="{{ route('apprenant.challenges.valider', $challenge) }}" class="space-y-4"
          @change="repondues = new Set([...$el.querySelectorAll('input[type=radio]:checked')].map(i => i.name)).size">
        @csrf
        @foreach($questions as $qi => $question)
        <div class="bg-white dark:bg-surface-dark rounded-xl p-5 shadow-sm">
            <p class="font-semibold text-gray-700 dark:text-gray-200 mb-3">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-accent-500/20 text-accent-600 text-xs font-bold mr-2">{{ $qi + 1 }}</span>{{ $question->texte }}
            </p>
            <div class="space-y-2">
                @foreach($question->reponses->shuffle() as $reponse)
                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-accent-500 has-[:checked]:border-accent-500 has-[:checked]:bg-accent-500/5 cursor-pointer transition">
                    <input type="radio" name="reponses[{{ $question->id }}]" value="{{ $reponse->id }}" class="text-accent-500 focus:ring-accent-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $reponse->texte }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
        <button type="submit" class="w-full py-3.5 rounded-xl bg-accent-500 text-primary-800 font-bold text-lg hover:bg-accent-400 transition shadow">
            Valider l'etape
        </button>
    </form>
</div>
@endsection
