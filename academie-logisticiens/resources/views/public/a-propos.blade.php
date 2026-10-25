@extends('layouts.public')
@section('title', 'À Propos - ' . $siteTitre)

@section('content')
<div class="pt-28"></div>

{{-- ═══════════════════ HERO À PROPOS ═══════════════════ --}}
<section class="relative bg-primary-700 text-white py-16 lg:py-24 overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/thumbs/global-logistics-transportation-network.jpg') }}" alt="" class="w-full h-full object-cover opacity-15">
    </div>
    <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-accent-400/10 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 right-1/4 w-[32rem] h-[32rem] rounded-full bg-emerald-400/10 blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 lg:px-6 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="flex flex-col gap-6">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-accent-400 max-w-fit text-xs font-bold tracking-wider uppercase">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/></svg>
                    A propos du cabinet
                </span>
                <h1 class="font-heading text-3xl md:text-5xl font-bold tracking-tight leading-tight">
                    Se faire former <span class="text-accent-400">partout en Afrique</span>
                </h1>
                <p class="text-lg text-gray-300 max-w-2xl">
                    {{ $siteTitre }} est un cabinet international d'expertise et de formation base a Brazzaville, specialise dans la logistique, le transport, le commerce international, la douane et le transit.
                </p>
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <a href="{{ route('formations.index') }}" class="px-6 py-3.5 rounded-lg bg-accent-500 text-primary-700 font-bold shadow-lg hover:bg-accent-400 transition-all flex items-center gap-2 group">
                        <span>Decouvrir nos formations</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="{{ route('contact') }}" class="px-5 py-3.5 rounded-lg bg-white/10 text-white hover:bg-white/20 transition-all font-medium flex items-center gap-2 backdrop-blur-sm">
                        Nous contacter
                    </a>
                </div>
            </div>
            <div class="hidden lg:flex justify-center">
                <div class="relative w-72 h-72">
                    <div class="absolute inset-0 rounded-full border-2 border-dashed border-white/20 animate-spin" style="animation-duration: 20s;"></div>
                    <div class="absolute inset-8 rounded-full border border-white/10"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <img src="{{ asset('assets/logo/logo-192.jpg') }}" alt="Logo ACLOG" class="w-24 h-24 rounded-full object-contain bg-white p-2 shadow-xl">
                    </div>
                    {{-- Orbit icons --}}
                    <span class="absolute w-10 h-10 rounded-full bg-white/10 backdrop-blur-sm flex items-center justify-center text-white" style="top: -8px; left: 50%; transform: translateX(-50%);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </span>
                    <span class="absolute w-10 h-10 rounded-full bg-white/10 backdrop-blur-sm flex items-center justify-center text-white" style="top: 50%; right: -8px; transform: translateY(-50%);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                    </span>
                    <span class="absolute w-10 h-10 rounded-full bg-white/10 backdrop-blur-sm flex items-center justify-center text-white" style="bottom: -8px; left: 50%; transform: translateX(-50%);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </span>
                    <span class="absolute w-10 h-10 rounded-full bg-white/10 backdrop-blur-sm flex items-center justify-center text-white" style="top: 50%; left: -8px; transform: translateY(-50%);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════ STATISTIQUES ═══════════════════ --}}
