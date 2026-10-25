@extends('layouts.public')

@section('title', 'Catalogue des Formations - ' . $siteTitre)

@section('content')
<div class="pt-28"></div>

{{-- Header --}}
<section class="bg-primary-700 text-white py-12 lg:py-16 relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/thumbs/industrial-port-container-yard.jpg') }}" alt="" class="w-full h-full object-cover opacity-15">
    </div>
    <div class="max-w-7xl mx-auto px-4 lg:px-6 relative z-10">
        <div class="max-w-3xl">
            <span class="text-xs font-bold text-accent-400 uppercase tracking-wider">Catalogue Complet</span>
            <h1 class="font-heading text-3xl md:text-5xl font-bold mt-2">Nos Formations Certifiantes</h1>
            <p class="text-lg text-gray-300 mt-4">Logistique, Douane, Commerce International - choisissez votre spécialisation et formez-vous avec les meilleurs experts du terrain CEMAC.</p>
        </div>
    </div>
</section>

{{-- Filtres --}}
<section class="bg-white dark:bg-surface-dark border-b dark:border-gray-800 sticky top-[5.5rem] z-30">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-4">
        <form method="GET" action="{{ route('formations.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Domaine :</label>
                <select name="categorie" onchange="this.form.submit()" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    <option value="">Toutes</option>
                    <option value="logistique" {{ request('categorie') == 'logistique' ? 'selected' : '' }}>Logistique & Supply Chain</option>
                    <option value="douane" {{ request('categorie') == 'douane' ? 'selected' : '' }}>Douane & Transit</option>
                    <option value="commerce" {{ request('categorie') == 'commerce' ? 'selected' : '' }}>Commerce International</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Niveau :</label>
                <select name="niveau" onchange="this.form.submit()" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    <option value="">Tous</option>
                    <option value="debutant" {{ request('niveau') == 'debutant' ? 'selected' : '' }}>Débutant</option>
                    <option value="intermediaire" {{ request('niveau') == 'intermediaire' ? 'selected' : '' }}>Intermédiaire</option>
                    <option value="avance" {{ request('niveau') == 'avance' ? 'selected' : '' }}>Avancé</option>
                </select>
            </div>
            @if(request('categorie') || request('niveau'))
            <a href="{{ route('formations.index') }}" class="text-sm text-gray-500 hover:text-red-500 transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Réinitialiser
            </a>
            @endif
        </form>
    </div>
</section>

{{-- Grille formations --}}
<section class="w-full py-10 lg:py-16 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-7xl mx-auto px-4 lg:px-6">
        @if($formations->isEmpty())
        <div class="text-center py-20">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <h3 class="font-heading text-xl font-bold text-gray-700 dark:text-gray-300 mt-4">Aucune formation trouvée</h3>
            <p class="text-gray-500 mt-2">Essayez de modifier vos filtres.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($formations as $formation)
            <div class="bg-white dark:bg-surface-dark rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col group">
                @php
                    $imagesParCategorie = [
                        'logistique' => ['african-american-people-looking-supplies-list-pc-working-with-computer-check-inventory-logistics-employees-organizing-merchandise-shelves-warehouse-space-handheld-shot.jpg', 'global-logistics-transportation-network.jpg'],
                        'commerce' => ['aerial-view-cargo-ship-cargo-container-harbor (1).jpg', 'businessman-touching-virtual-screen.jpg'],
                        'douane' => ['industrial-port-container-yard.jpg', 'freight-trucks-driving-highway-logistics-road-transport-content.jpg'],
                    ];
                    $fallbackImg = $imagesParCategorie[$formation->categorie][$loop->index % 2] ?? '6206720.jpg';
                @endphp
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
                        @if($formation->promo_active)
                        <span class="px-2 py-1 rounded-full bg-red-500 text-white text-xs font-bold uppercase shadow">Promo</span>
                        @endif
                        <span class="px-2 py-1 rounded-full bg-primary-700/80 backdrop-blur-md text-white text-xs">{{ $formation->duree_heures ?? '?' }}h &bull; {{ $formation->modules_count ?? $formation->modules->count() }} modules</span>
                    </div>
                    <div class="absolute top-3 right-3">
                        <span class="px-2 py-1 rounded-full bg-white/90 dark:bg-gray-900/90 text-xs font-semibold text-primary-700 dark:text-gray-300 capitalize">{{ $formation->niveau }}</span>
                    </div>
                </div>
                <div class="p-5 flex flex-col flex-1 justify-between gap-4">
                    <div class="flex flex-col gap-2">
                        <span class="text-xs font-bold text-accent-500 uppercase">{{ $formation->categorie }}</span>
                        <h3 class="font-heading text-lg font-bold text-primary-700 dark:text-white line-clamp-2">{{ $formation->titre }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ Str::limit($formation->description, 120) }}</p>
                    </div>
                    <div class="flex flex-col gap-3 pt-2">
                        <div class="flex items-center justify-between">
                            @if($formation->promo_active && $formation->prix_promo)
                            <span class="text-sm text-gray-400 line-through">{{ number_format($formation->prix, 0, ',', ' ') }} FCFA</span>
                            @else
                            <span class="text-xs text-gray-500">Certification incluse</span>
                            @endif
                            <span class="font-heading text-xl font-bold text-primary-700 dark:text-white">{{ number_format($formation->prix_effectif, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-500">FCFA</span></span>
                        </div>
                        <a href="{{ route('formations.show', $formation) }}" class="w-full py-2.5 rounded-lg bg-primary-700 text-white text-sm font-bold hover:bg-primary-500 transition-colors flex items-center justify-center gap-1">
                            <span>Voir le programme</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $formations->links() }}
        </div>
        @endif
    </div>
</section>
@endsection
