@extends('layouts.admin')
@section('page-title', 'Utilisateurs')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-6">
    <form method="GET" class="flex items-center gap-2 flex-1">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher..." class="w-full sm:w-64 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
        <select name="role" onchange="this.form.submit()" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
            <option value="">Tous les rôles</option>
            @foreach(['apprenant', 'admin', 'gestionnaire'] as $r)
            <option value="{{ $r }}" {{ request('role') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary-700 text-white text-sm font-semibold hover:bg-primary-500 transition">Filtrer</button>
    </form>
</div>

<div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Nom</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Email</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Profil</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Rôle</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Points</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Inscrit le</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-800">
                @forelse($utilisateurs as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-4 py-3 font-semibold">{{ $user->nom_complet ?? $user->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ \App\Support\ProfilOptions::SITUATIONS[$user->situation] ?? '—' }}
                        @if($user->profession)<br>{{ $user->profession }}@endif
                        @if($user->date_naissance)<br>{{ $user->date_naissance->age }} ans @if($user->ville)&bull; {{ $user->ville }}@endif @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php $roleColors = ['admin' => 'bg-red-100 text-red-700', 'gestionnaire' => 'bg-purple-100 text-purple-700', 'apprenant' => 'bg-blue-100 text-blue-700']; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($user->role) }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($user->isSuspended())
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700">Suspendu</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Actif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">{{ $user->points ?? 0 }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-right flex items-center justify-end gap-1">
                        <a href="{{ route('admin.utilisateurs.edit', $user) }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 transition" title="Voir / Modifier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.utilisateurs.destroy', $user) }}" class="inline" onsubmit="return confirm('Désactiver cet utilisateur ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-500 transition" title="Désactiver">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun utilisateur trouvé</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $utilisateurs->withQueryString()->links() }}</div>
@endsection
