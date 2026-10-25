@extends('layouts.admin')
@section('page-title', 'Formations')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $formations->total() }} formation(s)</p>
    <a href="{{ route('admin.formations.create') }}" class="px-5 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition flex items-center gap-2 text-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nouvelle formation
    </a>
</div>

<div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Formation</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Catégorie</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Prix</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Inscrits</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-800">
                @foreach($formations as $formation)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-primary-700 dark:text-white">{{ $formation->titre }}</p>
                        <p class="text-xs text-gray-400">{{ $formation->modules_count }} modules &bull; {{ $formation->duree_heures }}h</p>
                    </td>
                    <td class="px-4 py-3 capitalize">{{ $formation->categorie }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($formation->promo_active && $formation->prix_promo)
                        <span class="text-accent-500 font-bold">{{ number_format($formation->prix_promo, 0, ',', ' ') }}</span>
                        <span class="text-gray-400 line-through text-xs ml-1">{{ number_format($formation->prix, 0, ',', ' ') }}</span>
                        @else
                        <span class="font-semibold">{{ number_format($formation->prix, 0, ',', ' ') }}</span>
                        @endif
                        <span class="text-xs text-gray-500">F</span>
                    </td>
                    <td class="px-4 py-3 text-center font-semibold">{{ $formation->inscriptions_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $formation->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $formation->is_published ? 'Publié' : 'Brouillon' }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.formations.contenu', $formation) }}" class="p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-500 transition" title="Gérer le contenu">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </a>
                            <a href="{{ route('admin.formations.edit', $formation) }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 transition" title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.formations.destroy', $formation) }}" onsubmit="return confirm('Supprimer cette formation ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-400 hover:text-red-500 transition" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $formations->links() }}</div>
@endsection
