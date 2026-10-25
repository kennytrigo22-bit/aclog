@extends('layouts.admin')
@section('page-title', 'Importer via IA - ' . $formation->titre)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('admin.formations.contenu', $formation) }}" class="hover:text-primary-700 dark:hover:text-accent-400 transition">{{ $formation->titre }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary-700 dark:text-accent-400">Import IA</span>
    </div>

    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm p-8" x-data="uploadManager()">
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <h2 class="font-heading text-xl font-bold text-primary-700 dark:text-white">Import intelligent par IA</h2>
            <p class="text-sm text-gray-500 mt-2">Uploadez vos fichiers de cours (PDF, PowerPoint, Word). L'IA Gemini analysera tout et extraira automatiquement les modules, lecons, quiz et questions d'examens.</p>
        </div>

        @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
            <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.formations.gemini.analyze', $formation) }}" enctype="multipart/form-data" @submit="uploading = true">
            @csrf

            <label class="block border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-8 text-center cursor-pointer hover:border-violet-400 dark:hover:border-violet-500 hover:bg-violet-50/50 dark:hover:bg-violet-900/10 transition group">
                <input type="file" name="documents[]" accept=".pdf,.pptx,.ppt,.docx,.doc" multiple class="hidden" required @change="handleFiles($event)">
                <div x-show="files.length === 0">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3 group-hover:text-violet-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Cliquez ou glissez vos fichiers ici</p>
                    <p class="text-xs text-gray-400 mt-1">PDF, PowerPoint, Word - Plusieurs fichiers possibles - Max 50 Mo chacun</p>
                </div>
                <div x-show="files.length > 0" x-cloak>
                    <div class="space-y-2">
                        <template x-for="(file, index) in files" :key="index">
                            <div class="flex items-center justify-center gap-3 py-1">
                                <svg class="w-5 h-5 text-violet-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="text-sm font-semibold text-violet-700 dark:text-violet-400" x-text="file.name"></span>
                                <span class="text-xs text-gray-400" x-text="formatSize(file.size)"></span>
                            </div>
                        </template>
                    </div>
                    <p class="text-xs text-gray-400 mt-2" x-text="files.length + ' fichier(s) selectionne(s)'"></p>
                </div>
            </label>
            @error('documents')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            @error('documents.*')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach(['mode_contenu' => 'Contenu du cours (modules, lecons, quiz)', 'mode_examens' => 'Questions des examens (test / final)'] as $name => $titre)
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
                    <p class="text-xs font-bold text-gray-600 dark:text-gray-300 mb-2">{{ $titre }}</p>
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-1 cursor-pointer"><input type="radio" name="{{ $name }}" value="ajouter" checked class="text-violet-600 focus:ring-violet-500"> Conserver l'existant et ajouter</label>
                    <label class="flex items-center gap-2 text-sm text-red-600 cursor-pointer"><input type="radio" name="{{ $name }}" value="remplacer" class="text-red-600 focus:ring-red-500"> Supprimer l'ancien et remplacer</label>
                </div>
                @endforeach
            </div>

            <button type="submit" x-show="files.length > 0" x-cloak :disabled="uploading" class="mt-6 w-full py-3 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 text-white font-bold hover:from-violet-500 hover:to-purple-500 transition disabled:opacity-50 flex items-center justify-center gap-2">
                <template x-if="!uploading">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Analyser avec Gemini
                    </span>
                </template>
                <template x-if="uploading">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Analyse en cours (peut prendre 1-2 minutes)...
                    </span>
                </template>
            </button>
        </form>

        <div class="mt-6 bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
            <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Ce que l'IA va faire</h4>
            <ul class="text-xs text-gray-500 space-y-1.5">
                <li class="flex items-start gap-2"><svg class="w-3.5 h-3.5 text-violet-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Decouper le contenu en modules et lecons</li>
                <li class="flex items-start gap-2"><svg class="w-3.5 h-3.5 text-violet-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Extraire le contenu detaille de chaque lecon</li>
                <li class="flex items-start gap-2"><svg class="w-3.5 h-3.5 text-violet-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Generer des quiz pour chaque module</li>
                <li class="flex items-start gap-2"><svg class="w-3.5 h-3.5 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <strong>Extraire les questions d'examen test et final</strong></li>
                <li class="flex items-start gap-2"><svg class="w-3.5 h-3.5 text-violet-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Vous pourrez tout modifier avant d'enregistrer</li>
            </ul>
        </div>
    </div>
</div>

<script>
function uploadManager() {
    return {
        files: [],
        uploading: false,
        handleFiles(event) {
            this.files = Array.from(event.target.files);
        },
        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' o';
            if (bytes < 1048576) return (bytes / 1024).toFixed(0) + ' Ko';
            return (bytes / 1048576).toFixed(1) + ' Mo';
        }
    }
}
</script>
@endsection
