@extends('layouts.admin')
@section('page-title', 'Ticket #' . $ticket->id)

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.tickets.index') }}" class="hover:text-primary-700 dark:hover:text-accent-400 transition">Tickets</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary-700 dark:text-accent-400">#{{ $ticket->id }}</span>
    </div>

    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm mb-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading text-xl font-bold text-primary-700 dark:text-white">{{ $ticket->sujet }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Par <strong>{{ $ticket->user->nom_complet ?? $ticket->user->name }}</strong>
                    ({{ $ticket->user->email }})
                    &bull; {{ $ticket->created_at->format('d/m/Y H:i') }}
                    @if($ticket->formation)
                    &bull; Formation : {{ $ticket->formation->titre }}
                    @endif
                </p>
            </div>
            <form method="POST" action="{{ route('admin.tickets.statut', $ticket) }}">
                @csrf @method('PATCH')
                <select name="statut" onchange="this.form.submit()" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @foreach(['ouvert' => 'Ouvert', 'en_cours' => 'En cours', 'resolu' => 'Résolu', 'ferme' => 'Fermé'] as $val => $label)
                    <option value="{{ $val }}" {{ $ticket->statut == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="space-y-3">
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $ticket->message }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ $ticket->user->nom_complet ?? $ticket->user->name }} - {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
            </div>

            @foreach($ticket->messages as $msg)
            <div class="{{ $msg->is_admin ? 'bg-primary-700/5 dark:bg-primary-700/10 border-l-2 border-primary-500' : 'bg-gray-50 dark:bg-gray-800/50' }} rounded-lg p-4">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $msg->message }}</p>
                <p class="text-xs text-gray-400 mt-2">
                    {{ $msg->is_admin ? 'Admin' : ($ticket->user->nom_complet ?? $ticket->user->name) }}
                    - {{ $msg->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            @endforeach
        </div>
    </div>

    @if(!in_array($ticket->statut, ['ferme']))
    <form method="POST" action="{{ route('admin.tickets.repondre', $ticket) }}" class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        @csrf
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Répondre</label>
        <textarea name="message" rows="3" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500 mb-3" placeholder="Votre réponse..."></textarea>
        @error('message')<p class="text-red-500 text-xs mb-2">{{ $message }}</p>@enderror
        <button type="submit" class="px-6 py-2.5 rounded-lg bg-primary-700 text-white font-semibold hover:bg-primary-500 transition">Envoyer la réponse</button>
    </form>
    @endif
</div>
@endsection
