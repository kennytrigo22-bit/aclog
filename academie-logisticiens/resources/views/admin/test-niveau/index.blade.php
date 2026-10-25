@extends('layouts.admin')
@section('page-title', 'Tests de Niveau')

@section('content')
<div class="max-w-5xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-heading text-2xl font-bold text-primary-700 dark:text-white">Tests de Niveau</h2>
            <p class="text-sm text-gray-500 mt-1">Gerez les questions par domaine et niveau. Les questions sont selectionnees aleatoirement lors du test.</p>
        </div>
        <a href="{{ route('admin.test-niveau.resultats') }}" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-sm hover:bg-gray-300 dark:hover:bg-gray-600 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Voir les resultats
        </a>
    </div>

    @foreach($domaines as $domaine)
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r {{ $domaine === 'logistique' ? 'from-blue-600 to-blue-700' : ($domaine === 'douane' ? 'from-emerald-600 to-emerald-700' : 'from-violet-600 to-violet-700') }} text-white">
            <h3 class="font-heading font-bold text-lg capitalize">{{ $domaine }}</h3>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($niveaux as $niveau)
                @php
                    $test = $tests[$domaine]->where('niveau', $niveau)->first();
                @endphp
                <div class="border dark:border-gray-800 rounded-xl p-4" x-data="{ open: false }">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-bold text-primary-700 dark:text-white capitalize">{{ $niveau }}</h4>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ ($test->questions_count ?? 0) > 0 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600' : 'bg-gray-200 dark:bg-gray-700 text-gray-500' }}">
                            {{ $test->questions_count ?? 0 }} questions
                        </span>
                    </div>

                    @if($test)
                    {{-- Voir les questions existantes --}}
                    @if($test->questions_count > 0)
                    <details class="mb-3">
                        <summary class="text-xs text-blue-600 dark:text-blue-400 cursor-pointer hover:underline">Voir les questions</summary>
                        <div class="mt-2 space-y-2 max-h-60 overflow-y-auto">
                            @foreach($test->questions()->with('reponses')->get() as $q)
                            <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800/50 text-xs">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="font-semibold text-gray-700 dark:text-gray-300">{{ $q->texte }}</p>
                                    <form method="POST" action="{{ route('admin.test-niveau.questions.destroy', $q) }}" onsubmit="return confirm('Supprimer ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600 shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                                <div class="mt-1 space-y-0.5">
                                    @foreach($q->reponses as $r)
                                    <span class="inline-block px-1.5 py-0.5 rounded {{ $r->is_correct ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600' : 'text-gray-400' }}">{{ $r->texte }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </details>
                    @endif

                    <a href="{{ route('admin.qcm-import.index', ['cible' => 'niveau:' . $test->id]) }}" class="block w-full mb-2 py-2 rounded-lg bg-violet-600 text-white text-xs font-bold text-center hover:bg-violet-500 transition">Importer depuis Word / PDF (IA)</a>

                    {{-- Bouton ajouter --}}
                    <button @click="open = !open" class="w-full py-2 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 text-gray-400 text-xs font-bold hover:border-accent-500 hover:text-accent-500 transition flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Ajouter une question
                    </button>

                    {{-- Formulaire inline --}}
                    <form method="POST" action="{{ route('admin.test-niveau.questions.store') }}" x-show="open" x-collapse class="mt-3 space-y-3">
                        @csrf
                        <input type="hidden" name="test_niveau_id" value="{{ $test->id }}">

                        <div>
                            <input type="text" name="texte" required placeholder="Question..." class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                        </div>

                        <select name="type" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                            <option value="qcm">QCM (choix multiple)</option>
                            <option value="vrai_faux">Vrai / Faux</option>
                        </select>

                        <div class="space-y-2" x-data="{ reponses: [{texte: '', correct: false}, {texte: '', correct: false}, {texte: '', correct: false}, {texte: '', correct: false}] }">
                            <template x-for="(r, i) in reponses" :key="i">
                                <div class="flex items-center gap-2">
                                    <input type="text" :name="'reponses['+i+'][texte]'" x-model="r.texte" required placeholder="Reponse..." class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-xs focus:ring-accent-500 focus:border-accent-500">
                                    <label class="flex items-center gap-1 text-xs whitespace-nowrap">
                                        <input type="checkbox" :name="'reponses['+i+'][is_correct]'" x-model="r.correct" class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                                        <span class="text-emerald-600">Correct</span>
                                    </label>
                                </div>
                            </template>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-accent-500 text-primary-700 font-bold text-xs hover:bg-accent-400 transition">Ajouter</button>
                            <button type="button" @click="open = false" class="px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold transition">Annuler</button>
                        </div>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
