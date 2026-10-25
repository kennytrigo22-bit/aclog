@extends('layouts.admin')
@section('page-title', 'Dashboard')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    @php
        $cards = [
            ['label' => 'Apprenants', 'value' => $stats['apprenants'], 'color' => 'text-blue-600 bg-blue-50 dark:bg-blue-900/20', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['label' => 'Formations', 'value' => $stats['formations'], 'color' => 'text-purple-600 bg-purple-50 dark:bg-purple-900/20', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['label' => 'Inscrits actifs', 'value' => $stats['inscriptions'], 'color' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Revenus', 'value' => number_format($stats['revenus'], 0, ',', ' ') . ' F', 'color' => 'text-accent-500 bg-accent-500/10', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Paiements en attente', 'value' => $stats['paiements_en_attente'], 'color' => 'text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Tickets ouverts', 'value' => $stats['tickets_ouverts'], 'color' => 'text-red-600 bg-red-50 dark:bg-red-900/20', 'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4'],
        ];
    @endphp
    @foreach($cards as $card)
    <div class="bg-white dark:bg-surface-dark rounded-xl p-4 shadow-sm">
        <div class="w-10 h-10 rounded-lg {{ $card['color'] }} flex items-center justify-center mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/></svg>
        </div>
        <p class="text-2xl font-bold text-primary-700 dark:text-white">{{ $card['value'] }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $card['label'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    {{-- Paiements en attente --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm">
        <div class="p-5 border-b dark:border-gray-800 flex items-center justify-between">
            <h2 class="font-heading font-bold text-primary-700 dark:text-white">Paiements en attente</h2>
            @if(Route::has('admin.paiements.index'))
            <a href="{{ route('admin.paiements.index') }}" class="text-xs text-accent-500 hover:underline font-semibold">Voir tout</a>
            @endif
        </div>
        @if($paiementsRecents->isEmpty())
        <div class="p-5 text-center text-gray-400 text-sm">Aucun paiement en attente</div>
        @else
        <div class="divide-y dark:divide-gray-800">
            @foreach($paiementsRecents as $paiement)
            <div class="p-4 flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold truncate">{{ $paiement->user->nom_complet ?? $paiement->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $paiement->formation->titre ?? '-' }} &bull; {{ $paiement->created_at->diffForHumans() }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-bold text-primary-700 dark:text-white">{{ number_format($paiement->montant, 0, ',', ' ') }} F</p>
                    <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-[10px] font-bold">En attente</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Tickets récents --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm">
        <div class="p-5 border-b dark:border-gray-800 flex items-center justify-between">
            <h2 class="font-heading font-bold text-primary-700 dark:text-white">Tickets récents</h2>
            @if(Route::has('admin.tickets.index'))
            <a href="{{ route('admin.tickets.index') }}" class="text-xs text-accent-500 hover:underline font-semibold">Voir tout</a>
            @endif
        </div>
        @if($ticketsRecents->isEmpty())
        <div class="p-5 text-center text-gray-400 text-sm">Aucun ticket ouvert</div>
        @else
        <div class="divide-y dark:divide-gray-800">
            @foreach($ticketsRecents as $ticket)
            <div class="p-4">
                <div class="flex items-center gap-2 mb-1">
                    @php
                        $sColors = ['ouvert' => 'bg-blue-100 text-blue-700', 'en_cours' => 'bg-yellow-100 text-yellow-700', 'resolu' => 'bg-emerald-100 text-emerald-700'];
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sColors[$ticket->statut] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $ticket->statut)) }}</span>
                    <span class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm font-semibold text-primary-700 dark:text-white">{{ $ticket->sujet }}</p>
                <p class="text-xs text-gray-500">{{ $ticket->user->nom_complet ?? $ticket->user->name }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
