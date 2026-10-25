@extends('layouts.apprenant')
@section('page-title', $parcours->titre)

@section('content')
@php
    $total = $etapes->count();
    $captures = $etapes->where('capturee', true)->count();
    $pct = $total > 0 ? round($captures / $total * 100) : 0;
    $totalPoints = $etapes->sum('points');
    $offsets = ['md:-translate-x-24', 'md:translate-x-24'];
    $courant = $etapes->first(fn($e) => !$e->capturee);
@endphp

<style>
    @keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(207,171,90,.7); } 70% { box-shadow: 0 0 0 22px rgba(207,171,90,0); } 100% { box-shadow: 0 0 0 0 rgba(207,171,90,0); } }
    @keyframes float-y { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
    .node-current { animation: pulse-ring 1.8s infinite; }
    .flag-float { animation: float-y 2.4s ease-in-out infinite; }
</style>

<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="{{ route('apprenant.challenges.index') }}" class="hover:text-primary-700 dark:hover:text-accent-400 transition">Challenges</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-primary-700 dark:text-accent-400">{{ $parcours->titre }}</span>
</div>

@if(session('error'))
<div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
@endif

<div class="rounded-3xl overflow-hidden bg-gradient-to-b from-[#0B1E36] via-[#12304f] to-[#0B1E36] text-white shadow-xl">
    {{-- HUD --}}
    <div class="p-6 border-b border-white/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-accent-400">{{ $parcours->domaine }} &bull; {{ $parcours->niveau }}</span>
            <h2 class="font-heading text-2xl font-bold mt-1">{{ $parcours->titre }}</h2>
            @if($parcours->description)<p class="text-sm text-white/60 mt-1 max-w-xl">{{ $parcours->description }}</p>@endif
        </div>
        <div class="flex items-center gap-5 shrink-0">
            <div class="text-center">
                <p class="text-2xl font-bold text-accent-400">{{ $captures }}<span class="text-white/40 text-base">/{{ $total }}</span></p>
                <p class="text-[10px] uppercase tracking-wider text-white/50">Drapeaux</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-accent-400">{{ $totalPoints }}</p>
                <p class="text-[10px] uppercase tracking-wider text-white/50">Points a gagner</p>
            </div>
            <div class="w-24">
                <div class="h-3 rounded-full bg-white/10 overflow-hidden"><div class="h-full bg-gradient-to-r from-accent-500 to-yellow-300 rounded-full transition-all" style="width: {{ $pct }}%"></div></div>
                <p class="text-[10px] text-center text-white/50 mt-1">{{ $pct }}%</p>
            </div>
        </div>
    </div>

    {{-- Parcours --}}
    <div class="relative px-4 py-10 max-w-2xl mx-auto">
        <div class="absolute left-1/2 top-10 bottom-10 -translate-x-1/2 border-l-4 border-dashed border-white/15"></div>

        <div class="relative flex flex-col items-center mb-10">
            <div class="w-16 h-16 rounded-full bg-emerald-500/20 border-2 border-emerald-400 flex items-center justify-center">
                <svg class="w-7 h-7 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
            </div>
            <span class="mt-2 text-[11px] font-bold uppercase tracking-widest text-emerald-400">Depart</span>
        </div>

        @forelse($etapes as $i => $etape)
        @php
            $etat = $etape->capturee ? 'done' : (!$etape->debloquee ? 'locked' : (!$etape->jouable ? 'soon' : 'current'));
            $estCourant = $courant && $courant->id === $etape->id;
        @endphp
        <div class="relative flex flex-col items-center mb-12 {{ $offsets[$i % 2] }} transition">
            @if($etat === 'done')
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-accent-400 to-yellow-600 border-4 border-yellow-200 flex items-center justify-center shadow-[0_0_35px_rgba(207,171,90,.55)]">
                <svg class="w-11 h-11 text-primary-800 flag-float" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/></svg>
            </div>
            @elseif($etat === 'current')
            <a href="{{ route('apprenant.challenges.etape', $etape) }}" class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 border-4 {{ $estCourant ? 'border-accent-400 node-current' : 'border-white/40' }} flex items-center justify-center hover:scale-110 transition">
                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
            </a>
            @else
            <div class="w-24 h-24 rounded-full bg-white/5 border-4 border-white/15 flex items-center justify-center">
                <svg class="w-9 h-9 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            @endif

            <div class="mt-3 w-64 text-center rounded-2xl p-4 {{ $etat === 'done' ? 'bg-accent-500/10 border border-accent-500/40' : ($etat === 'current' ? 'bg-white/10 border border-white/30' : 'bg-white/5 border border-white/10 opacity-70') }}">
                <span class="text-[10px] font-bold uppercase tracking-widest {{ $etat === 'done' ? 'text-accent-400' : 'text-white/50' }}">Etape {{ $i + 1 }}</span>
                <h3 class="font-heading font-bold mt-0.5">{{ $etape->titre }}</h3>
                @if($etape->theme)<p class="text-xs text-white/60 mt-0.5">Theme : {{ $etape->theme }}</p>@endif
                <p class="text-[11px] text-white/50 mt-1">{{ min($etape->questions_count, max(10, $etape->nb_questions)) }} questions &bull; +{{ $etape->points }} pts</p>

                @if($etat === 'done')
                <p class="mt-2 text-xs font-bold text-accent-400">Drapeau capture</p>
                <a href="{{ route('apprenant.challenges.etape', $etape) }}" class="text-[11px] text-white/50 hover:text-white underline">Rejouer</a>
                @elseif($etat === 'current')
                <a href="{{ route('apprenant.challenges.etape', $etape) }}" class="mt-3 inline-block px-5 py-2 rounded-lg bg-accent-500 text-primary-800 text-sm font-bold hover:bg-accent-400 transition">{{ $estCourant ? 'Jouer' : 'Rejouer' }}</a>
                @elseif($etat === 'soon')
                <p class="mt-2 text-xs text-white/40">Bientot disponible</p>
                @else
                <p class="mt-2 text-xs text-white/40">Capturez le drapeau precedent</p>
                @endif
            </div>
        </div>
        @empty
        <p class="relative text-center text-white/50 py-10">Aucune etape dans ce parcours pour le moment.</p>
        @endforelse

        <div class="relative flex flex-col items-center">
            <div class="w-20 h-20 rounded-full flex items-center justify-center border-4 {{ $termine ? 'bg-gradient-to-br from-accent-400 to-yellow-600 border-yellow-200 shadow-[0_0_45px_rgba(207,171,90,.7)]' : 'bg-white/5 border-white/15' }}">
                <svg class="w-10 h-10 {{ $termine ? 'text-primary-800' : 'text-white/30' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h8V3a1 1 0 112 0v1h1a1 1 0 011 1v2a4 4 0 01-3.5 3.97A4.002 4.002 0 0111 13.9V16h2a1 1 0 110 2H7a1 1 0 110-2h2v-2.1A4.002 4.002 0 014.5 10.97 4 4 0 011 7V5a1 1 0 011-1h1V3a1 1 0 011-1zM3 6v1a2 2 0 001.5 1.94V6H3zm13.5 0v2.94A2 2 0 0018 7V6h-1.5z" clip-rule="evenodd"/></svg>
            </div>
            <span class="mt-2 text-[11px] font-bold uppercase tracking-widest {{ $termine ? 'text-accent-400' : 'text-white/40' }}">{{ $termine ? 'Parcours termine !' : 'Arrivee' }}</span>
        </div>
    </div>
</div>
@endsection
