@extends('layouts.admin')
@section('page-title', 'Tickets de support')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.tickets.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold {{ !request('statut') ? 'bg-primary-700 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">Tous</a>
    @foreach(['ouvert' => 'Ouverts', 'en_cours' => 'En cours', 'resolu' => 'Résolus', 'ferme' => 'Fermés'] as $val => $label)
    <a href="{{ route('admin.tickets.index', ['statut' => $val]) }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold {{ request('statut') == $val ? 'bg-primary-700 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Sujet</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Apprenant</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Date</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-800">
                @forelse($tickets as $ticket)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-4 py-3 font-semibold text-primary-700 dark:text-white">{{ $ticket->sujet }}</td>
                    <td class="px-4 py-3">{{ $ticket->user->nom_complet ?? $ticket->user->name }}</td>
                    <td class="px-4 py-3 text-center">
                        @php $sColors = ['ouvert' => 'bg-blue-100 text-blue-700', 'en_cours' => 'bg-yellow-100 text-yellow-700', 'resolu' => 'bg-emerald-100 text-emerald-700', 'ferme' => 'bg-gray-100 text-gray-700']; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $sColors[$ticket->statut] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $ticket->statut)) }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $ticket->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.tickets.show', $ticket) }}" class="px-3 py-1.5 rounded-lg bg-primary-700 text-white text-xs font-bold hover:bg-primary-500 transition">Voir</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Aucun ticket trouvé</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $tickets->withQueryString()->links() }}</div>
@endsection
