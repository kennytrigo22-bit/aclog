@extends('layouts.apprenant')
@section('page-title', $ressource->titre)

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('apprenant.cours.index') }}" class="hover:text-primary-700 dark:hover:text-accent-400 transition">Mes Formations</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span>{{ $ressource->formation->titre }}</span>
    </div>

    <div class="rounded-2xl bg-gradient-to-br from-[#0B1E36] to-[#1B3A5C] text-white p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-accent-400">Ressource &bull; {{ $ressource->formation->titre }}</span>
            <h1 class="font-heading text-xl font-bold mt-1">{{ $ressource->titre }}</h1>
        </div>
        @if($ressource->fichier)
        <a href="{{ route('apprenant.ressources.telecharger', $ressource) }}" class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-accent-500 text-primary-800 font-bold hover:bg-accent-400 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Télécharger{{ $ressource->estPdf() ? ' (PDF)' : ' (Word)' }}
        </a>
        @endif
    </div>

    @if($ressource->contenu_html)
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm contenu-cours text-gray-700 dark:text-gray-200">
        {!! \App\Support\HtmlSanitizer::clean($ressource->contenu_html) !!}
    </div>
    @elseif($ressource->estPdf())
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">
        <iframe src="{{ route('apprenant.ressources.pdf', $ressource) }}" title="{{ $ressource->titre }}" class="w-full" style="height: 80vh" loading="lazy"></iframe>
    </div>
    @endif
</div>
@endsection
