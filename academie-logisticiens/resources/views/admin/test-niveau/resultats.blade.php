@extends('layouts.admin')
@section('page-title', 'Resultats - Tests de Niveau')

@section('content')
<div class="max-w-5xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-heading text-2xl font-bold text-primary-700 dark:text-white">Resultats des Tests de Niveau</h2>
            <p class="text-sm text-gray-500 mt-1">Historique de tous les tests passes par les utilisateurs</p>
        </div>
        <a href="{{ route('admin.test-niveau.index') }}" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-sm hover:bg-gray-300 dark:hover:bg-gray-600 transition">
            Gerer les questions
        </a>
    </div>

    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">
        @if($resultats->isEmpty())
        <div class="p-12 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <p class="font-medium">Aucun resultat pour le moment</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Utilisateur</th>
                    <th class="px-4 py-3 text-left">Domaine</th>
                    <th class="px-4 py-3 text-left">Niveau</th>
                    <th class="px-4 py-3 text-center">Score</th>
                    <th class="px-4 py-3 text-center">Resultat</th>
                    <th class="px-4 py-3 text-right">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-800">
                @foreach($resultats as $r)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-4 py-3">
                        @if($r->user)
                        <span class="font-semibold text-primary-700 dark:text-white">{{ $r->user->prenom ?? '' }} {{ $r->user->name }}</span>
                        <br><span class="text-xs text-gray-400">{{ $r->user->email }}</span>
                        @else
                        <span class="text-gray-400">Anonyme</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 capitalize">{{ $r->domaine }}</td>
                    <td class="px-4 py-3 capitalize">{{ $r->niveau }}</td>
                    <td class="px-4 py-3 text-center font-bold {{ $r->passed ? 'text-emerald-600' : 'text-red-500' }}">{{ $r->score }}%</td>
                    <td class="px-4 py-3 text-center">
                        @if($r->passed)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 text-xs font-bold">Reussi</span>
                        @else
                        <span class="px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 text-xs font-bold">Echoue</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-xs text-gray-400">{{ $r->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t dark:border-gray-800">
            {{ $resultats->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
