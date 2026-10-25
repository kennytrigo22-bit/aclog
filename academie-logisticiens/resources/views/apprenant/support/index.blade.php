@extends('layouts.apprenant')
@section('page-title', 'Support')

@section('content')
{{-- Nouveau ticket --}}
<div x-data="{ open: false }" class="mb-6">
    <button @click="open = !open" class="px-5 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nouveau ticket
    </button>
    <div x-show="open" x-transition class="mt-4 bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <form method="POST" action="{{ route('apprenant.support.creer') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Sujet *</label>
                <input type="text" name="sujet" required value="{{ old('sujet') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                @error('sujet')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Formation concernée (optionnel)</label>
                <select name="formation_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    <option value="">- Aucune -</option>
                    @foreach(auth()->user()->inscriptions ?? [] as $inscription)
                    <option value="{{ $inscription->formation_id }}">{{ $inscription->formation->titre ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Message *</label>
                <textarea name="message" rows="4" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">{{ old('message') }}</textarea>
                @error('message')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-primary-700 text-white font-semibold hover:bg-primary-500 transition">Envoyer</button>
        </form>
    </div>
</div>

{{-- Liste des tickets --}}
@if($tickets->isEmpty())
<div class="bg-white dark:bg-surface-dark rounded-xl p-8 text-center shadow-sm">
    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
    <p class="text-gray-500 mt-3">Aucun ticket de support pour le moment.</p>
</div>
@else
<div class="space-y-4">
    @foreach($tickets as $ticket)
    <div x-data="{ expanded: false }" class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">
        <button @click="expanded = !expanded" class="w-full p-5 text-left flex items-center justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    @php
                        $statutColors = ['ouvert' => 'bg-blue-100 text-blue-700', 'en_cours' => 'bg-yellow-100 text-yellow-700', 'resolu' => 'bg-emerald-100 text-emerald-700', 'ferme' => 'bg-gray-100 text-gray-700'];
                        $statutLabels = ['ouvert' => 'Ouvert', 'en_cours' => 'En cours', 'resolu' => 'Résolu', 'ferme' => 'Fermé'];
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $statutColors[$ticket->statut] ?? 'bg-gray-100 text-gray-700' }}">{{ $statutLabels[$ticket->statut] ?? $ticket->statut }}</span>
                    @if($ticket->formation)
                    <span class="text-xs text-gray-500">{{ $ticket->formation->titre }}</span>
                    @endif
                </div>
                <h3 class="font-heading font-bold text-primary-700 dark:text-white truncate">{{ $ticket->sujet }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ $ticket->created_at->diffForHumans() }}</p>
            </div>
            <svg class="w-5 h-5 text-gray-400 shrink-0 transition-transform" :class="expanded && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="expanded" x-transition class="border-t border-gray-100 dark:border-gray-800">
            <div class="p-5 space-y-3">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3">
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $ticket->message }}</p>
                    <p class="text-xs text-gray-400 mt-1">Vous - {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @foreach($ticket->messages as $msg)
                <div class="{{ $msg->is_admin ? 'bg-primary-700/5 dark:bg-primary-700/10 border-l-2 border-primary-500' : 'bg-gray-50 dark:bg-gray-800/50' }} rounded-lg p-3">
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $msg->message }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $msg->is_admin ? 'Support' : 'Vous' }} - {{ $msg->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @endforeach
            </div>
            @if(!in_array($ticket->statut, ['resolu', 'ferme']))
            <form method="POST" action="{{ route('apprenant.support.repondre', $ticket) }}" class="p-5 pt-0">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="message" required placeholder="Votre réponse..." class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-700 text-white text-sm font-semibold hover:bg-primary-500 transition">Envoyer</button>
                </div>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
