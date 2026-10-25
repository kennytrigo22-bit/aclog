@extends('layouts.public')

@section('title', $siteTitre . ' - Formations Certifiantes en Logistique, Douane & Commerce International')

@section('content')
{{-- Spacer pour la navbar fixe --}}
<div class="pt-28"></div>

{{-- ═══════════════════ HERO SECTION ═══════════════════ --}}
<section class="relative w-full overflow-hidden bg-primary-700 text-white"
    x-data="{
        current: 0,
        loaded: 1,
        images: [
            '{{ asset('assets/images/thumbs/aerial-view-cargo-ship-cargo-container-harbor.jpg') }}',
            '{{ asset('assets/images/thumbs/global-logistics-transportation-network.jpg') }}',
            '{{ asset('assets/images/thumbs/commercial-trucks-lined-up-logistics-hub-sunset.jpg') }}',
            '{{ asset('assets/images/thumbs/industrial-port-container-yard.jpg') }}',
            '{{ asset('assets/images/thumbs/freight-trucks-driving-highway-logistics-road-transport-content.jpg') }}'
        ],
        init() {
            window.addEventListener('load', () => setTimeout(() => this.loaded = this.images.length, 1500));
            setInterval(() => this.current = (this.current + 1) % this.loaded, 5000);
        }
    }">
    <div class="absolute inset-0">
        <template x-for="(img, i) in images.slice(0, loaded)" :key="i">
            <img :src="img" alt="" decoding="async" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-1000"
                 :class="current === i ? 'opacity-20' : 'opacity-0'">
        </template>
    </div>
    <div class="absolute inset-0 bg-gradient-to-r from-primary-700/80 via-primary-700/40 to-primary-700/80 z-[1]"></div>
    <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-accent-400/10 blur-3xl pointer-events-none z-[2]"></div>
    <div class="absolute -bottom-20 right-1/4 w-[32rem] h-[32rem] rounded-full bg-emerald-400/10 blur-3xl pointer-events-none z-[2]"></div>
    {{-- Indicateurs du carrousel --}}
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-20">
        <template x-for="(img, i) in images" :key="'dot-'+i">
            <button @click="current = i" class="w-2.5 h-2.5 rounded-full transition-all duration-300"
                    :class="current === i ? 'bg-accent-400 w-6' : 'bg-white/40 hover:bg-white/60'"></button>
        </template>
    </div>
    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-10 lg:py-24 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-7 flex flex-col gap-6">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-accent-400 max-w-fit shadow-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span class="text-xs font-bold tracking-wider uppercase">Hub Digital Panafricain &bull; Session 2026 Ouverte</span>
                </div>
                <h1 class="font-heading text-3xl md:text-5xl font-bold tracking-tight leading-tight">
                    Propulsez votre carrière en <span class="text-accent-400">Logistique</span>, Douane & Commerce International
                </h1>
                <p class="text-lg text-gray-300 max-w-2xl">
                    Le 1<sup class="text-xs">er</sup> cabinet académique digital de référence en Afrique Centrale. Formations certifiantes, examens immersifs et parcours gamifiés avec récompenses convertibles en <span class="text-accent-400 font-semibold">jetons FCFA</span>.
                </p>
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <a href="{{ route('formations.index') }}" class="px-6 py-3.5 rounded-lg bg-accent-500 text-primary-700 font-bold shadow-lg hover:bg-accent-400 transition-all flex items-center gap-2 group">
                        <span>Explorer le Catalogue</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <div class="relative group">
                        <span class="absolute -top-3 right-2 px-2 py-0.5 rounded-full bg-emerald-400 text-emerald-900 text-[10px] uppercase font-bold tracking-wider shadow">
                            Orientation IA
                        </span>
                        <a href="{{ route('tests.index') }}" class="px-5 py-3.5 rounded-lg bg-white/10 text-white hover:bg-white/20 transition-all font-medium flex items-center gap-2 backdrop-blur-sm">
                            <svg class="w-5 h-5 text-accent-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                            <span>Test de Niveau gratuit (+2 pts)</span>
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4">
                    <div class="flex items-center gap-2 text-gray-300">
                        <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-accent-400 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </span>
                        <span class="text-xs font-semibold leading-tight">Certifications vérifiables par QR Code</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-300">
                        <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-accent-400 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </span>
                        <span class="text-xs font-semibold leading-tight">Mobile Money MTN & Airtel (+242)</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-300">
                        <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-accent-400 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </span>
                        <span class="text-xs font-semibold leading-tight">Experts terrain CEMAC</span>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-5 relative flex justify-center">
                <div class="absolute inset-0 bg-accent-400/20 rounded-2xl rotate-3 scale-95 blur-sm"></div>
                <div class="relative w-full max-w-md rounded-2xl shadow-2xl overflow-hidden">
                    <img src="{{ asset('assets/images/certificat-aclog.jpeg') }}" alt="Certificat de fin de formation ACLOG" class="w-full h-auto rounded-2xl">
                </div>
                <div class="absolute -bottom-6 -left-4 sm:-left-6 bg-white dark:bg-surface-dark text-gray-900 dark:text-gray-100 p-4 rounded-xl shadow-xl flex items-center gap-3 max-w-xs animate-bounce" style="animation-duration: 4s;">
                    <div class="w-10 h-10 rounded-full bg-primary-700 text-accent-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-primary-700 dark:text-white">Certificat Vérifiable</span>
                        <span class="text-xs text-gray-500">QR Code de vérification intégré</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════ BANDEAU CHIFFRES CLÉS ═══════════════════ --}}
