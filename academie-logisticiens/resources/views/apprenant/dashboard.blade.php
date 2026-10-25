@extends('layouts.apprenant')
@section('page-title', 'Tableau de bord')

@section('content')
@if(\App\Support\ProfilOptions::profilIncomplet($user))
<a href="{{ route('profile.edit') }}" class="mb-4 flex items-center gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 transition">
    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
    <p class="text-sm text-amber-800 dark:text-amber-300"><strong>Complétez votre profil</strong> : date et lieu de naissance, situation (étudiant / professionnel). Ces informations figurent sur votre certificat.</p>
</a>
@endif

{{-- Welcome banner --}}
<div class="relative bg-primary-700 rounded-2xl overflow-hidden mb-6">
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/thumbs/business-logistics-concept-container-cargo.jpg') }}" alt="" class="w-full h-full object-cover opacity-15">
    </div>
    <div class="relative z-10 p-6 flex items-center gap-4">
        <div class="w-14 h-14 rounded-xl bg-accent-500 text-primary-700 flex items-center justify-center font-bold text-xl shrink-0">{{ strtoupper(substr($user->prenom ?? $user->name, 0, 1)) }}</div>
        <div class="text-white">
            <h2 class="font-heading text-xl font-bold">Bienvenue, {{ $user->prenom ?? $user->name }} !</h2>
            <p class="text-sm text-gray-300 mt-0.5">Continuez votre progression et gagnez des récompenses.</p>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-surface-dark rounded-xl p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-700/20 text-primary-700 dark:text-accent-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-primary-700 dark:text-white">{{ $user->points }}</p>
                <p class="text-xs text-gray-500">Points</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-surface-dark rounded-xl p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-accent-400/10 text-accent-500 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-primary-700 dark:text-white">{{ number_format($user->jetons) }}</p>
                <p class="text-xs text-gray-500">Jetons FCFA</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-surface-dark rounded-xl p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-primary-700 dark:text-white">{{ $certificatsCount }}</p>
                <p class="text-xs text-gray-500">Certificats</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-surface-dark rounded-xl p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-primary-700 dark:text-white">{{ $flagsCount }}</p>
                <p class="text-xs text-gray-500">Flags capturés</p>
            </div>
        </div>
    </div>
</div>

{{-- Reprendre progression --}}
@if($reprise && $reprise['status']['next_lecon'])
<div class="bg-primary-700 rounded-xl p-5 mb-6 flex flex-col md:flex-row items-center justify-between gap-4 text-white">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-accent-500/20 text-accent-400 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="font-semibold">Reprendre votre cours</p>
            <p class="text-sm text-gray-300">{{ $reprise['status']['next_lecon']->titre }} - {{ $reprise['formation']->titre }}</p>
        </div>
    </div>
    <a href="{{ $reprise['url'] }}" class="px-5 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition shrink-0">
        Continuer
    </a>
</div>
@endif

{{-- Mes formations --}}
<h2 class="font-heading text-xl font-bold text-primary-700 dark:text-white mb-4">Mes formations</h2>
@if($cartes->isEmpty())
<div class="bg-white dark:bg-surface-dark rounded-xl p-8 text-center shadow-sm">
    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
    <p class="text-gray-500 mt-3">Vous n'avez pas encore de formation active.</p>
    <a href="{{ route('formations.index') }}" class="inline-block mt-4 px-6 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition">Explorer le catalogue</a>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($cartes as $carte)
    @php
        $f = $carte['formation'];
        $pct = $carte['status']['pct'];
        $termine = $pct >= 100;
    @endphp
    <a href="{{ $carte['url'] }}" class="group bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition block">
        <div class="relative h-36 overflow-hidden bg-gradient-to-br from-primary-700 to-primary-800">
            <img src="{{ $f->image_url }}" loading="lazy" decoding="async" alt="{{ $f->titre }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">

            <div class="absolute inset-0 bg-gradient-to-t from-primary-900/80 to-transparent"></div>
            <span class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-accent-500 text-primary-700 text-[10px] font-bold uppercase">{{ $f->categorie }}</span>
            @if($termine)
            <span class="absolute top-3 right-3 px-2 py-0.5 rounded-full bg-emerald-500 text-white text-[10px] font-bold">Terminée</span>
            @endif
            <div class="absolute bottom-3 left-3 right-3 text-white">
                <h3 class="font-heading font-bold leading-tight line-clamp-2">{{ $f->titre }}</h3>
            </div>
        </div>
        <div class="p-4">
            <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="text-gray-500">{{ $carte['status']['done'] }}/{{ $carte['status']['total'] }} leçons</span>
                <span class="font-bold {{ $termine ? 'text-emerald-600' : 'text-accent-600' }}">{{ $pct }}%</span>
            </div>
            <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                <div class="h-full rounded-full {{ $termine ? 'bg-emerald-500' : 'bg-accent-500' }}" style="width: {{ $pct }}%"></div>
            </div>
            <p class="mt-3 text-sm font-semibold text-primary-700 dark:text-accent-400 flex items-center gap-1">
                {{ $pct === 0 ? 'Commencer' : ($termine ? 'Revoir le cours' : 'Continuer') }}
                <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </p>
        </div>
    </a>
    @endforeach
</div>
@endif

{{-- Notifications --}}
@if($notificationsNonLues > 0)
<div class="mt-6 bg-accent-500/10 dark:bg-accent-500/5 rounded-xl p-4 border border-accent-500/20 flex items-center gap-3">
    <svg class="w-5 h-5 text-accent-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>
    <p class="text-sm text-gray-700 dark:text-gray-300">Vous avez <strong>{{ $notificationsNonLues }}</strong> notification(s) non lue(s).</p>
</div>
@endif
@endsection
