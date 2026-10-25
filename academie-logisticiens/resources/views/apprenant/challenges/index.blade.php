@extends('layouts.apprenant')
@section('page-title', 'Challenges CTF')

@section('content')
<div class="relative bg-gradient-to-br from-[#0B1E36] to-[#1B3A5C] text-white rounded-2xl overflow-hidden p-6 mb-6">
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/thumbs/black-employee-works-with-ai-tools-enhance-package-tracking-efficiency.jpg') }}" alt="" class="w-full h-full object-cover opacity-10">
    </div>
    <div class="relative z-10">
        <h2 class="font-heading text-xl font-bold">Arene des Parcours CTF</h2>
        <p class="text-sm text-gray-300 mt-1 max-w-2xl">Chaque parcours est une aventure en plusieurs etapes. Chaque etape aborde un theme : repondez aux questions (70% minimum) pour <strong class="text-accent-400">capturer le drapeau</strong>, debloquer l'etape suivante et gagner des points.</p>
    </div>
</div>

@forelse($parcours as $domaine => $items)
<div class="mb-8">
    <h3 class="font-heading text-lg font-bold text-primary-700 dark:text-white mb-3 capitalize">{{ str_replace('_', ' ', $domaine) }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($items as $p)
        @php
            $pct = $p->total_etapes > 0 ? round($p->etapes_capturees / $p->total_etapes * 100) : 0;
            $fini = $p->total_etapes > 0 && $p->etapes_capturees >= $p->total_etapes;
        @endphp
        <a href="{{ route('apprenant.challenges.parcours', $p) }}" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0B1E36] to-[#1B3A5C] text-white p-5 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition">
            <div class="flex items-center justify-between mb-3">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-white/10 text-accent-400">{{ $p->niveau }}</span>
                @if($fini)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-accent-500 text-primary-800">Termine</span>
                @endif
            </div>
            <h4 class="font-heading font-bold group-hover:text-accent-400 transition">{{ $p->titre }}</h4>
            <p class="text-xs text-white/60 mt-1 line-clamp-2">{{ $p->description }}</p>
            <div class="mt-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-accent-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/></svg>
                <span class="text-xs font-bold">{{ $p->etapes_capturees }}/{{ $p->total_etapes }} drapeaux</span>
            </div>
            <div class="mt-2 h-2 rounded-full bg-white/10 overflow-hidden"><div class="h-full bg-gradient-to-r from-accent-500 to-yellow-300" style="width: {{ $pct }}%"></div></div>
        </a>
        @endforeach
    </div>
</div>
@empty
<div class="bg-white dark:bg-surface-dark rounded-xl p-8 text-center shadow-sm">
    <p class="text-gray-500">Aucun parcours de challenges disponible pour le moment.</p>
</div>
@endforelse
@endsection