<section class="w-full bg-white dark:bg-surface-dark shadow-sm py-6 relative z-20">
    <div class="max-w-7xl mx-auto px-4 lg:px-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-10">
            <div class="flex items-center gap-4 p-2">
                <div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-gray-800 flex items-center justify-center text-primary-700 dark:text-accent-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-heading text-2xl font-bold text-primary-700 dark:text-white tracking-tight">{{ number_format($stats['apprenants']) }}+</span>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Apprenants actifs</span>
                </div>
            </div>
            <div class="flex items-center gap-4 p-2">
                <div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-gray-800 flex items-center justify-center text-accent-500 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-heading text-2xl font-bold text-primary-700 dark:text-white tracking-tight">98.4%</span>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Taux de réussite</span>
                </div>
            </div>
            <div class="flex items-center gap-4 p-2">
                <div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-gray-800 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-heading text-2xl font-bold text-primary-700 dark:text-white tracking-tight">{{ $stats['formations'] }}+</span>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Formations certifiantes</span>
                </div>
            </div>
            <div class="flex items-center gap-4 p-2">
                <div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-gray-800 flex items-center justify-center text-accent-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-heading text-2xl font-bold text-primary-700 dark:text-white tracking-tight">{{ number_format($stats['certificats']) }}+</span>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Certificats délivrés</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════ 3 PILIERS D'EXCELLENCE ═══════════════════ --}}
<section class="w-full py-10 lg:py-24 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 flex flex-col gap-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="flex flex-col gap-2 max-w-2xl">
                <span class="text-xs font-bold text-accent-500 uppercase tracking-wider">Nos Filières Royales</span>
                <h2 class="font-heading text-2xl md:text-4xl font-bold text-primary-700 dark:text-white">3 Piliers d'Excellence pour Dominer le Marché</h2>
                <p class="text-gray-600 dark:text-gray-400">Conçus avec les armateurs, manutentionnaires de Pointe-Noire et les autorités douanières congolaises.</p>
            </div>
            <a href="{{ route('formations.index') }}" class="text-sm font-semibold text-primary-700 dark:text-accent-400 hover:text-accent-500 flex items-center gap-1 transition-colors self-start md:self-auto">
                Toutes les spécialisations
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @php
                $piliers = [
                    ['icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'num' => 'I', 'titre' => 'Supply Chain & Logistique Multimodale', 'desc' => 'Gestion opérationnelle des stocks, corridors routiers Brazzaville-Pointe-Noire et logistique fluviale sur le fleuve Congo.', 'competences' => ['WMS & Optimisation des flux', 'Gestion de flotte & Télématique', 'KPI de rupture et coûts logistiques'], 'modules' => '6 Modules Certifiants'],
                    ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'num' => 'II', 'titre' => 'Douane, Transit & Législation CEMAC', 'desc' => 'Maîtrise des régimes douaniers suspensifs, contentieux douanier, SYDONIA World et classification tarifaire SH.', 'competences' => ['Déclarations en douane & D24', 'Incoterms® 2020 & Valeur en douane', 'Transit corridor RCA & Tchad'], 'modules' => '8 Modules Pratiques'],
                    ['icon' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8', 'num' => 'III', 'titre' => 'Commerce International & Fret', 'desc' => 'Négociation maritime, fret aérien IATA, assurance transport cargo et instruments de paiement documentaire.', 'competences' => ['Connaissement maritime (B/L) & LTA', 'Crédit documentaire & Lettre de crédit', 'Surestaries et détention au port'], 'modules' => '5 Modules Internationaux'],
                ];
            @endphp
            @foreach($piliers as $pilier)
            <div class="bg-white dark:bg-surface-dark p-8 rounded-2xl shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                <div class="flex flex-col gap-4">
                    <div class="w-14 h-14 rounded-xl bg-primary-50 dark:bg-gray-800 text-primary-700 dark:text-accent-400 flex items-center justify-center group-hover:bg-primary-700 group-hover:text-accent-400 transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $pilier['icon'] }}"/></svg>
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-xs font-bold text-accent-500 uppercase">PILIER {{ $pilier['num'] }}</span>
                        <h3 class="font-heading text-xl font-bold text-primary-700 dark:text-white">{{ $pilier['titre'] }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $pilier['desc'] }}</p>
                    </div>
                    <ul class="flex flex-col gap-2 pt-2">
                        @foreach($pilier['competences'] as $comp)
                        <li class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 text-accent-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            {{ $comp }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="pt-6">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $pilier['modules'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════ FORMATIONS PHARES ═══════════════════ --}}
