@extends('layouts.public')
@section('title', 'Erreur serveur')

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-16 px-4">
    <div class="text-center max-w-lg">
        <p class="text-8xl font-bold text-red-500 font-heading">500</p>
        <h1 class="font-heading text-3xl font-bold text-primary-700 dark:text-white mt-4">Erreur serveur</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-3">Une erreur inattendue s'est produite. Notre équipe a été notifiée. Veuillez réessayer dans quelques instants.</p>
        <div class="mt-8">
            <a href="{{ route('home') }}" class="px-6 py-3 rounded-lg bg-primary-700 text-white font-bold hover:bg-primary-500 transition shadow">Retour à l'accueil</a>
        </div>
    </div>
</section>
@endsection
