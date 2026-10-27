@extends('layouts.public')

@section('title', 'Tests de Niveau - ' . $siteTitre)

@section('content')

<section class="bg-primary-700 text-white py-12 lg:py-16 relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/thumbs/technological-futuristic-holograms-logistics-means-transport.jpg') }}" alt="" class="w-full h-full object-cover opacity-15">
    </div>
    <div class="max-w-7xl mx-auto px-4 lg:px-6 text-center relative z-10">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-accent-400/20 text-accent-400 text-xs font-bold uppercase tracking-wider mb-4">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
            Gagnez +2 points à chaque test réussi
        </span>
        <h1 class="font-heading text-3xl md:text-5xl font-bold">Testez votre Niveau</h1>
        <p class="text-lg text-gray-300 mt-4 max-w-2xl mx-auto">Évaluez vos compétences en logistique, douane ou commerce international. Recevez une orientation IA personnalisée vers les formations adaptées à votre profil.</p>
    </div>
</section>

<section class="w-full py-10 lg:py-16 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-3xl mx-auto px-4 lg:px-6" x-data="testNiveau()">

        {{-- Etape 1 : Selection --}}
        <div x-show="step === 'select'" class="space-y-8">
            <form @submit.prevent="loadQuestions()" class="space-y-8">

                <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 shadow-sm">
                    <h2 class="font-heading text-xl font-bold text-primary-700 dark:text-white mb-4">1. Choisissez votre domaine</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @php
                            $domaines = [
                                ['value' => 'logistique', 'label' => 'Logistique & Supply Chain', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                                ['value' => 'douane', 'label' => 'Douane & Transit', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                                ['value' => 'transport', 'label' => 'Transport', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
                                ['value' => 'transport_logistique', 'label' => 'Transport & Logistique', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                                ['value' => 'commerce', 'label' => 'Commerce International', 'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ];
                        @endphp
                        @foreach($domaines as $d)
                        <label class="cursor-pointer">
                            <input type="radio" name="domaine" value="{{ $d['value'] }}" x-model="domaine" class="hidden peer">
                            <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 peer-checked:border-accent-500 peer-checked:bg-accent-500/5 transition-all flex flex-col items-center gap-2 text-center">
                                <svg class="w-8 h-8 text-primary-700 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $d['icon'] }}"/></svg>
                                <span class="text-sm font-bold text-primary-700 dark:text-white">{{ $d['label'] }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 shadow-sm">
                    <h2 class="font-heading text-xl font-bold text-primary-700 dark:text-white mb-4">2. Votre niveau estimé</h2>
                    <div class="grid grid-cols-3 gap-4">
                        @foreach(['debutant' => 'Débutant', 'intermediaire' => 'Intermédiaire', 'avance' => 'Avancé'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="niveau" value="{{ $val }}" x-model="niveau" class="hidden peer">
                            <div class="p-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 peer-checked:border-accent-500 peer-checked:bg-accent-500/5 transition-all text-center">
                                <span class="text-sm font-bold text-primary-700 dark:text-white">{{ $label }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="bg-accent-500/10 dark:bg-accent-500/5 rounded-xl p-5 border border-accent-500/20 flex items-start gap-3">
                    <svg class="w-5 h-5 text-accent-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        <p class="font-semibold mb-1">Comment ça marche ?</p>
                        <ul class="list-disc ml-4 space-y-1 text-gray-600 dark:text-gray-400">
                            <li>Le test est rapide (10 questions aléatoires)</li>
                            <li>Vous recevez une <strong>orientation IA</strong> vers les formations adaptées</li>
                            <li>Si connecté, vous gagnez <strong>+2 points</strong> en cas de réussite</li>
                            <li>Les points se convertissent en <strong>jetons FCFA</strong> (100 pts = 5 000 FCFA)</li>
                        </ul>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" :disabled="!domaine || !niveau || loading" class="px-8 py-4 rounded-lg bg-accent-500 text-primary-700 font-bold text-lg shadow-lg hover:bg-accent-400 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Lancer le Test de Niveau</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Chargement...
                        </span>
                    </button>
                    <p class="text-xs text-gray-500 mt-2">Gratuit et sans engagement</p>
                </div>
            </form>
        </div>

        {{-- Etape 2 : Questions --}}
        <div x-show="step === 'quiz'" x-cloak>
            <div class="mb-6 text-center">
                <h2 class="font-heading text-2xl font-bold text-primary-700 dark:text-white">Test de Niveau</h2>
                <p class="text-sm text-gray-500 mt-1 capitalize" x-text="domaine + ' - ' + niveau"></p>
            </div>

            <p x-show="errorMsg" class="text-center text-red-500 font-bold mb-4" x-text="errorMsg"></p>

            <form method="POST" action="{{ route('tests.soumettre') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="domaine" :value="domaine">
                <input type="hidden" name="niveau" :value="niveau">

                <template x-for="(q, qi) in questions" :key="q.id">
                    <div class="bg-white dark:bg-surface-dark rounded-xl p-5 shadow-sm">
                        <p class="font-semibold text-gray-700 dark:text-gray-200 mb-3">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-accent-500/20 text-accent-500 text-xs font-bold mr-2" x-text="qi + 1"></span>
                            <span x-text="q.texte"></span>
                        </p>
                        <div class="space-y-2">
                            <template x-for="r in q.reponses" :key="r.id">
                                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-accent-500 cursor-pointer transition">
                                    <input type="radio" :name="'reponses[' + q.id + ']'" :value="r.id" required class="text-accent-500 focus:ring-accent-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300" x-text="r.texte"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <button type="submit" class="w-full py-4 rounded-lg bg-accent-500 text-primary-700 font-bold text-lg shadow-lg hover:bg-accent-400 transition">
                    Soumettre mes réponses
                </button>

                <button type="button" @click="step = 'select'" class="w-full py-2 text-sm text-gray-500 hover:text-primary-700 transition">
                    Changer de domaine/niveau
                </button>
            </form>
        </div>
    </div>
</section>

<script>
function testNiveau() {
    return {
        step: 'select',
        domaine: '',
        niveau: '',
        questions: [],
        loading: false,
        errorMsg: '',
        async loadQuestions() {
            if (!this.domaine || !this.niveau) return;
            this.loading = true;
            this.errorMsg = '';
            try {
                const res = await fetch('{{ route("tests.questions") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ domaine: this.domaine, niveau: this.niveau }),
                });
                const data = await res.json();
                if (data.questions && data.questions.length > 0) {
                    this.questions = data.questions;
                    this.step = 'quiz';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    this.errorMsg = data.message || 'Aucune question disponible pour cette combinaison.';
                    this.step = 'quiz';
                }
            } catch (e) {
                this.errorMsg = 'Erreur de chargement. Veuillez réessayer.';
            }
            this.loading = false;
        }
    }
}
</script>
@endsection
