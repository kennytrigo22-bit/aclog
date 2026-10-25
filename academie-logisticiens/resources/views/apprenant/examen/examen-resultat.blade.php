@extends('layouts.apprenant')
@section('page-title', 'Resultat de l\'examen')

@section('content')
<div class="max-w-lg mx-auto text-center">
    <div class="bg-white dark:bg-surface-dark rounded-2xl p-8 shadow-sm">
        <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center text-2xl font-bold {{ $passed ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-600' : 'bg-red-100 dark:bg-red-900 text-red-600' }}">
            {{ $score }}%
        </div>
        <h2 class="font-heading text-2xl font-bold text-primary-700 dark:text-white mt-4">
            @if($passed && $examen->isFinal())
                Felicitations, vous etes certifie(e) !
            @elseif($passed)
                Bravo, examen test reussi !
            @else
                Examen non reussi
            @endif
        </h2>
        <p class="text-gray-500 mt-2">{{ $correct }}/{{ $total }} bonnes reponses ({{ $score }}%)</p>
        @if(!empty($enRetard))
        <p class="text-xs text-red-600 mt-1">Temps depasse : la soumission a ete recue apres la fin de l'examen et n'est pas validee.</p>
        @elseif(isset($tempsEcoule) && $tempsEcoule)
        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">L'examen a ete soumis automatiquement car le temps etait ecoule.</p>
        @endif

        @if($passed && $examen->isFinal() && isset($certificat))
        <p class="text-sm text-gray-500 mt-2">Votre certificat a ete genere avec succes.</p>
        <a href="{{ route('apprenant.certificats.download', $certificat) }}" class="inline-block mt-4 px-6 py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition shadow">
            Telecharger le certificat PDF
        </a>
        @elseif($passed && $examen->isTest())
        <p class="text-sm text-emerald-600 mt-2">Vous maitrisez bien cette formation. Vous pouvez repasser le test ou tenter l'examen final.</p>
        @elseif(!$passed)
        <p class="text-sm text-gray-500 mt-2">Score minimum requis : {{ $examen->score_minimum }}%.</p>
        @if($examen->isTest())
        <p class="text-sm text-blue-600 mt-1">Vous pouvez repasser l'examen test autant de fois que necessaire.</p>
        @else
        <p class="text-sm text-red-600 mt-1">Contactez l'administration pour une reactivation de tentative.</p>
        @endif
        @endif

        <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
            @if($examen->isTest())
            <a href="{{ route('apprenant.examen', $examen) }}" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white font-bold text-sm hover:bg-blue-500 transition">Repasser le test</a>
            @endif
            <a href="{{ route('apprenant.cours.index') }}" class="px-5 py-2.5 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-sm hover:bg-gray-300 dark:hover:bg-gray-600 transition">Retour aux cours</a>
        </div>
    </div>
</div>
@endsection
