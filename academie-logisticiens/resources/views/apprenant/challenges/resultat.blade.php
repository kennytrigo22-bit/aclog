@extends('layouts.apprenant')
@section('page-title', $reussi ? 'Drapeau capture !' : 'Etape non validee')

@section('content')
<style>
    @keyframes flag-in { 0% { transform: scale(.2) rotate(-25deg); opacity: 0; } 60% { transform: scale(1.2) rotate(6deg); opacity: 1; } 100% { transform: scale(1) rotate(0); } }
    .flag-in { animation: flag-in .9s ease-out both; }
</style>
<div class="max-w-lg mx-auto text-center">
    <div class="rounded-3xl p-8 shadow-xl text-white {{ $reussi ? 'bg-gradient-to-b from-[#0B1E36] to-[#1B3A5C]' : 'bg-white dark:bg-surface-dark !text-gray-800 dark:!text-white' }}">
        @if($reussi)
        <div class="w-28 h-28 rounded-full mx-auto bg-gradient-to-br from-accent-400 to-yellow-600 border-4 border-yellow-200 flex items-center justify-center shadow-[0_0_50px_rgba(207,171,90,.7)] flag-in">
            <svg class="w-14 h-14 text-primary-800" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/></svg>
        </div>
        <h2 class="font-heading text-2xl font-bold mt-5">Drapeau capture !</h2>
        <p class="text-accent-400 font-mono text-sm mt-2 break-all">{{ $challenge->flag_attendu }}</p>
        <p class="text-white/70 mt-3">{{ $correct }}/{{ $total }} bonnes reponses ({{ $score }}%)</p>
        @if($points > 0)<p class="mt-1 text-accent-400 font-bold">+{{ $points }} points</p>@endif
        @else
        <div class="w-24 h-24 rounded-full mx-auto bg-red-100 dark:bg-red-900/30 text-red-500 flex items-center justify-center text-3xl font-bold">{{ $score }}%</div>
        <h2 class="font-heading text-2xl font-bold mt-5">Etape non validee</h2>
        <p class="text-gray-500 mt-2">{{ $correct }}/{{ $total }} bonnes reponses. Il faut au moins {{ $seuil }}% pour capturer le drapeau.</p>
        <p class="text-sm text-gray-400 mt-1">Pas de souci : les questions changent a chaque tentative.</p>
        @endif

        <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
            @if($reussi && $suivante)
            <a href="{{ route('apprenant.challenges.parcours', $challenge->parcours) }}" class="px-5 py-2.5 rounded-lg bg-accent-500 text-primary-800 font-bold hover:bg-accent-400 transition">Etape suivante : {{ $suivante->titre }}</a>
            @elseif($reussi)
            <a href="{{ route('apprenant.challenges.parcours', $challenge->parcours) }}" class="px-5 py-2.5 rounded-lg bg-accent-500 text-primary-800 font-bold hover:bg-accent-400 transition">Voir le parcours</a>
            @else
            <a href="{{ route('apprenant.challenges.etape', $challenge) }}" class="px-5 py-2.5 rounded-lg bg-primary-700 text-white font-bold hover:bg-primary-600 transition">Reessayer</a>
            <a href="{{ route('apprenant.challenges.parcours', $challenge->parcours) }}" class="px-5 py-2.5 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold transition">Retour au parcours</a>
            @endif
        </div>
    </div>
</div>
@endsection
