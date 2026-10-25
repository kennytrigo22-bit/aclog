@extends('layouts.admin')
@section('page-title', 'Codes Promo')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $promos->total() }} code(s) promo</p>
    <a href="{{ route('admin.promos.create') }}" class="px-5 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition flex items-center gap-2 text-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nouveau code
    </a>
</div>

<div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Code</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Type</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Valeur</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Utilisations</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Validité</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-800">
                @forelse($promos as $promo)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-4 py-3 font-mono font-bold text-primary-700 dark:text-accent-400">{{ $promo->code }}</td>
                    <td class="px-4 py-3 text-center capitalize">{{ $promo->type }}</td>
                    <td class="px-4 py-3 text-center font-semibold">{{ $promo->type === 'pourcentage' ? $promo->valeur . '%' : number_format($promo->valeur, 0, ',', ' ') . ' F' }}</td>
                    <td class="px-4 py-3 text-center">{{ $promo->nb_utilisations_actuel }}/{{ $promo->nb_utilisations_max ?? '∞' }}</td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                        @if($promo->date_debut && $promo->date_fin)
                        {{ $promo->date_debut->format('d/m') }} → {{ $promo->date_fin->format('d/m/Y') }}
                        @else
                        Illimité
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $promo->estValide() ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $promo->estValide() ? 'Actif' : 'Inactif' }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.promos.edit', $promo) }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.promos.destroy', $promo) }}" onsubmit="return confirm('Supprimer ce code promo ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-400 hover:text-red-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun code promo</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $promos->links() }}</div>
@endsection
