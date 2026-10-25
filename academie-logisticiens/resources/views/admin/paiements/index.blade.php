@extends('layouts.admin')
@section('page-title', 'Paiements')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.paiements.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold {{ !request('statut') ? 'bg-primary-700 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">Tous</a>
    @foreach(['en_attente' => 'En attente', 'valide' => 'Validés', 'rejete' => 'Rejetés'] as $val => $label)
    <a href="{{ route('admin.paiements.index', ['statut' => $val]) }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold {{ request('statut') == $val ? 'bg-primary-700 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Apprenant</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Formation</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Montant</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Date</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-800">
                @forelse($paiements as $paiement)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-4 py-3 font-semibold">{{ $paiement->user->nom_complet ?? $paiement->user->name }}</td>
                    <td class="px-4 py-3">{{ $paiement->formation->titre ?? '-' }}</td>
                    <td class="px-4 py-3 text-center font-bold">{{ number_format($paiement->montant, 0, ',', ' ') }} F</td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $colors = ['en_attente' => 'bg-yellow-100 text-yellow-700', 'valide' => 'bg-emerald-100 text-emerald-700', 'rejete' => 'bg-red-100 text-red-700'];
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $colors[$paiement->statut] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $paiement->statut)) }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $paiement->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-right">
                        @if($paiement->statut === 'en_attente')
                        <div x-data="{ showReject: false }" class="flex items-center justify-end gap-1">
                            <form method="POST" action="{{ route('admin.paiements.valider', $paiement) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500 text-white text-xs font-bold hover:bg-emerald-600 transition">Valider</button>
                            </form>
                            <button @click="showReject = !showReject" class="px-3 py-1.5 rounded-lg bg-red-500 text-white text-xs font-bold hover:bg-red-600 transition">Rejeter</button>
                            <div x-show="showReject" x-transition class="absolute right-4 mt-32 bg-white dark:bg-surface-dark rounded-lg shadow-lg border dark:border-gray-700 p-3 z-10 w-64">
                                <form method="POST" action="{{ route('admin.paiements.rejeter', $paiement) }}">
                                    @csrf
                                    <textarea name="note_admin" required placeholder="Motif du rejet..." rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-xs focus:ring-accent-500 focus:border-accent-500 mb-2"></textarea>
                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-red-500 text-white text-xs font-bold">Confirmer le rejet</button>
                                </form>
                            </div>
                        </div>
                        @elseif($paiement->capture_paiement)
                        <a href="{{ route('admin.paiements.capture', $paiement) }}" target="_blank" class="text-xs text-accent-500 hover:underline">Voir capture</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucun paiement trouvé</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $paiements->withQueryString()->links() }}</div>
@endsection
