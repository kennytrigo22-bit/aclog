@extends('layouts.public')

@section('title', 'Résultat du Test - ' . $siteTitre)

@section('content')

<section class="w-full py-10 lg:py-20 bg-surface-light dark:bg-surface-darker">
    <div class="max-w-2xl mx-auto px-4 lg:px-6">
        <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 shadow-sm text-center">
            {{-- Score --}}
            <div class="w-24 h-24 rounded-full mx-auto flex items-center justify-center text-3xl font-bold {{ $passed ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400' : 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400' }}">
                {{ $score }}%
            </div>
            <h1 class="font-heading text-2xl font-bold text-primary-700 dark:text-white mt-6">
                @if($passed)
                    Félicitations !
                @else
                    Continuez vos efforts !
                @endif
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                @if($passed)
                    Vous avez réussi le test avec un score de {{ $score }}%.
                    @auth
                        <span class="text-accent-500 font-semibold">+2 points ajoutés à votre compte !</span>
                    @endauth
                @else
                    Votre score est de {{ $score }}%. Ne vous découragez pas, nos formations vous aideront à progresser.
                @endif
            </p>

            {{-- Recommandation IA --}}
            <div class="mt-8 bg-primary-700/5 dark:bg-primary-700/20 rounded-xl p-6 text-left">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-accent-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                    <span class="font-heading font-bold text-primary-700 dark:text-white">Orientation IA</span>
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">{{ $recommandation['explication'] }}</p>
                <p class="text-sm font-semibold text-primary-700 dark:text-accent-400">Niveau recommandé : <span class="capitalize">{{ $recommandation['niveau_recommande'] }}</span></p>
                @if(!empty($recommandation['formation']))
                <div class="mt-3">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-2">Formation suggérée :</p>
                    <a href="{{ route('formations.show', $recommandation['formation']) }}" class="inline-block px-3 py-1 rounded-full bg-accent-500/10 text-accent-700 dark:text-accent-400 text-xs font-semibold hover:bg-accent-500/20 transition">{{ $recommandation['formation']->titre }}</a>
                </div>
                @endif
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('formations.index') }}" class="px-6 py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition-all">
                    Explorer les formations
                </a>
                <a href="{{ route('tests.index') }}" class="px-6 py-3 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    Refaire un test
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
