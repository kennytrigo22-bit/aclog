@extends('layouts.admin')
@section('page-title', 'Examens - ' . $formation->titre)

@section('content')
<div class="max-w-5xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.formations.contenu', $formation) }}" class="hover:text-primary-700 dark:hover:text-accent-400">{{ $formation->titre }}</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-primary-700 dark:text-accent-400">Examens</span>
            </div>
            <h2 class="font-heading text-xl font-bold text-primary-700 dark:text-white">Gestion des examens</h2>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-3 text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</div>
    @endif

    {{-- Examen Test (Blanc) --}}
    @php $ex = $examenTest; @endphp
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden" x-data="{ showForm: {{ $ex ? 'false' : 'true' }}, showQuestions: false, showAddQ: false }">
        <div class="bg-blue-600 px-5 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white font-bold text-sm">T</span>
                <div>
                    <h3 class="font-heading font-bold text-white">Examen Test (Blanc)</h3>
                    <p class="text-xs text-white/70">Tentatives illimitees, questions aleatoires</p>
                </div>
            </div>
            @if($ex)
            <span class="px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold">{{ $ex->questions->count() }} questions dans le pool</span>
            @endif
        </div>
        <div class="p-5 space-y-4">
            @if($ex)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3"><p class="text-lg font-bold text-primary-700 dark:text-white">{{ $ex->nb_questions }}</p><p class="text-xs text-gray-500">Questions/examen</p></div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3"><p class="text-lg font-bold text-primary-700 dark:text-white">{{ $ex->duree_minutes }} min</p><p class="text-xs text-gray-500">Duree</p></div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3"><p class="text-lg font-bold text-primary-700 dark:text-white">{{ $ex->score_minimum }}%</p><p class="text-xs text-gray-500">Score minimum</p></div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3"><p class="text-lg font-bold text-primary-700 dark:text-white">{{ $ex->questions->count() }}</p><p class="text-xs text-gray-500">Pool total</p></div>
            </div>
            <div class="flex gap-2">
                <button @click="showForm = !showForm" class="text-sm text-blue-600 font-semibold hover:underline">Modifier les parametres</button>
                <span class="text-gray-300">|</span>
                <button @click="showQuestions = !showQuestions" class="text-sm text-blue-600 font-semibold hover:underline">Voir les questions (<span x-text="showQuestions ? 'masquer' : 'afficher'"></span>)</button>
                <span class="text-gray-300">|</span>
                <button @click="showAddQ = !showAddQ" class="text-sm text-emerald-600 font-semibold hover:underline">+ Ajouter une question</button>
            </div>
            @endif

            <div x-show="showForm" x-transition x-cloak>
                <form method="POST" action="{{ route('admin.formations.examens.store', $formation) }}" class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
                    @csrf
                    <input type="hidden" name="type" value="test">
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1">Titre</label><input type="text" name="titre" value="{{ $ex->titre ?? 'Examen Test' }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm"></div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1">Questions/session</label><input type="number" name="nb_questions" value="{{ $ex->nb_questions ?? 10 }}" min="1" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm"></div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1">Duree (min)</label><input type="number" name="duree_minutes" value="{{ $ex->duree_minutes ?? 60 }}" min="5" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm"></div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1">Score min (%)</label><input type="number" name="score_minimum" value="{{ $ex->score_minimum ?? 70 }}" min="1" max="100" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm"></div>
                    <div>
                        <input type="hidden" name="nb_tentatives_max" value="9999">
                        <button type="submit" class="w-full py-2 rounded-lg bg-blue-600 text-white font-bold text-sm hover:bg-blue-500 transition">Enregistrer</button>
                    </div>
                </form>
            </div>

            @if($ex)
            {{-- Liste des questions --}}
            <div x-show="showQuestions" x-transition x-cloak class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($ex->questions as $qi => $q)
                <div class="flex items-start gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3">
                    <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs font-bold shrink-0">{{ $qi + 1 }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $q->texte }}</p>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach($q->reponses as $r)
                            <span class="px-2 py-0.5 rounded text-xs {{ $r->is_correct ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold' : 'bg-gray-100 dark:bg-gray-800 text-gray-500' }}">{{ $r->texte }}</span>
                            @endforeach
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.examens.questions.destroy', $q) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')
                        <button class="p-1 text-gray-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </form>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">Aucune question. Ajoutez des questions au pool.</p>
                @endforelse
            </div>

            {{-- Formulaire ajout question --}}
            <div x-show="showAddQ" x-transition x-cloak>
                @include('admin.formations._question-form', ['actionUrl' => route('admin.examens.questions.store', $ex)])
            </div>
            @endif
        </div>
    </div>

    {{-- Examen Final --}}
    @php $ex = $examenFinal; @endphp
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden" x-data="{ showForm: {{ $ex ? 'false' : 'true' }}, showQuestions: false, showAddQ: false }">
        <div class="bg-primary-700 px-5 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white font-bold text-sm">F</span>
                <div>
                    <h3 class="font-heading font-bold text-white">Examen Final</h3>
                    <p class="text-xs text-white/70">{{ $ex ? $ex->nb_tentatives_max : 1 }} tentative(s) max, certificat si reussi</p>
                </div>
            </div>
            @if($ex)
            <span class="px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold">{{ $ex->questions->count() }} questions dans le pool</span>
            @endif
        </div>
        <div class="p-5 space-y-4">
            @if($ex)
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-center">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3"><p class="text-lg font-bold text-primary-700 dark:text-white">{{ $ex->nb_questions }}</p><p class="text-xs text-gray-500">Questions/examen</p></div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3"><p class="text-lg font-bold text-primary-700 dark:text-white">{{ $ex->duree_minutes }} min</p><p class="text-xs text-gray-500">Duree</p></div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3"><p class="text-lg font-bold text-primary-700 dark:text-white">{{ $ex->score_minimum }}%</p><p class="text-xs text-gray-500">Score minimum</p></div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3"><p class="text-lg font-bold text-primary-700 dark:text-white">{{ $ex->nb_tentatives_max }}</p><p class="text-xs text-gray-500">Tentatives max</p></div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3"><p class="text-lg font-bold text-primary-700 dark:text-white">{{ $ex->questions->count() }}</p><p class="text-xs text-gray-500">Pool total</p></div>
            </div>
            <div class="flex gap-2">
                <button @click="showForm = !showForm" class="text-sm text-primary-700 dark:text-accent-400 font-semibold hover:underline">Modifier les parametres</button>
                <span class="text-gray-300">|</span>
                <button @click="showQuestions = !showQuestions" class="text-sm text-primary-700 dark:text-accent-400 font-semibold hover:underline">Voir les questions</button>
                <span class="text-gray-300">|</span>
                <button @click="showAddQ = !showAddQ" class="text-sm text-emerald-600 font-semibold hover:underline">+ Ajouter une question</button>
            </div>
            @endif

            <div x-show="showForm" x-transition x-cloak>
                <form method="POST" action="{{ route('admin.formations.examens.store', $formation) }}" class="grid grid-cols-2 md:grid-cols-6 gap-3 items-end">
                    @csrf
                    <input type="hidden" name="type" value="final">
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1">Titre</label><input type="text" name="titre" value="{{ $ex->titre ?? 'Examen Final' }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm"></div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1">Questions</label><input type="number" name="nb_questions" value="{{ $ex->nb_questions ?? 10 }}" min="1" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm"></div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1">Duree (min)</label><input type="number" name="duree_minutes" value="{{ $ex->duree_minutes ?? 80 }}" min="5" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm"></div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1">Score min (%)</label><input type="number" name="score_minimum" value="{{ $ex->score_minimum ?? 70 }}" min="1" max="100" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm"></div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1">Tentatives max</label><input type="number" name="nb_tentatives_max" value="{{ $ex->nb_tentatives_max ?? 1 }}" min="1" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm"></div>
                    <div><button type="submit" class="w-full py-2 rounded-lg bg-primary-700 text-white font-bold text-sm hover:bg-primary-500 transition">Enregistrer</button></div>
                </form>
            </div>

            @if($ex)
            <div x-show="showQuestions" x-transition x-cloak class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($ex->questions as $qi => $q)
                <div class="flex items-start gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3">
                    <span class="w-6 h-6 rounded-full bg-primary-700/20 text-primary-700 dark:text-accent-400 flex items-center justify-center text-xs font-bold shrink-0">{{ $qi + 1 }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $q->texte }}</p>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach($q->reponses as $r)
                            <span class="px-2 py-0.5 rounded text-xs {{ $r->is_correct ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold' : 'bg-gray-100 dark:bg-gray-800 text-gray-500' }}">{{ $r->texte }}</span>
                            @endforeach
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.examens.questions.destroy', $q) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')
                        <button class="p-1 text-gray-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </form>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">Aucune question. Ajoutez des questions au pool.</p>
                @endforelse
            </div>

            <div x-show="showAddQ" x-transition x-cloak>
                @include('admin.formations._question-form', ['actionUrl' => route('admin.examens.questions.store', $ex)])
            </div>
            @endif
        </div>
    </div>

    {{-- Reactivation tentatives examen final --}}
    @if($examenFinal && $tentativesFinales->isNotEmpty())
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">
        <div class="bg-orange-500 px-5 py-4">
            <h3 class="font-heading font-bold text-white">Reactivation des tentatives</h3>
            <p class="text-xs text-white/70">Apprenants ayant echoue et atteint le maximum de tentatives</p>
        </div>
        <div class="p-5">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-400">Apprenant</th>
                        <th class="px-3 py-2 text-center font-semibold text-gray-600 dark:text-gray-400">Score</th>
                        <th class="px-3 py-2 text-center font-semibold text-gray-600 dark:text-gray-400">Date</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600 dark:text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-800">
                    @foreach($tentativesFinales as $t)
                    <tr>
                        <td class="px-3 py-2">
                            <p class="font-semibold text-gray-700 dark:text-gray-300">{{ $t->user->nom_complet ?? $t->user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $t->user->email }}</p>
                        </td>
                        <td class="px-3 py-2 text-center"><span class="px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900/20 text-red-600 text-xs font-bold">{{ $t->score }}%</span></td>
                        <td class="px-3 py-2 text-center text-gray-500">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 text-right">
                            <form method="POST" action="{{ route('admin.examens.tentatives.reactiver', $t) }}" onsubmit="return confirm('Reactiver la tentative pour cet apprenant ?')">
                                @csrf
                                <button class="px-3 py-1.5 rounded-lg bg-orange-500 text-white text-xs font-bold hover:bg-orange-400 transition">Reactiver</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
