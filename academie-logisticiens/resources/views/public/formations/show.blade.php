                @if($formation->programme)
                <div>
                    <h2 class="font-heading text-2xl font-bold text-primary-700 dark:text-white mb-4">Plan de la formation</h2>
                    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm contenu-cours text-gray-700 dark:text-gray-200">
                        {!! str_contains($formation->programme, "</") ? \App\Support\HtmlSanitizer::clean($formation->programme) : nl2br(e($formation->programme)) !!}
                    </div>
                </div>
                @endif
            </div>

@extends('layouts.public')

@section('title', $formation->titre . ' - ' . $siteTitre)

@section('content')
<div class="pt-28"></div>

{{-- Hero formation --}}
<section class="bg-primary-700 text-white py-10 lg:py-16 relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ $formation->image ? asset('storage/' . $formation->image) : asset('assets/images/thumbs/freight-trucks-driving-highway-logistics-road-transport-content.jpg') }}" alt="" class="w-full h-full object-cover opacity-15">
    </div>
    <div class="max-w-7xl mx-auto px-4 lg:px-6 relative z-10">
        <div class="flex items-center gap-2 text-sm text-gray-300 mb-4">
            <a href="{{ route('formations.index') }}" class="hover:text-white transition">Formations</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-accent-400">{{ $formation->titre }}</span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 flex flex-col gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-full bg-accent-500/20 text-accent-400 text-xs font-bold uppercase">{{ $formation->categorie }}</span>
                    <span class="px-3 py-1 rounded-full bg-white/10 text-xs font-medium capitalize">{{ $formation->niveau }}</span>
                    @if($formation->is_featured)
                    <span class="px-3 py-1 rounded-full bg-accent-500 text-primary-700 text-xs font-bold uppercase">Populaire</span>
                    @endif
                </div>
                <h1 class="font-heading text-2xl md:text-4xl font-bold">{{ $formation->titre }}</h1>
                <p class="text-gray-300 text-lg">{{ $formation->description }}</p>
                <div class="flex flex-wrap items-center gap-6 text-sm text-gray-300 pt-2">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $formation->duree_heures ?? '?' }} heures
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        {{ $formation->modules->count() }} modules
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ $formation->modules->sum(fn($m) => $m->lecons->count()) }} leçons
                    </span>
                    @if($formation->examen)
                    <span class="flex items-center gap-1.5">
                        <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Examen final
                    </span>
                    @endif
                </div>
            </div>
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-surface-dark text-gray-900 dark:text-gray-100 rounded-2xl p-6 shadow-xl">
                    @if($formation->image)
                    <img src="{{ asset('storage/' . $formation->image) }}" alt="{{ $formation->titre }}" class="w-full h-40 object-cover rounded-xl mb-4">
                    @endif
                    <div class="flex items-baseline gap-2 mb-4">
                        @if($formation->promo_active && $formation->prix_promo)
                        <span class="font-heading text-3xl font-bold text-primary-700 dark:text-white">{{ number_format($formation->prix_promo, 0, ',', ' ') }}</span>
                        <span class="text-sm text-gray-500">FCFA</span>
                        <span class="text-sm text-gray-400 line-through ml-2">{{ number_format($formation->prix, 0, ',', ' ') }}</span>
                        @else
                        <span class="font-heading text-3xl font-bold text-primary-700 dark:text-white">{{ number_format($formation->prix, 0, ',', ' ') }}</span>
                        <span class="text-sm text-gray-500">FCFA</span>
                        @endif
                    </div>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400 mb-6">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-accent-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Accès à vie aux cours
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-accent-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Certificat vérifiable par QR Code
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-accent-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Support pédagogique
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-accent-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Paiement MTN / Airtel Money
                        </li>
                    </ul>
                    @auth
                    <a href="{{ route('apprenant.paiement.initier', $formation) }}" class="w-full block text-center py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition-colors shadow">
                        S'inscrire à cette formation
                    </a>
                    @else
                    <a href="{{ route('register') }}" class="w-full block text-center py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition-colors shadow">
                        Créer un compte pour s'inscrire
                    </a>
                    <a href="{{ route('login') }}" class="w-full block text-center py-2.5 mt-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-primary-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        Déjà inscrit ? Se connecter
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Programme détaillé --}}
<section class="w-full py-10 lg:py-16 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-7xl mx-auto px-4 lg:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 flex flex-col gap-8">
                <div>
                    <h2 class="font-heading text-2xl font-bold text-primary-700 dark:text-white mb-6">Programme de la formation</h2>
                    <div class="space-y-4" x-data="{ openModule: 0 }">
                        @foreach($formation->modules as $index => $module)
                        <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">
                            <button @click="openModule = openModule === {{ $index }} ? -1 : {{ $index }}" class="w-full p-5 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                <div class="flex items-center gap-4">
                                    <span class="w-10 h-10 rounded-lg bg-primary-700 text-accent-400 flex items-center justify-center font-bold text-sm shrink-0">{{ $index + 1 }}</span>
                                    <div>
                                        <h3 class="font-heading font-bold text-primary-700 dark:text-white">{{ $module->titre }}</h3>
                                        <span class="text-xs text-gray-500">{{ $module->lecons->count() }} leçons</span>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': openModule === {{ $index }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="openModule === {{ $index }}" x-transition class="border-t dark:border-gray-800">
                                @if($module->description)
                                <p class="px-5 pt-3 text-sm text-gray-600 dark:text-gray-400">{{ $module->description }}</p>
                                @endif
                                <ul class="p-5 space-y-2">
                                    @foreach($module->lecons as $lecon)
                                    <li class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ $lecon->titre }}</span>
                                        @if($lecon->duree_minutes)
                                        <span class="ml-auto text-xs text-gray-400">{{ $lecon->duree_minutes }} min</span>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($formation->programme)
                <div>
                    <h2 class="font-heading text-2xl font-bold text-primary-700 dark:text-white mb-4">Description détaillée</h2>
                    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm prose dark:prose-invert max-w-none">
                        {!! nl2br(e($formation->programme)) !!}
                    </div>
                </div>
                @endif
            </div>

            <div class="lg:col-span-1">
                <div class="sticky top-40 space-y-6">
                    {{-- Infos --}}
                    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
                        <h3 class="font-heading font-bold text-primary-700 dark:text-white mb-4">Informations</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Catégorie</dt>
                                <dd class="font-semibold text-gray-700 dark:text-gray-300 capitalize">{{ $formation->categorie }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Niveau</dt>
                                <dd class="font-semibold text-gray-700 dark:text-gray-300 capitalize">{{ $formation->niveau }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Durée</dt>
                                <dd class="font-semibold text-gray-700 dark:text-gray-300">{{ $formation->duree_heures ?? '?' }}h</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Modules</dt>
                                <dd class="font-semibold text-gray-700 dark:text-gray-300">{{ $formation->modules->count() }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Leçons</dt>
                                <dd class="font-semibold text-gray-700 dark:text-gray-300">{{ $formation->modules->sum(fn($m) => $m->lecons->count()) }}</dd>
                            </div>
                            @if($formation->examen)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Examen final</dt>
                                <dd class="font-semibold text-accent-500">Oui - {{ $formation->examen->nb_questions }} questions</dd>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Certificat</dt>
                                <dd class="font-semibold text-accent-500">PDF + QR Code</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- CTA mobile --}}
                    <div class="bg-accent-500/10 dark:bg-accent-500/5 rounded-xl p-6 border border-accent-500/20">
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">Paiement sécurisé par <strong>MTN Mobile Money</strong> ou <strong>Airtel Money</strong></p>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <svg class="w-4 h-4 text-accent-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                            Données sécurisées SSL
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
