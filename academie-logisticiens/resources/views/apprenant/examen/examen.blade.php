@extends('layouts.apprenant')
@section('page-title', $examen->titre . ' - ' . $examen->formation->titre)

@section('content')
<div class="max-w-3xl mx-auto" x-data="examTimer({{ $secondesRestantes }})">
    <div class="{{ $examen->isTest() ? 'bg-blue-600' : 'bg-primary-700' }} text-white rounded-xl p-6 mb-6 sticky top-0 z-30 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold text-sm">{{ $examen->isTest() ? 'T' : 'F' }}</span>
                    <h2 class="font-heading text-lg font-bold">{{ $examen->titre }}</h2>
                </div>
                <p class="text-xs text-white/70">{{ $questions->count() }} questions &bull; Score min: {{ $examen->score_minimum }}%</p>
            </div>
            <div class="text-right">
                <div class="flex items-center gap-2 px-3 py-2 rounded-lg" :class="remaining < 120 ? 'bg-red-500/30 animate-pulse' : 'bg-white/10'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-mono text-xl font-bold" x-text="display" :class="{ 'text-red-300': remaining < 120 }"></span>
                </div>
                <p class="text-xs mt-1 text-white/50" x-show="remaining < 120" x-transition>Soumission automatique imminente</p>
            </div>
        </div>
    </div>

    <div x-show="timeUp" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" style="display: none;">
        <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 text-center max-w-sm mx-4">
            <svg class="w-16 h-16 mx-auto text-amber-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="font-heading text-xl font-bold text-gray-800 dark:text-white">Temps ecoule !</h3>
            <p class="text-sm text-gray-500 mt-2">Vos reponses sont en cours de soumission...</p>
        </div>
    </div>

    <form method="POST" action="{{ route('apprenant.examen.soumettre', $examen) }}" id="exam-form" class="space-y-4">
        @csrf
        @foreach($questions as $qi => $question)
        <div class="bg-white dark:bg-surface-dark rounded-xl p-5 shadow-sm">
            <p class="font-semibold text-gray-700 dark:text-gray-200 mb-3">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full {{ $examen->isTest() ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600' : 'bg-primary-100 dark:bg-primary-900/30 text-primary-700' }} text-xs font-bold mr-2">{{ $qi + 1 }}</span>
                {{ $question->texte }}
            </p>
            <div class="space-y-2">
                @foreach($question->reponses as $reponse)
                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-accent-500 cursor-pointer transition">
                    <input type="radio" name="reponses[{{ $question->id }}]" value="{{ $reponse->id }}" class="text-accent-500 focus:ring-accent-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $reponse->texte }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
        <button type="submit" class="w-full py-3 rounded-lg {{ $examen->isTest() ? 'bg-blue-600 hover:bg-blue-500 text-white' : 'bg-accent-500 hover:bg-accent-400 text-primary-700' }} font-bold transition shadow">
            Soumettre l'examen
        </button>
    </form>
</div>

<script>
function examTimer(seconds) {
    return {
        remaining: seconds,
        display: '',
        timeUp: false,
        interval: null,
        init() {
            this.tick();
            setInterval(() => fetch('{{ route("apprenant.ping") }}', { credentials: 'same-origin' }).catch(() => {}), 240000);
            this.interval = setInterval(() => {
                this.remaining--;
                this.tick();
                if (this.remaining <= 0) {
                    clearInterval(this.interval);
                    this.timeUp = true;
                    setTimeout(() => {
                        document.getElementById('exam-form').submit();
                    }, 1500);
                }
            }, 1000);
        },
        tick() {
            let m = Math.floor(Math.max(0, this.remaining) / 60);
            let s = Math.max(0, this.remaining) % 60;
            this.display = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        }
    }
}
</script>
@endsection
