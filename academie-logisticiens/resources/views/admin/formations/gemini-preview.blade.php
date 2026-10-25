@extends('layouts.admin')
@section('page-title', 'Verifier l\'import IA - ' . $formation->titre)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('admin.formations.contenu', $formation) }}" class="hover:text-primary-700 dark:hover:text-accent-400 transition">{{ $formation->titre }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary-700 dark:text-accent-400">Verification du contenu IA</span>
    </div>

    <div class="bg-gradient-to-r from-violet-600 to-purple-600 rounded-xl p-5 mb-6 flex items-center gap-4 text-white">
        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        </div>
        <div>
            <h2 class="font-heading font-bold text-lg">Contenu extrait par l'IA</h2>
            <p class="text-sm text-white/80">
                {{ count($data['modules'] ?? []) }} module(s),
                {{ count($data['examen_test']['questions'] ?? []) }} question(s) examen test,
                {{ count($data['examen_final']['questions'] ?? []) }} question(s) examen final.
                Verifiez et modifiez avant d'enregistrer.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.formations.gemini.save', $formation) }}" id="gemini-form">
        @csrf

        {{-- Modules --}}
        <div class="space-y-6" id="modules-container">
            @foreach(($data['modules'] ?? []) as $mi => $module)
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden" x-data="{ expanded: true }">
                <div class="bg-primary-700 dark:bg-primary-700/80 px-5 py-4 flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white font-bold text-sm">{{ $mi + 1 }}</span>
                        <div>
                            <p class="text-xs text-white/60 uppercase font-bold tracking-wider">Module {{ $mi + 1 }}</p>
                            <input type="text" name="modules[{{ $mi }}][titre]" value="{{ $module['titre'] ?? '' }}" class="bg-transparent text-white font-bold text-lg border-0 border-b border-transparent focus:border-white/50 focus:ring-0 p-0 w-full" @click.stop>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-white/60 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>

                <div x-show="expanded" x-transition class="p-5 space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Description du module</label>
                        <textarea name="modules[{{ $mi }}][description]" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-violet-500 focus:border-violet-500">{{ $module['description'] ?? '' }}</textarea>
                    </div>

                    {{-- Lecons --}}
                    <div>
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            Lecons ({{ count($module['lecons'] ?? []) }})
                        </h4>
                        <div class="space-y-3">
                            @foreach(($module['lecons'] ?? []) as $li => $lecon)
                            <div class="border dark:border-gray-700 rounded-lg p-4" x-data="{ showContent: false }">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                    <div class="md:col-span-6">
                                        <label class="block text-xs text-gray-500 mb-0.5">Titre</label>
                                        <input type="text" name="modules[{{ $mi }}][lecons][{{ $li }}][titre]" value="{{ $lecon['titre'] ?? '' }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-violet-500 focus:border-violet-500">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-xs text-gray-500 mb-0.5">URL video</label>
                                        <input type="text" name="modules[{{ $mi }}][lecons][{{ $li }}][video_url]" value="{{ $lecon['video_url'] ?? '' }}" placeholder="https://youtube.com/..." class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-violet-500 focus:border-violet-500">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs text-gray-500 mb-0.5">Duree (min)</label>
                                        <input type="number" name="modules[{{ $mi }}][lecons][{{ $li }}][duree_minutes]" value="{{ $lecon['duree_minutes'] ?? '' }}" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-violet-500 focus:border-violet-500">
                                    </div>
                                    <div class="md:col-span-1 flex items-end">
                                        <button type="button" @click="showContent = !showContent" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 transition" title="Voir le contenu">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div x-show="showContent" x-transition class="mt-3">
                                    <label class="block text-xs text-gray-500 mb-0.5">Contenu de la lecon</label>
                                    <textarea name="modules[{{ $mi }}][lecons][{{ $li }}][contenu_texte]" rows="6" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-violet-500 focus:border-violet-500 font-mono">{{ $lecon['contenu_texte'] ?? '' }}</textarea>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Quiz --}}
                    @if(!empty($module['quiz']['questions']))
                    <div>
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Quiz ({{ count($module['quiz']['questions']) }} questions)
                        </h4>
                        <div class="space-y-3">
                            @foreach($module['quiz']['questions'] as $qi => $question)
                            <div class="bg-purple-50 dark:bg-purple-900/10 border border-purple-200 dark:border-purple-800/30 rounded-lg p-4">
                                <div class="flex gap-3 mb-3">
                                    <span class="w-6 h-6 rounded-full bg-purple-200 dark:bg-purple-800 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-bold shrink-0">{{ $qi + 1 }}</span>
                                    <div class="flex-1">
                                        <input type="text" name="modules[{{ $mi }}][quiz][questions][{{ $qi }}][texte]" value="{{ $question['texte'] ?? '' }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-purple-500 focus:border-purple-500">
                                        <input type="hidden" name="modules[{{ $mi }}][quiz][questions][{{ $qi }}][type]" value="{{ $question['type'] ?? 'qcm' }}">
                                    </div>
                                </div>
                                <div class="ml-9 space-y-1.5">
                                    @foreach(($question['reponses'] ?? []) as $ri => $reponse)
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="modules[{{ $mi }}][quiz][questions][{{ $qi }}][reponses][{{ $ri }}][is_correct]" value="1" {{ !empty($reponse['is_correct']) ? 'checked' : '' }} class="text-purple-500 focus:ring-purple-500 rounded">
                                        <input type="text" name="modules[{{ $mi }}][quiz][questions][{{ $qi }}][reponses][{{ $ri }}][texte]" value="{{ $reponse['texte'] ?? '' }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-purple-500 focus:border-purple-500">
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Examen Test --}}
        @if(!empty($data['examen_test']['questions']))
        <div class="mt-8">
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden" x-data="{ expanded: true }">
                <div class="bg-blue-600 px-5 py-4 flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white font-bold text-sm">T</span>
                        <div>
                            <p class="text-xs text-white/60 uppercase font-bold tracking-wider">Examen Test (Blanc)</p>
                            <p class="text-white font-bold">{{ count($data['examen_test']['questions']) }} questions extraites</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-white/60 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div x-show="expanded" x-transition class="p-5 space-y-3 max-h-[500px] overflow-y-auto">
                    @foreach($data['examen_test']['questions'] as $qi => $question)
                    <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/30 rounded-lg p-4">
                        <div class="flex gap-3 mb-3">
                            <span class="w-6 h-6 rounded-full bg-blue-200 dark:bg-blue-800 text-blue-700 dark:text-blue-300 flex items-center justify-center text-xs font-bold shrink-0">{{ $qi + 1 }}</span>
                            <div class="flex-1">
                                <input type="text" name="examen_test[questions][{{ $qi }}][texte]" value="{{ $question['texte'] ?? '' }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-blue-500 focus:border-blue-500">
                                <input type="hidden" name="examen_test[questions][{{ $qi }}][type]" value="{{ $question['type'] ?? 'qcm' }}">
                            </div>
                        </div>
                        <div class="ml-9 space-y-1.5">
                            @foreach(($question['reponses'] ?? []) as $ri => $reponse)
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="examen_test[questions][{{ $qi }}][reponses][{{ $ri }}][is_correct]" value="1" {{ !empty($reponse['is_correct']) ? 'checked' : '' }} class="text-blue-500 focus:ring-blue-500 rounded">
                                <input type="text" name="examen_test[questions][{{ $qi }}][reponses][{{ $ri }}][texte]" value="{{ $reponse['texte'] ?? '' }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Examen Final --}}
        @if(!empty($data['examen_final']['questions']))
        <div class="mt-6">
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden" x-data="{ expanded: true }">
                <div class="bg-primary-700 px-5 py-4 flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white font-bold text-sm">F</span>
                        <div>
                            <p class="text-xs text-white/60 uppercase font-bold tracking-wider">Examen Final</p>
                            <p class="text-white font-bold">{{ count($data['examen_final']['questions']) }} questions extraites</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-white/60 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div x-show="expanded" x-transition class="p-5 space-y-3 max-h-[500px] overflow-y-auto">
                    @foreach($data['examen_final']['questions'] as $qi => $question)
                    <div class="bg-gray-50 dark:bg-gray-800/30 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex gap-3 mb-3">
                            <span class="w-6 h-6 rounded-full bg-primary-700/20 text-primary-700 dark:text-accent-400 flex items-center justify-center text-xs font-bold shrink-0">{{ $qi + 1 }}</span>
                            <div class="flex-1">
                                <input type="text" name="examen_final[questions][{{ $qi }}][texte]" value="{{ $question['texte'] ?? '' }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                                <input type="hidden" name="examen_final[questions][{{ $qi }}][type]" value="{{ $question['type'] ?? 'qcm' }}">
                            </div>
                        </div>
                        <div class="ml-9 space-y-1.5">
                            @foreach(($question['reponses'] ?? []) as $ri => $reponse)
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="examen_final[questions][{{ $qi }}][reponses][{{ $ri }}][is_correct]" value="1" {{ !empty($reponse['is_correct']) ? 'checked' : '' }} class="text-accent-500 focus:ring-accent-500 rounded">
                                <input type="text" name="examen_final[questions][{{ $qi }}][reponses][{{ $ri }}][texte]" value="{{ $reponse['texte'] ?? '' }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="mt-8 flex items-center gap-3 sticky bottom-4 bg-white dark:bg-surface-dark rounded-xl shadow-lg p-4 border dark:border-gray-800">
            <button type="submit" class="flex-1 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-green-600 text-white font-bold hover:from-emerald-500 hover:to-green-500 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Enregistrer tout dans la formation
            </button>
            <a href="{{ route('admin.formations.gemini.upload', $formation) }}" class="px-6 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition">Recommencer</a>
        </div>
    </form>
</div>
@endsection
