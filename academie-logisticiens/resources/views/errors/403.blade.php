@extends('layouts.public')
@section('title', 'Accès refusé')

@section('content')
<div class="pt-28"></div>
<section class="min-h-[60vh] flex items-center justify-center py-16 px-4">
    <div class="text-center max-w-lg">
        <p class="text-8xl font-bold text-amber-500 font-heading">403</p>
        <h1 class="font-heading text-3xl font-bold text-primary-700 dark:text-white mt-4">Accès refusé</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-3">Vous n'avez pas les permissions nécessaires pour accéder à cette page.</p>
        <div class="mt-8">
            <a href="{{ route('home') }}" class="px-6 py-3 rounded-lg bg-primary-700 text-white font-bold hover:bg-primary-500 transition shadow">Retour à l'accueil</a>
        </div>
    </div>
</section>
@endsection
