@extends('layouts.admin')
@section('page-title', 'Partenaires')

@section('content')
<div class="max-w-4xl space-y-6">

    {{-- Formulaire d'ajout --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <h2 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-5">Ajouter un partenaire</h2>
        <form method="POST" action="{{ route('admin.partenaires.store') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-end gap-4">
            @csrf
            <div class="flex-1 w-full">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nom du partenaire</label>
                <input type="text" name="nom" required placeholder="Ex: Port Autonome de Pointe-Noire" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                @error('nom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex-1 w-full">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Logo (image)</label>
                <input type="file" name="logo" accept="image/*" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-accent-500 file:text-primary-700 file:font-semibold file:text-sm hover:file:bg-accent-400 file:cursor-pointer">
                @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition whitespace-nowrap flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajouter
            </button>
        </form>
    </div>

    {{-- Liste des partenaires --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <h2 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-5">
            Partenaires ({{ $partenaires->count() }})
        </h2>

        @if($partenaires->count() > 0)
        <div class="space-y-3">
            @foreach($partenaires as $partenaire)
            <div class="flex items-center gap-4 p-3 rounded-lg {{ $partenaire->actif ? 'bg-surface-light dark:bg-surface-darker' : 'bg-gray-100 dark:bg-gray-800 opacity-60' }}">
                <div class="w-16 h-16 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden shrink-0 p-1">
                    <img src="{{ asset('storage/' . $partenaire->logo) }}" alt="{{ $partenaire->nom }}" class="max-w-full max-h-full object-contain">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-primary-700 dark:text-white truncate">{{ $partenaire->nom }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $partenaire->actif ? 'Actif' : 'Inactif' }} - Ordre : {{ $partenaire->ordre }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <form method="POST" action="{{ route('admin.partenaires.toggle', $partenaire) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="p-2 rounded-lg {{ $partenaire->actif ? 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20' : 'text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }} transition" title="{{ $partenaire->actif ? 'Désactiver' : 'Activer' }}">
                            @if($partenaire->actif)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            @endif
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.partenaires.destroy', $partenaire) }}" onsubmit="return confirm('Supprimer ce partenaire ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Supprimer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-10 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21"/></svg>
            <p class="font-medium">Aucun partenaire pour le moment</p>
            <p class="text-sm mt-1">Utilisez le formulaire ci-dessus pour en ajouter.</p>
        </div>
        @endif
    </div>

    {{-- Apercu du carrousel --}}
    @if($partenaires->where('actif', true)->count() > 0)
    <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
        <h2 class="font-heading font-bold text-primary-700 dark:text-white border-b dark:border-gray-800 pb-3 mb-5">Apercu du carrousel</h2>
        <div class="overflow-hidden">
            <div class="flex items-center gap-12 animate-marquee">
                @foreach($partenaires->where('actif', true) as $p)
                <div class="shrink-0 w-24 h-16 flex items-center justify-center transition hover:scale-110">
                    <img src="{{ asset('storage/' . $p->logo) }}" alt="{{ $p->nom }}" class="max-w-full max-h-full object-contain">
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