<section class="w-full py-10 lg:py-24 bg-white dark:bg-surface-dark" id="catalogue">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 flex flex-col gap-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="flex flex-col gap-2">
                <span class="text-xs font-bold text-accent-500 uppercase tracking-wider">Programmes à Impact Immédiat</span>
                <h2 class="font-heading text-2xl md:text-4xl font-bold text-primary-700 dark:text-white">Formations Phares & Populaires</h2>
                <p class="text-gray-600 dark:text-gray-400">Inscrivez-vous directement avec MTN Mobile Money, Airtel Money ou vos jetons FCFA.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($formationsPopulaires as $formation)
            @php
                $imagesParCategorie = [
                    'logistique' => ['african-american-people-looking-supplies-list-pc-working-with-computer-check-inventory-logistics-employees-organizing-merchandise-shelves-warehouse-space-handheld-shot.jpg', 'global-logistics-transportation-network.jpg', '6206720.jpg'],
                    'commerce' => ['aerial-view-cargo-ship-cargo-container-harbor (1).jpg', 'businessman-touching-virtual-screen.jpg', 'aerial-view-cargo-ship-cargo-container-harbor (2).jpg'],
                    'douane' => ['industrial-port-container-yard.jpg', 'freight-trucks-driving-highway-logistics-road-transport-content.jpg', '6241196.jpg'],
                ];
                $catKey = strtolower($formation->categorie ?? 'logistique');
                $imagesPool = $imagesParCategorie[$catKey] ?? $imagesParCategorie['logistique'];
                $fallbackImg = $imagesPool[$loop->index % count($imagesPool)];
            @endphp
            <div class="bg-surface-light dark:bg-surface-darker rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col group">
                <div class="relative h-48 w-full overflow-hidden bg-gray-200 dark:bg-gray-800">
                    @if($formation->image)
                    <img src="{{ asset('storage/' . $formation->image) }}" alt="{{ $formation->titre }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <img loading="lazy" decoding="async" src="{{ asset('assets/images/thumbs/' . $fallbackImg) }}" alt="{{ $formation->titre }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @endif
                    <div class="absolute top-3 left-3 flex gap-2">
                        @if($formation->is_featured)
                        <span class="px-2 py-1 rounded-full bg-accent-500 text-primary-700 text-xs font-bold uppercase shadow">Populaire</span>
                        @endif
                        <span class="px-2 py-1 rounded-full bg-primary-700/80 backdrop-blur-md text-white text-xs">{{ $formation->duree_heures ?? '?' }}h &bull; {{ $formation->modules->count() }} modules</span>
                    </div>
                </div>
                <div class="p-5 flex flex-col flex-1 justify-between gap-4">
                    <div class="flex flex-col gap-2">
                        <span class="text-xs font-bold text-accent-500 uppercase">{{ $formation->categorie }}</span>
                        <h3 class="font-heading text-lg font-bold text-primary-700 dark:text-white line-clamp-2">{{ $formation->titre }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $formation->description_courte }}</p>
                    </div>
                    <div class="flex flex-col gap-3 pt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Certification incluse</span>
                            <span class="font-heading text-xl font-bold text-primary-700 dark:text-white">{{ number_format($formation->prix_effectif, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-500">FCFA</span></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('formations.show', $formation) }}" class="py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-primary-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-center">
                                Voir programme
                            </a>
                            <a href="{{ route('formations.show', $formation) }}" class="py-2.5 rounded-lg bg-primary-700 text-white text-sm font-bold hover:bg-primary-500 transition-colors flex items-center justify-center gap-1">
                                <span>Acheter</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500">Les formations seront bientôt disponibles.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ═══════════════════ GAMIFICATION & CHALLENGES CTF ═══════════════════ --}}
<section class="w-full py-10 lg:py-24 bg-primary-700 text-white relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/thumbs/global-logistics-transportation-network.jpg') }}" alt="" class="w-full h-full object-cover opacity-10">
    </div>
    <div class="absolute -right-20 -bottom-20 w-96 h-96 rounded-full bg-accent-400/10 blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 lg:px-6 flex flex-col gap-10 relative z-10">
        <div class="text-center max-w-3xl mx-auto flex flex-col gap-2">
            <span class="text-xs font-bold text-accent-400 uppercase tracking-wider">Modèle Éducatif Innovant</span>
            <h2 class="font-heading text-2xl md:text-4xl font-bold">Apprenez, Relevez des Défis & Gagnez des Jetons FCFA</h2>
            <p class="text-gray-300">Chaque action académique est récompensée. Convertissez vos points en réductions financières sur vos prochains modules certifiants.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
                $etapes = [
                    ['icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-accent-500', 'text' => 'text-primary-700', 'num' => '1', 'titre' => 'Test de niveau rapide', 'desc' => 'Évaluez vos compétences sur 10 questions aléatoires en transit ou entrepôt.', 'badge' => 'Gain : +2 points', 'badgeBg' => 'bg-emerald-600 text-white'],
                    ['icon' => 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9', 'bg' => 'bg-emerald-500', 'text' => 'text-white', 'num' => '2', 'titre' => 'Challenges CTF Logistique', 'desc' => 'Résolvez un blocage douanier simulé ou un conteneur en anomalie.', 'badge' => 'Gain : +5 points / flag', 'badgeBg' => 'bg-emerald-600 text-white'],
                    ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-accent-400', 'text' => 'text-primary-700', 'num' => '3', 'titre' => 'Conversion en Jetons FCFA', 'desc' => 'Vos points débloquent des bons de réduction déductibles à la caisse.', 'badge' => '100 pts = 5 000 FCFA', 'badgeBg' => 'bg-accent-500 text-primary-700'],
                ];
            @endphp
            @foreach($etapes as $etape)
            <div class="bg-white/5 backdrop-blur-md p-8 rounded-2xl flex flex-col items-center text-center gap-4">
                <div class="w-16 h-16 rounded-2xl {{ $etape['bg'] }} {{ $etape['text'] }} flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $etape['icon'] }}"/></svg>
                </div>
                <div class="flex flex-col gap-2">
                    <span class="text-xs font-mono font-bold text-accent-400 tracking-wider">ÉTAPE {{ $etape['num'] }}</span>
                    <h3 class="font-heading text-xl font-bold">{{ $etape['titre'] }}</h3>
                    <p class="text-sm text-gray-300">{{ $etape['desc'] }}</p>
                </div>
                <span class="px-4 py-1.5 rounded-full {{ $etape['badgeBg'] }} text-sm font-bold">{{ $etape['badge'] }}</span>
            </div>
            @endforeach
        </div>
        <div class="bg-white dark:bg-surface-dark text-gray-900 dark:text-gray-100 rounded-2xl p-6 flex flex-col md:flex-row items-center justify-between gap-4 shadow-xl">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-accent-400/20 text-accent-500 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-heading font-bold text-primary-700 dark:text-white">Challenge Hebdomadaire Actif</span>
                    <span class="text-sm text-gray-500">Plus de 340 logisticiens s'affrontent sur le diagnostic de chaîne de froid.</span>
                </div>
            </div>
            <a href="{{ route('tests.index') }}" class="px-6 py-3 rounded-lg bg-primary-700 text-white font-bold hover:bg-primary-500 transition-colors shrink-0">
                Rejoindre l'Arène
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════ TÉMOIGNAGES ═══════════════════ --}}
<section class="w-full py-10 lg:py-24 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 flex flex-col gap-10">
        <div class="text-center max-w-2xl mx-auto flex flex-col gap-2">
            <span class="text-xs font-bold text-accent-500 uppercase tracking-wider">Succès Réels</span>
            <h2 class="font-heading text-2xl md:text-4xl font-bold text-primary-700 dark:text-white">Ils témoignent de Brazzaville à Pointe-Noire</h2>
            <p class="text-gray-600 dark:text-gray-400">Nos diplômés occupent des postes clés au sein des terminaux portuaires, agences en douane et multinationales.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
                $temoignages = [
                    ['nom' => 'Cedric Nguesso', 'poste' => 'Déclarant en Douane Senior • Pointe-Noire', 'texte' => 'Grâce au module sur les régimes douaniers suspensifs CEMAC et aux simulations SYDONIA, j\'ai réussi mon entretien chez Bolloré Transport & Logistics à Pointe-Noire dès le mois suivant ma certification.', 'initiales' => 'CN'],
                    ['nom' => 'Clarisse Moukala', 'poste' => 'Superviseur Logistique • Brazzaville', 'texte' => 'La flexibilité de la plateforme m\'a permis d\'étudier le soir tout en conservant mon poste de gestionnaire de stocks à Maya-Maya. Le paiement par Airtel Money était fluide et immédiat.', 'initiales' => 'CM'],
                    ['nom' => 'Jean-Paul Samba', 'poste' => 'Directeur d\'Agence Transit • Kinshasa / BZV', 'texte' => 'Les jetons FCFA gagnés via les challenges m\'ont fait économiser 15 000 FCFA sur mon certificat international Incoterms. C\'est l\'école la plus innovante d\'Afrique Centrale.', 'initiales' => 'JS'],
                ];
            @endphp
            @foreach($temoignages as $t)
            <div class="bg-white dark:bg-surface-dark p-6 rounded-2xl shadow-sm flex flex-col justify-between gap-4">
                <div class="flex flex-col gap-3">
                    <div class="flex text-accent-400">
                        @for($i = 0; $i < 5; $i++)
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 italic">&laquo; {{ $t['texte'] }} &raquo;</p>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <div class="w-11 h-11 rounded-full bg-primary-700 text-accent-400 flex items-center justify-center font-bold text-sm">{{ $t['initiales'] }}</div>
                    <div class="flex flex-col">
                        <span class="font-semibold text-primary-700 dark:text-white">{{ $t['nom'] }}</span>
                        <span class="text-xs text-gray-500">{{ $t['poste'] }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($partenaires->count() > 0)
        <div class="pt-4 flex flex-col gap-4 items-center w-full">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest text-center">Partenaires Logistiques & Reseaux d'Insertion</span>
            <div class="w-full overflow-hidden">
                <div class="flex items-center gap-12 {{ $partenaires->count() >= 4 ? 'animate-marquee-partners hover:[animation-play-state:paused]' : 'justify-center' }}">
                    @foreach($partenaires as $p)
                    <div class="shrink-0 h-12 w-28 flex items-center justify-center transition-all hover:scale-110" title="{{ $p->nom }}">
                        <img src="{{ asset('storage/' . $p->logo) }}" alt="{{ $p->nom }}" class="max-w-full max-h-full object-contain">
                    </div>
                    @endforeach
                    @if($partenaires->count() >= 4)
                    @foreach($partenaires as $p)
                    <div class="shrink-0 h-12 w-28 flex items-center justify-center transition-all hover:scale-110" title="{{ $p->nom }}">
                        <img src="{{ asset('storage/' . $p->logo) }}" alt="{{ $p->nom }}" class="max-w-full max-h-full object-contain">
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</section>

{{-- ═══════════════════ CTA FINAL ═══════════════════ --}}
<section class="w-full py-16 lg:py-24 bg-primary-700 text-white relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/thumbs/commercial-trucks-lined-up-logistics-hub-sunset.jpg') }}" alt="" class="w-full h-full object-cover opacity-15">
    </div>
    <div class="max-w-4xl mx-auto px-4 lg:px-6 text-center flex flex-col gap-6 items-center relative z-10">
        <h2 class="font-heading text-2xl md:text-4xl font-bold">Prêt à transformer votre carrière ?</h2>
        <p class="text-gray-300 max-w-2xl">Rejoignez plus de {{ number_format($stats['apprenants']) }} professionnels qui ont choisi l'excellence avec {{ $siteTitre }} de Brazzaville.</p>
        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="px-8 py-4 rounded-lg bg-accent-500 text-primary-700 font-bold text-lg shadow-lg hover:bg-accent-400 transition-all">
                S'inscrire Gratuitement
            </a>
            <a href="{{ route('contact') }}" class="px-8 py-4 rounded-lg bg-white/10 text-white font-medium text-lg hover:bg-white/20 transition-all backdrop-blur-sm">
                Nous Contacter
            </a>
        </div>
    </div>
</section>
@endsection
