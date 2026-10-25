@extends('layouts.apprenant')
@section('page-title', 'Quiz - ' . $quiz->module->titre)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm mb-6">
        <span class="text-xs font-bold text-accent-500 uppercase">{{ $quiz->module->formation->titre }}</span>
        <h2 class="font-heading text-xl font-bold text-primary-700 dark:text-white mt-1">Quiz : {{ $quiz->module->titre }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $quiz->questions->count() }} questions &bull; Score minimum requis : 70%</p>
    </div>

    <form method="POST" action="{{ route('apprenant.quiz.soumettre', $quiz) }}" class="space-y-4">
        @csrf
        @foreach($quiz->questions as $qi => $question)
        <div class="bg-white dark:bg-surface-dark rounded-xl p-5 shadow-sm">
            <p class="font-semibold text-gray-700 dark:text-gray-200 mb-3">{{ $qi + 1 }}. {{ $question->texte }}</p>
            <div class="space-y-2">
                @foreach($question->reponses as $reponse)
                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-accent-500 cursor-pointer transition">
                    <input type="radio" name="reponses[{{ $question->id }}]" value="{{ $reponse->id }}" required class="text-accent-500 focus:ring-accent-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $reponse->texte }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
        <button type="submit" class="w-full py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition shadow">
            Soumettre le Quiz
        </button>
    </form>
</div>
@endsection
