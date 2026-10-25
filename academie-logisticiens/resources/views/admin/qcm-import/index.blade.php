@extends('layouts.admin')
@section('page-title', 'Importeur de questions')

@section('content')
<div class="max-w-3xl space-y-6" x-data="importeur('{{ old('source', 'fichier') }}')">
    <div>
        <h2 class="font-heading text-2xl font-bold text-primary-700 dark:text-white">Importeur de questions</h2>
        <p class="text-sm text-gray-500 mt-1">Importez en masse les questions des examens, quiz de module, tests de niveau et etapes CTF depuis un fichier Word / PDF (analyse par IA) ou un texte colle.</p>
    </div>

    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.qcm-import.analyze') }}" enctype="multipart/form-data" class="space-y-6" @submit="soumettre($event)">
            @csrf

            {{-- 1. Destination --}}
            <div>
                <label class="block text-sm font-bold text-primary-700 dark:text-white mb-1">1. Destination</label>
                <select name="cible" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    <option value="">-- Choisir ou importer les questions --</option>
                    @foreach($groupes as $groupe => $options)
                    <optgroup label="{{ $groupe }}">
                        @foreach($options as $o)
                        <option value="{{ $o['value'] }}" {{ old('cible', $cible) === $o['value'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                @error('cible') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 2. Anciennes donnees --}}
            <div>
                <label class="block text-sm font-bold text-primary-700 dark:text-white mb-2">2. Anciennes questions</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="mode" value="ajouter" class="peer hidden" {{ old('mode', 'ajouter') === 'ajouter' ? 'checked' : '' }}>
                        <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/10 transition">
                            <p class="font-bold text-sm text-gray-800 dark:text-white">Conserver</p>
                            <p class="text-xs text-gray-500 mt-0.5">Les nouvelles questions sont ajoutees aux existantes.</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="mode" value="remplacer" class="peer hidden" {{ old('mode') === 'remplacer' ? 'checked' : '' }}>
                        <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/10 transition">
                            <p class="font-bold text-sm text-gray-800 dark:text-white">Supprimer et remplacer</p>
                            <p class="text-xs text-gray-500 mt-0.5">Toutes les anciennes questions sont effacees.</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- 3. Source --}}
            <div>
                <label class="block text-sm font-bold text-primary-700 dark:text-white mb-2">3. Source des questions</label>
                <div class="flex gap-2 mb-4">
                    <label class="cursor-pointer"><input type="radio" name="source" value="fichier" x-model="source" class="peer hidden"><span class="inline-block px-4 py-2 rounded-lg text-sm font-bold bg-gray-100 dark:bg-gray-800 text-gray-500 peer-checked:bg-violet-600 peer-checked:text-white transition">Fichier Word / PDF (IA)</span></label>
                    <label class="cursor-pointer"><input type="radio" name="source" value="texte" x-model="source" class="peer hidden"><span class="inline-block px-4 py-2 rounded-lg text-sm font-bold bg-gray-100 dark:bg-gray-800 text-gray-500 peer-checked:bg-primary-700 peer-checked:text-white transition">Texte colle</span></label>
                </div>

                <div x-show="source === 'fichier'">
                    @unless($aiDispo)
                    <p class="mb-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/10 text-amber-700 text-xs">Cle GEMINI_API_KEY absente du fichier .env : l'analyse par IA est indisponible.</p>
                    @endunless
                    <label class="block border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-8 text-center cursor-pointer hover:border-violet-400 hover:bg-violet-50/50 dark:hover:bg-violet-900/10 transition">
                        <input type="file" name="documents[]" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt" multiple class="hidden" @change="files = Array.from($event.target.files)">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400" x-show="files.length === 0">Cliquez pour choisir vos fichiers (Word, PDF...) contenant les questions et exercices</p>
                        <template x-for="f in files" :key="f.name"><p class="text-sm font-semibold text-violet-600" x-text="f.name"></p></template>
                        <p class="text-xs text-gray-400 mt-1">Jusqu'a 10 fichiers, 50 Mo chacun. L'IA reconnait les corriges, sinon elle determine les bonnes reponses.</p>
                    </label>
                    @error('documents') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('documents.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div x-show="source === 'texte'" x-cloak>
                    <textarea name="texte" rows="12" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm font-mono focus:ring-accent-500 focus:border-accent-500" placeholder="1. Question ?&#10;A. Reponse&#10;*B. Bonne reponse&#10;C. Reponse&#10;D. Reponse&#10;&#10;2. Question suivante ?...">{{ old('texte') }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">Une ligne vide entre les questions. Marquez la bonne reponse avec * au debut ou a la fin de la ligne.</p>
                    @error('texte') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div x-show="etape" x-cloak class="p-4 rounded-xl bg-violet-50 dark:bg-violet-900/10 border border-violet-200 dark:border-violet-800">
                <p class="text-sm font-bold text-violet-700 dark:text-violet-300" x-text="etape"></p>
                <div class="mt-2 h-2 rounded-full bg-violet-200 dark:bg-violet-900 overflow-hidden"><div class="h-full bg-violet-600 transition-all duration-500" :style="`width:${progression}%`"></div></div>
                <p class="text-xs text-violet-600 mt-2" x-show="total > 0"><strong x-text="total"></strong> question(s) extraite(s) pour l'instant. Ne fermez pas cette page.</p>
                <div x-show="erreur" class="mt-3">
                    <p class="text-sm text-red-600" x-text="erreur"></p>
                    <button type="button" x-show="total > 0" @click="terminer()" class="mt-2 px-4 py-2 rounded-lg bg-violet-600 text-white text-sm font-bold">Continuer avec les <span x-text="total"></span> questions deja extraites</button>
                </div>
            </div>

            <button type="submit" :disabled="uploading" class="w-full py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition disabled:opacity-60 flex items-center justify-center gap-2">
                <span x-show="!uploading">Analyser et previsualiser</span>
                <span x-show="uploading" x-cloak>Analyse en cours...</span>
            </button>
        </form>
    </div>
</div>

<script>
function importeur(source) {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const post = async (url, body) => {
        const res = await fetch(url, { method: 'POST', body, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
        let data = {};
        try { data = await res.json(); } catch (e) {}
        if (!res.ok) {
            const msg = data.message || (data.errors ? Object.values(data.errors).flat()[0] : null) || ('Erreur ' + res.status);
            const err = new Error(msg); err.data = data; throw err;
        }
        return data;
    };
    return {
        source, uploading: false, files: [], etape: '', erreur: '', total: 0, progression: 0,
        async soumettre(event) {
            if (this.source === 'texte') { this.uploading = true; return; }
            event.preventDefault();
            this.erreur = ''; this.total = 0; this.progression = 3; this.uploading = true;
            try {
                this.etape = 'Envoi des fichiers a l\'IA...';
                await post('{{ route('admin.qcm-import.lancer') }}', new FormData(event.target));
                let lot = 0, fini = false;
                while (!fini) {
                    lot++;
                    this.etape = 'Analyse par l\'IA - lot ' + lot + ' (questions ' + ((lot - 1) * 40 + 1) + ' a ' + (lot * 40) + ')...';
                    this.progression = Math.min(92, 8 + lot * 7);
                    let essais = 0, res = null;
                    while (!res) {
                        try { res = await post('{{ route('admin.qcm-import.lot') }}', new FormData()); }
                        catch (e) { if (++essais >= 3) throw e; await new Promise(r => setTimeout(r, 2000)); }
                    }
                    this.total = res.total; fini = res.fini;
                }
                await this.terminer();
            } catch (e) {
                this.erreur = e.message; this.uploading = false;
                if (e.data && typeof e.data.total === 'number') this.total = e.data.total;
            }
        },
        async terminer() {
            try {
                this.etape = 'Preparation de l\'apercu...'; this.progression = 97;
                const res = await post('{{ route('admin.qcm-import.terminer') }}', new FormData());
                window.location.href = res.url;
            } catch (e) { this.erreur = e.message; this.uploading = false; }
        }
    }
}
</script>
@endsection
