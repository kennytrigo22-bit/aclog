@extends('layouts.admin')
@section('page-title', 'Contenu - ' . $formation->titre)

@section('content')
<div class="max-w-4xl space-y-6">

    {{-- En-tête --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.formations.index') }}" class="hover:text-primary-700 dark:hover:text-accent-400">Formations</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>{{ $formation->titre }}</span>
            </div>
            <h2 class="font-heading text-xl font-bold text-primary-700 dark:text-white">Gestion du contenu</h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.formations.examens', $formation) }}" class="px-4 py-2 rounded-lg bg-primary-700 text-white text-sm font-bold hover:bg-primary-500 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Examens
            </a>
            <a href="{{ route('admin.formations.gemini.upload', $formation) }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-bold hover:from-violet-500 hover:to-purple-500 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                Import IA
            </a>
            <a href="{{ route('admin.formations.edit', $formation) }}" class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition">Modifier la formation</a>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white dark:bg-surface-dark rounded-xl p-4 shadow-sm text-center">
            <p class="text-2xl font-bold text-primary-700 dark:text-white">{{ $formation->modules->count() }}</p>
            <p class="text-xs text-gray-500">Modules</p>
        </div>
        <div class="bg-white dark:bg-surface-dark rounded-xl p-4 shadow-sm text-center">
            <p class="text-2xl font-bold text-primary-700 dark:text-white">{{ $formation->modules->sum(fn($m) => $m->lecons->count()) }}</p>
            <p class="text-xs text-gray-500">Leçons</p>
        </div>
        <div class="bg-white dark:bg-surface-dark rounded-xl p-4 shadow-sm text-center">
            <p class="text-2xl font-bold text-primary-700 dark:text-white">{{ $formation->modules->filter(fn($m) => $m->quiz)->count() }}</p>
            <p class="text-xs text-gray-500">Quiz</p>
        </div>
    </div>

    {{-- Ajouter un module --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <h3 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-4">Ajouter un module</h3>
        <form method="POST" action="{{ route('admin.modules.store', $formation) }}" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Titre du module *</label>
                <input type="text" name="titre" required placeholder="Ex: Introduction à la logistique" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition text-sm whitespace-nowrap">Ajouter</button>
        </form>
    </div>

    {{-- Modules --}}
    @foreach($formation->modules as $module)
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden" x-data="{ openLecon: false, openQuiz: false }">
        {{-- En-tête du module --}}
        <div class="p-5 border-b dark:border-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-accent-500 uppercase">Module {{ $module->ordre }}</span>
                    <h3 class="font-heading font-bold text-primary-700 dark:text-white text-lg">{{ $module->titre }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $module->lecons->count() }} leçon(s) {{ $module->quiz ? '• 1 quiz' : '' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="openLecon = !openLecon" class="px-3 py-1.5 rounded-lg bg-primary-700 text-white text-xs font-bold hover:bg-primary-500 transition">+ Leçon</button>
                    @if(!$module->quiz)
                    <form method="POST" action="{{ route('admin.quiz.store', $module) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-purple-600 text-white text-xs font-bold hover:bg-purple-500 transition">+ Quiz</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.modules.destroy', $module) }}" onsubmit="return confirm('Supprimer ce module et tout son contenu ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Formulaire ajout leçon --}}
            <div x-show="openLecon" x-cloak x-transition class="mt-4 p-4 bg-surface-light dark:bg-surface-darker rounded-lg">
                <form method="POST" action="{{ route('admin.lecons.store', $module) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Titre *</label>
                            <input type="text" name="titre" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-dark text-sm focus:ring-accent-500 focus:border-accent-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">URL vidéo</label>
                            <input type="url" name="video_url" placeholder="https://..." class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-dark text-sm focus:ring-accent-500 focus:border-accent-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Durée (min)</label>
                            <input type="number" name="duree_minutes" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-dark text-sm focus:ring-accent-500 focus:border-accent-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Contenu</label>
                            <textarea name="contenu_texte" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-dark text-sm focus:ring-accent-500 focus:border-accent-500" placeholder="Contenu HTML ou texte de la leçon..."></textarea>
                        </div>
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_preview" value="1" class="rounded text-accent-500 focus:ring-accent-500">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Accessible en aperçu gratuit</span>
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent-500 text-primary-700 font-bold text-sm hover:bg-accent-400 transition">Enregistrer</button>
                        <button type="button" @click="openLecon = false" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-sm font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition">Annuler</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Liste des leçons --}}
        @if($module->lecons->count() > 0)
        <div class="divide-y dark:divide-gray-800">
            @foreach($module->lecons as $lecon)
            <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800/30">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-primary-700/10 dark:bg-primary-500/20 flex items-center justify-center text-xs font-bold text-primary-700 dark:text-primary-300">{{ $lecon->ordre }}</span>
                    <div>
                        <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $lecon->titre }}</p>
                        <div class="flex items-center gap-2 mt-0.5">
                            @if($lecon->video_url)
                            <span class="text-[10px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-900/20 px-1.5 py-0.5 rounded">VIDEO</span>
                            @endif
                            @if($lecon->contenu_texte)
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 px-1.5 py-0.5 rounded">TEXTE</span>
                            @endif
                            @if($lecon->is_preview)
                            <span class="text-[10px] font-bold text-orange-600 bg-orange-50 dark:bg-orange-900/20 px-1.5 py-0.5 rounded">APERÇU</span>
                            @endif
                            @if($lecon->duree_minutes)
                            <span class="text-[10px] text-gray-400">{{ $lecon->duree_minutes }} min</span>
                            @endif
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.lecons.destroy', $lecon) }}" onsubmit="return confirm('Supprimer cette leçon ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1.5 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Quiz --}}
        @if($module->quiz)
        <div class="border-t dark:border-gray-800 bg-purple-50/50 dark:bg-purple-900/10">
            <div class="px-5 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-bold text-sm text-purple-700 dark:text-purple-300">{{ $module->quiz->titre }}</span>
                    <span class="text-xs text-gray-500">({{ $module->quiz->questions->count() }} questions)</span>
                </div>
                <button @click="openQuiz = !openQuiz" class="px-3 py-1.5 rounded-lg bg-purple-600 text-white text-xs font-bold hover:bg-purple-500 transition">+ Question</button>
            </div>

            {{-- Questions existantes --}}
            @foreach($module->quiz->questions as $question)
            <div class="px-5 py-2 border-t border-purple-100 dark:border-purple-900/30 flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold">Q{{ $question->ordre }}.</span> {{ $question->texte }}</p>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach($question->reponses as $rep)
                        <span class="text-xs px-2 py-0.5 rounded {{ $rep->is_correct ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 font-bold' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">{{ $rep->texte }}</span>
                        @endforeach
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" onsubmit="return confirm('Supprimer ?')">
                    @csrf @method('DELETE')
                    <button class="p-1 text-red-400 hover:text-red-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </form>
            </div>
            @endforeach

            {{-- Formulaire ajout question --}}
            <div x-show="openQuiz" x-cloak x-transition class="px-5 py-4 border-t border-purple-100 dark:border-purple-900/30">
                <form method="POST" action="{{ route('admin.questions.store', $module->quiz) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Question *</label>
                        <input type="text" name="texte" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Type</label>
                            <select name="type" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                                <option value="qcm">QCM</option>
                                <option value="vrai_faux">Vrai / Faux</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Points</label>
                            <input type="number" name="points" value="1" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Réponses (cocher la/les bonne(s))</label>
                        <div class="space-y-2" x-data="{ count: 3 }">
                            <template x-for="i in count" :key="i">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" :name="'reponses[' + (i-1) + '][is_correct]'" class="rounded text-emerald-500 focus:ring-emerald-500">
                                    <input type="text" :name="'reponses[' + (i-1) + '][texte]'" required placeholder="Réponse..." class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                                </div>
                            </template>
                            <button type="button" @click="count++" class="text-xs text-accent-500 font-semibold hover:underline">+ Ajouter une réponse</button>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 text-white font-bold text-sm hover:bg-purple-500 transition">Ajouter la question</button>
                        <button type="button" @click="openQuiz = false" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-sm font-semibold transition">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
    @endforeach

    @if($formation->modules->count() === 0)
    <div class="bg-white dark:bg-surface-dark rounded-xl p-10 shadow-sm text-center">
        <svg class="w-14 h-14 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <p class="text-gray-400 font-medium">Aucun module pour cette formation</p>
        <p class="text-sm text-gray-400 mt-1">Commencez par ajouter un module ci-dessus.</p>
    </div>
    @endif
</div>
@endsection
