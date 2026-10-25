@extends('layouts.apprenant')
@section('page-title', 'Résultat du Quiz')

@section('content')
<div class="max-w-lg mx-auto text-center">
    <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 shadow-sm">
        <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center text-2xl font-bold {{ $passed ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-600' : 'bg-red-100 dark:bg-red-900 text-red-600' }}">
            {{ $score }}%
        </div>
        <h2 class="font-heading text-2xl font-bold text-primary-700 dark:text-white mt-4">{{ $passed ? 'Quiz réussi !' : 'Quiz non réussi' }}</h2>
        <p class="text-gray-500 mt-2">{{ $correct }} / {{ $total }} réponses correctes</p>
        <p class="text-xs mt-2 {{ $passed ? 'text-emerald-600' : 'text-amber-600' }}">
            {{ $passed ? 'Ce quiz est validé : il compte pour le déverrouillage des examens.' : 'Il faut au moins 70% à chaque quiz de module pour débloquer les examens.' }}
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('apprenant.cours.index') }}" class="px-6 py-2.5 rounded-lg bg-primary-700 text-white font-semibold hover:bg-primary-500 transition">Retour aux cours</a>
            @if(!$passed)
            <a href="{{ route('apprenant.quiz', $quiz) }}" class="px-6 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-200 transition">Réessayer</a>
            @endif
        </div>
    </div>
</div>
@endsection
