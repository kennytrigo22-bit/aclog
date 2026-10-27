@extends('layouts.public')
@section('title', 'Page introuvable')

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-16 px-4">
    <div class="text-center max-w-lg">
        <p class="text-8xl font-bold text-accent-500 font-heading">404</p>
        <h1 class="font-heading text-3xl font-bold text-primary-700 dark:text-white mt-4">Page introuvable</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-3">La page que vous recherchez n'existe pas ou a été déplacée.</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8">
            <a href="{{ route('home') }}" class="px-6 py-3 rounded-lg bg-primary-700 text-white font-bold hover:bg-primary-500 transition shadow">Retour à l'accueil</a>
            <a href="{{ route('formations.index') }}" class="px-6 py-3 rounded-lg border-2 border-primary-700 dark:border-accent-400 text-primary-700 dark:text-accent-400 font-bold hover:bg-primary-700 hover:text-white dark:hover:bg-accent-400 dark:hover:text-primary-700 transition">Voir les formations</a>
        </div>
    </div>
</section>
@endsection