<section class="w-full bg-white dark:bg-surface-dark shadow-sm py-8 relative z-20">
    <div class="max-w-7xl mx-auto px-4 lg:px-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-10">
            @php
                $stats_apropos = [
                    ['svg' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/></svg>', 'valeur' => '40', 'label' => 'formations certifiantes'],
                    ['svg' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>', 'valeur' => '4', 'label' => "filieres d'expertise"],
                    ['svg' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.42 15.17l-5.1-5.1m0 0L11.42 4.96m-5.1 5.11h13.36M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.42 15.17l-5.1-5.1"/></svg>', 'valeur' => '100%', 'label' => 'orientation pratique'],
                    ['svg' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25z"/></svg>', 'valeur' => '2', 'label' => 'modes : presentiel & en ligne'],
                ];
            @endphp
            @foreach($stats_apropos as $stat)
            <div class="flex flex-col items-center text-center gap-2 p-4">
                <div class="w-12 h-12 rounded-xl bg-accent-400/15 text-accent-500 flex items-center justify-center">
                    {!! $stat['svg'] !!}
                </div>
                <span class="font-heading text-3xl font-bold text-primary-700 dark:text-white">{{ $stat['valeur'] }}</span>
                <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $stat['label'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════ QUI SOMMES-NOUS ═══════════════════ --}}
<section class="py-12 lg:py-20 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-6xl mx-auto px-4 lg:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div class="flex flex-col gap-6">
                <span class="text-xs font-bold text-accent-500 uppercase tracking-wider">Qui sommes-nous ?</span>
                <h2 class="font-heading text-2xl md:text-3xl font-bold text-primary-700 dark:text-white">Un cabinet au service de la competence</h2>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                    Depuis sa creation, le cabinet accompagne etudiants, professionnels et entreprises dans l'acquisition de competences operationnelles reconnues sur le marche du travail.
                </p>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                    Porte par une equipe de formateurs experimentes issus du monde professionnel, il concoit des parcours certifiants qui allient rigueur theorique et cas pratiques, en presentiel comme a distance, pour repondre aux besoins concrets des secteurs du transport aerien, terrestre et maritime.
                </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @php
                    $cards = [
                        ['color' => 'bg-blue-500', 'svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>', 'titre' => 'Mission', 'desc' => "Former des logisticiens competents et operationnels, capables de repondre aux exigences du commerce international et du transport moderne."],
                        ['color' => 'bg-purple-500', 'svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', 'titre' => 'Vision', 'desc' => "Devenir la reference des formations certifiantes en logistique et transport en Afrique centrale."],
                        ['color' => 'bg-red-500', 'svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>', 'titre' => 'Valeurs', 'desc' => "Competence, rigueur, integrite et accompagnement de proximite - « la competence, rien que la competence »."],
                        ['color' => 'bg-teal-500', 'svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>', 'titre' => 'Methode', 'desc' => "Cours en ligne et en presentiel, ateliers pratiques, hackathons et accompagnement individualise."],
                    ];
                @endphp
                @foreach($cards as $card)
                <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 shadow-sm border-t-4 {{ $card['color'] }}/80 flex flex-col gap-3">
                    <div class="w-10 h-10 rounded-lg {{ $card['color'] }}/15 text-primary-700 dark:text-white flex items-center justify-center">
                        {!! $card['svg'] !!}
                    </div>
                    <h3 class="font-heading font-bold text-primary-700 dark:text-white">{{ $card['titre'] }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $card['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════ DEVISE / MOTTO ═══════════════════ --}}
<section class="py-12 lg:py-16 bg-primary-700 text-white relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/thumbs/industrial-port-container-yard.jpg') }}" alt="" class="w-full h-full object-cover opacity-10">
    </div>
    <div class="max-w-4xl mx-auto px-4 lg:px-6 text-center relative z-10 flex flex-col items-center gap-4">
        <span class="text-6xl text-accent-400 font-heading font-bold leading-none">"</span>
        <blockquote class="font-heading text-2xl md:text-4xl font-bold italic -mt-6">
            La competence, rien que la competence
        </blockquote>
        <p class="text-gray-300 text-lg">La devise qui guide chacune de nos formations.</p>
    </div>
</section>

{{-- ═══════════════════ DIRECTEUR / EXPERT ═══════════════════ --}}
<section class="py-12 lg:py-20 bg-white dark:bg-surface-dark">
    <div class="max-w-6xl mx-auto px-4 lg:px-6">
        <div class="text-center mb-10">
            <h2 class="font-heading text-2xl md:text-3xl font-bold text-primary-700 dark:text-white">La direction du cabinet</h2>
        </div>
        <div class="bg-surface-light dark:bg-surface-darker rounded-2xl p-6 lg:p-10 shadow-sm">
            <div class="flex flex-col lg:flex-row items-center gap-10">
                <div class="relative shrink-0">
                    <img src="{{ asset('assets/images/pdg-aclog.jpeg') }}" alt="Didi-Chance AMBIA" class="w-56 h-56 lg:w-72 lg:h-72 rounded-2xl object-cover shadow-lg">
                    <span class="absolute -top-3 -right-3 px-3 py-1.5 rounded-full bg-accent-500 text-primary-700 text-xs font-bold shadow-lg uppercase tracking-wider">
                        Expert CEMAC
                    </span>
                </div>
                <div class="flex flex-col gap-4 text-center lg:text-left">
                    <div>
                        <span class="inline-block px-3 py-1 rounded-full bg-primary-700 text-accent-400 text-xs font-bold uppercase tracking-wider mb-2">Directeur du Cabinet ACLOG</span>
                        <h3 class="font-heading text-2xl md:text-3xl font-bold text-primary-700 dark:text-white">Didi-Chance AMBIA</h3>
                        <p class="text-accent-500 font-semibold">Expert CEMAC en Gestion logistique et Transport</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Directeur du {{ $siteTitre }}, Brazzaville</p>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Expert CEMAC en gestion logistique et transport, Didi-Chance AMBIA dirige le Cabinet ACLOG, cabinet international d'expertise et de formation dedie a la logistique, au transport, au commerce international, a la douane et au transit.
                    </p>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Fidele a la devise du cabinet, « la competence, rien que la competence », il porte une vision exigeante de la formation professionnelle : des parcours certifiants, orientes pratique, concus pour repondre aux besoins reels des entreprises et des professionnels de la sous-region.
                    </p>
                    <div class="flex flex-wrap gap-2 justify-center lg:justify-start">
                        <span class="px-3 py-1.5 rounded-full bg-primary-50 dark:bg-gray-800 text-primary-700 dark:text-accent-400 text-xs font-semibold">Expert CEMAC</span>
                        <span class="px-3 py-1.5 rounded-full bg-primary-50 dark:bg-gray-800 text-primary-700 dark:text-accent-400 text-xs font-semibold">Gestion logistique</span>
                        <span class="px-3 py-1.5 rounded-full bg-primary-50 dark:bg-gray-800 text-primary-700 dark:text-accent-400 text-xs font-semibold">Transport aerien, terrestre et maritime</span>
                        <span class="px-3 py-1.5 rounded-full bg-primary-50 dark:bg-gray-800 text-primary-700 dark:text-accent-400 text-xs font-semibold">Formations certifiantes</span>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-accent-500 text-primary-700 font-bold shadow-lg hover:bg-accent-400 transition-all group">
                            <span>Prendre contact</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════ VOTRE PARCOURS ═══════════════════ --}}
<section class="py-12 lg:py-20 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-6xl mx-auto px-4 lg:px-6">
        <div class="text-center mb-10">
            <h2 class="font-heading text-2xl md:text-3xl font-bold text-primary-700 dark:text-white">Votre parcours chez ACLOG</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $etapes = [
                    ['num' => '1', 'svg' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>', 'titre' => 'Choisissez votre formation', 'desc' => "Parmi 40 formations certifiantes en logistique, transport, commerce international, douane et transit."],
                    ['num' => '2', 'svg' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>', 'titre' => 'Inscrivez-vous et payez', 'desc' => "Reglez par MTN Mobile Money ou Airtel Money, puis confirmez votre paiement sur WhatsApp."],
                    ['num' => '3', 'svg' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>', 'titre' => 'Formez-vous pendant 2 mois', 'desc' => "Cours en ligne et en presentiel, avec des cas pratiques et des ateliers de mise en situation."],
                    ['num' => '4', 'svg' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0016.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.003 6.003 0 01-3.77 1.522m0 0a6.003 6.003 0 01-3.77-1.522"/></svg>', 'titre' => 'Recevez votre certificat', 'desc' => "Un certificat officiel avec QR code de verification, reconnu dans la zone CEMAC."],
                ];
            @endphp
            @foreach($etapes as $etape)
            <div class="bg-white dark:bg-surface-dark rounded-2xl p-6 shadow-sm flex flex-col items-center text-center gap-4 relative">
                <span class="absolute -top-4 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-accent-500 text-primary-700 flex items-center justify-center text-sm font-bold shadow">{{ $etape['num'] }}</span>
                <div class="w-14 h-14 rounded-xl bg-accent-400/15 text-accent-500 flex items-center justify-center mt-2">
                    {!! $etape['svg'] !!}
                </div>
                <h3 class="font-heading font-bold text-primary-700 dark:text-white">{{ $etape['titre'] }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $etape['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════ EQUIPE PEDAGOGIQUE ═══════════════════ --}}
<section class="py-12 lg:py-20 bg-white dark:bg-surface-dark">
    <div class="max-w-6xl mx-auto px-4 lg:px-6">
        <div class="text-center mb-10">
            <h2 class="font-heading text-2xl md:text-3xl font-bold text-primary-700 dark:text-white">Notre equipe pedagogique</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
                $equipe = [
                    ['color' => 'border-blue-500', 'svg' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', 'titre' => 'Formateurs en logistique', 'desc' => "Experts en gestion des stocks, approvisionnement et supply chain management, issus d'entreprises du secteur."],
                    ['color' => 'border-teal-500', 'svg' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>', 'titre' => 'Formateurs en transport', 'desc' => "Specialistes du transport aerien, terrestre et maritime, familiers des reglementations et des vecteurs de transport."],
                    ['color' => 'border-red-500', 'svg' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>', 'titre' => 'Formateurs en douane', 'desc' => "Praticiens des procedures de dedouanement, du commerce international et du transit inter-Etats."],
                ];
            @endphp
            @foreach($equipe as $membre)
            <div class="bg-surface-light dark:bg-surface-darker rounded-2xl p-6 shadow-sm border-l-4 {{ $membre['color'] }} flex flex-col gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-gray-800 text-primary-700 dark:text-accent-400 flex items-center justify-center">
                    {!! $membre['svg'] !!}
                </div>
                <h3 class="font-heading font-bold text-primary-700 dark:text-white">{{ $membre['titre'] }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $membre['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════ ANNUAIRE DES CERTIFIÉS ═══════════════════ --}}
<section class="py-12 lg:py-20 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-6xl mx-auto px-4 lg:px-6">
        <div class="text-center mb-10">
            <span class="text-xs font-bold text-accent-500 uppercase tracking-wider">Annuaire des certifies</span>
            <h2 class="font-heading text-2xl md:text-3xl font-bold text-primary-700 dark:text-white mt-2">Nos etudiants certifies</h2>
            <p class="text-gray-500 mt-2 max-w-xl mx-auto">Des professionnels formes et certifies par le Cabinet ACLOG, operationnels sur le marche.</p>
        </div>
        <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 lg:p-12 shadow-sm text-center">
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 rounded-2xl bg-accent-400/15 text-accent-500 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/></svg>
                </div>
            </div>
            <div x-data="{ count: 0, target: {{ $totalCertifies ?? 0 }} }" x-init="
                let start = 0;
                let duration = 2000;
                let startTime = null;
                function animate(timestamp) {
                    if (!startTime) startTime = timestamp;
                    let progress = Math.min((timestamp - startTime) / duration, 1);
                    count = Math.floor(progress * target);
                    if (progress < 1) requestAnimationFrame(animate);
                }
                requestAnimationFrame(animate);
            " class="mb-6">
                <span class="font-heading text-5xl md:text-6xl font-bold text-primary-700 dark:text-white" x-text="count">0</span>
                <p class="text-gray-500 font-medium mt-2">etudiants certifies a ce jour</p>
            </div>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('annuaire-certifies') }}" class="px-6 py-3 rounded-lg bg-accent-500 text-primary-700 font-bold shadow-lg hover:bg-accent-400 transition-all flex items-center gap-2 group">
                    <span>Voir l'annuaire</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ route('annuaire-certifies') }}?code=" class="px-6 py-3 rounded-lg bg-primary-700 text-white font-semibold hover:bg-primary-600 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Verifier un certificat</span>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════ CTA ═══════════════════ --}}
<section class="py-12 lg:py-16 bg-primary-700 text-white relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/thumbs/aerial-view-cargo-ship-cargo-container-harbor.jpg') }}" alt="" class="w-full h-full object-cover opacity-10">
    </div>
    <div class="max-w-4xl mx-auto px-4 lg:px-6 text-center flex flex-col gap-6 items-center relative z-10">
        <h2 class="font-heading text-2xl md:text-3xl font-bold">Pret a faire avancer votre carriere ?</h2>
        <p class="text-gray-300 max-w-xl">Rejoignez {{ $siteTitre }} et decrochez votre certificat.</p>
        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('formations.index') }}" class="px-8 py-4 rounded-lg bg-accent-500 text-primary-700 font-bold text-lg shadow-lg hover:bg-accent-400 transition-all">
                Voir le Catalogue
            </a>
            <a href="{{ route('contact') }}" class="px-8 py-4 rounded-lg bg-white/10 text-white font-medium text-lg hover:bg-white/20 transition-all backdrop-blur-sm">
                Nous Contacter
            </a>
        </div>
    </div>
</section>
@endsection
