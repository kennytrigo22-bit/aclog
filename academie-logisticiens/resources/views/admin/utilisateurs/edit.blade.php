@extends('layouts.admin')
@section('page-title', 'Utilisateur : ' . $utilisateur->nom_complet)

@section('content')
<div class="max-w-4xl space-y-6">

    {{-- En-tête profil --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm flex items-center gap-5">
        <div class="w-16 h-16 rounded-full bg-primary-700 flex items-center justify-center text-white text-xl font-bold shrink-0 overflow-hidden">
            @if($utilisateur->avatar)
                <img src="{{ asset('storage/' . $utilisateur->avatar) }}" alt="" class="w-full h-full object-cover">
            @else
                {{ strtoupper(substr($utilisateur->prenom ?? $utilisateur->name, 0, 1)) }}{{ strtoupper(substr($utilisateur->name, 0, 1)) }}
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <h2 class="font-heading text-xl font-bold text-primary-700 dark:text-white">{{ $utilisateur->nom_complet }}</h2>
            <p class="text-sm text-gray-500">{{ $utilisateur->email }}</p>
            <div class="flex items-center gap-2 mt-1">
                @php $roleColors = ['admin' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300', 'gestionnaire' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300', 'apprenant' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300']; @endphp
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $roleColors[$utilisateur->role] ?? '' }}">{{ ucfirst($utilisateur->role) }}</span>
                @if($utilisateur->isSuspended())
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">Suspendu</span>
                @endif
                <span class="text-xs text-gray-400">Inscrit le {{ $utilisateur->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Messages flash --}}
    @if(session('success'))
    <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Informations générales --}}
        <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
            <h3 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-4">Informations générales</h3>
            <form method="POST" action="{{ route('admin.utilisateurs.update', $utilisateur) }}" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                        <input type="text" name="name" value="{{ old('name', $utilisateur->name) }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Prénom</label>
                        <input type="text" name="prenom" value="{{ old('prenom', $utilisateur->prenom) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $utilisateur->email) }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Rôle *</label>
                    @if($utilisateur->id === auth()->id())
                        <input type="hidden" name="role" value="{{ $utilisateur->role }}">
                        <p class="text-sm text-gray-500 italic py-2">Vous ne pouvez pas modifier votre propre rôle.</p>
                    @else
                        <select name="role" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                            @foreach(['apprenant' => 'Apprenant', 'gestionnaire' => 'Gestionnaire', 'admin' => 'Administrateur'] as $val => $label)
                            <option value="{{ $val }}" {{ old('role', $utilisateur->role) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Points</label>
                        <input type="number" name="points" value="{{ old('points', $utilisateur->points) }}" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Jetons FCFA</label>
                        <input type="number" name="jetons" value="{{ old('jetons', $utilisateur->jetons) }}" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    </div>
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition text-sm">Enregistrer</button>
            </form>
        </div>

        {{-- Réinitialiser le mot de passe --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
                <h3 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-4">Mot de passe</h3>
                <form method="POST" action="{{ route('admin.utilisateurs.resetPassword', $utilisateur) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nouveau mot de passe *</label>
                        <input type="password" name="new_password" required minlength="8" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                        @error('new_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Confirmer *</label>
                        <input type="password" name="new_password_confirmation" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                    </div>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary-700 text-white font-bold hover:bg-primary-500 transition text-sm">Réinitialiser</button>
                </form>
            </div>

            {{-- Suspension --}}
            <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
                <h3 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-4">Suspension</h3>
                @if($utilisateur->isSuspended())
                    <div class="p-3 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 text-sm mb-4">
                        <p class="font-semibold">Compte suspendu depuis le {{ $utilisateur->suspended_at->format('d/m/Y à H:i') }}</p>
                        @if($utilisateur->suspended_until)
                            <p>Jusqu'au {{ $utilisateur->suspended_until->format('d/m/Y') }}</p>
                        @else
                            <p>Suspension définitive</p>
                        @endif
                        @if($utilisateur->suspension_reason)
                            <p class="mt-1">Raison : {{ $utilisateur->suspension_reason }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.utilisateurs.unsuspend', $utilisateur) }}">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-600 text-white font-bold hover:bg-emerald-500 transition text-sm">Lever la suspension</button>
                    </form>
                @else
                    @if($utilisateur->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.utilisateurs.suspend', $utilisateur) }}" x-data="{ type: 'temporaire' }" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Type de suspension</label>
                            <select name="suspension_type" x-model="type" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                                <option value="temporaire">Temporaire</option>
                                <option value="definitive">Définitive</option>
                            </select>
                        </div>
                        <div x-show="type === 'temporaire'" x-cloak>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Suspendre jusqu'au *</label>
                            <input type="date" name="suspended_until" min="{{ now()->addDay()->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Raison</label>
                            <input type="text" name="suspension_reason" placeholder="Optionnel" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                        </div>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-orange-600 text-white font-bold hover:bg-orange-500 transition text-sm" onclick="return confirm('Confirmer la suspension ?')">Suspendre</button>
                    </form>
                    @else
                    <p class="text-sm text-gray-500 italic">Vous ne pouvez pas suspendre votre propre compte.</p>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Formations en cours --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <h3 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-4">Formations ({{ $utilisateur->inscriptions->count() }})</h3>
        @if($utilisateur->inscriptions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-400">Formation</th>
                        <th class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-400">Date d'inscription</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-800">
                    @foreach($utilisateur->inscriptions as $inscription)
                    <tr>
                        <td class="px-4 py-2">{{ $inscription->formation->titre ?? '-' }}</td>
                        <td class="px-4 py-2 text-center">
                            @php $statutColors = ['en_cours' => 'bg-blue-100 text-blue-700', 'terminee' => 'bg-emerald-100 text-emerald-700', 'en_attente' => 'bg-yellow-100 text-yellow-700']; @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $statutColors[$inscription->statut] ?? 'bg-gray-100 text-gray-700' }}">{{ str_replace('_', ' ', ucfirst($inscription->statut)) }}</span>
                        </td>
                        <td class="px-4 py-2 text-center text-gray-500">{{ $inscription->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-sm text-gray-400 text-center py-4">Aucune formation</p>
        @endif
    </div>

    {{-- Paiements --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <h3 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-4">Paiements ({{ $utilisateur->paiements->count() }})</h3>
        @if($utilisateur->paiements->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-400">Réf.</th>
                        <th class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-400">Montant</th>
                        <th class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-400">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-800">
                    @foreach($utilisateur->paiements as $paiement)
                    <tr>
                        <td class="px-4 py-2 font-mono text-xs">{{ $paiement->reference ?? $paiement->id }}</td>
                        <td class="px-4 py-2 text-center font-semibold">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                        <td class="px-4 py-2 text-center">
                            @php $pColors = ['valide' => 'bg-emerald-100 text-emerald-700', 'en_attente' => 'bg-yellow-100 text-yellow-700', 'rejete' => 'bg-red-100 text-red-700']; @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $pColors[$paiement->statut] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $paiement->statut)) }}</span>
                        </td>
                        <td class="px-4 py-2 text-center text-gray-500">{{ $paiement->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-sm text-gray-400 text-center py-4">Aucun paiement</p>
        @endif
    </div>

    {{-- Certificats --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <h3 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-4">Certificats ({{ $utilisateur->certificats->count() }})</h3>
        @if($utilisateur->certificats->count() > 0)
        <div class="flex flex-wrap gap-3">
            @foreach($utilisateur->certificats as $cert)
            <div class="px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300">
                <p class="font-semibold text-sm">{{ $cert->formation->titre ?? 'Formation' }}</p>
                <p class="text-xs mt-0.5">Obtenu le {{ $cert->created_at->format('d/m/Y') }}</p>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-400 text-center py-4">Aucun certificat</p>
        @endif
    </div>

    {{-- Désactivation --}}
    @if($utilisateur->id !== auth()->id())
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm border border-red-200 dark:border-red-900/50">
        <h3 class="font-heading font-bold text-red-600 dark:text-red-400 border-b dark:border-gray-800 pb-3 mb-4">Zone dangereuse</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Désactiver cet utilisateur masquera son compte de toutes les listes. Cette action ne supprime pas les données.</p>
        <form method="POST" action="{{ route('admin.utilisateurs.destroy', $utilisateur) }}">
            @csrf @method('DELETE')
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-red-600 text-white font-bold hover:bg-red-500 transition text-sm" onclick="return confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ? Son compte sera masqué mais ses données seront conservées.')">Désactiver le compte</button>
        </form>
    </div>
    @endif

    <div class="flex">
        <a href="{{ route('admin.utilisateurs.index') }}" class="px-5 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition text-sm">Retour à la liste</a>
    </div>
</div>
@endsection
