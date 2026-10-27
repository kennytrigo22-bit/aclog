@extends('layouts.admin')
@section('page-title', 'Challenges CTF')

@section('content')
<div class="max-w-5xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-heading text-2xl font-bold text-primary-700 dark:text-white">Parcours & Challenges</h2>
            <p class="text-sm text-gray-500 mt-1">Gerez les parcours CTF et leurs challenges (Capture The Flag)</p>
        </div>
        <a href="{{ route('admin.challenges.parcours.create') }}" class="px-5 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau Parcours
        </a>
    </div>

    @if($parcours->isEmpty())
    <div class="bg-white dark:bg-surface-dark rounded-xl p-12 shadow-sm text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
        <h3 class="font-heading font-bold text-gray-500 text-lg">Aucun parcours CTF</h3>
        <p class="text-sm text-gray-400 mt-1">Creez votre premier parcours pour commencer a ajouter des challenges.</p>
    </div>
    @else
    <div class="grid gap-4">
        @foreach($parcours as $p)
        <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 flex flex-wrap sm:flex-nowrap items-center gap-3 sm:gap-4">
                @if($p->image)
                <img src="{{ asset('storage/' . $p->image) }}" alt="" class="w-16 h-16 rounded-lg object-cover shrink-0">
                @else
                <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-primary-700 to-primary-800 flex items-center justify-center shrink-0">
                    <svg class="w-8 h-8 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                </div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-heading font-bold text-primary-700 dark:text-white truncate">{{ $p->titre }}</h3>
                        @if(!$p->is_active)
                        <span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 text-xs font-bold">Inactif</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-xs text-gray-500">
                        <span class="px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold">{{ $p->domaine }}</span>
                        <span class="capitalize">{{ $p->niveau }}</span>
                        <span>{{ $p->challenges_count }} challenge(s)</span>
                    </div>
                    @if($p->description)
                    <p class="text-xs text-gray-400 mt-1 truncate">{{ $p->description }}</p>
                    @endif
                </div>
                <div class="flex items-center justify-end gap-2 shrink-0 w-full sm:w-auto">
                    <a href="{{ route('admin.challenges.parcours.show', $p) }}" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition" title="Gerer les challenges">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.challenges.parcours.edit', $p) }}" class="p-2 rounded-lg text-accent-500 hover:bg-accent-500/10 transition" title="Modifier">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form method="POST" action="{{ route('admin.challenges.parcours.destroy', $p) }}" onsubmit="return confirm('Supprimer ce parcours et tous ses challenges ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Supprimer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
