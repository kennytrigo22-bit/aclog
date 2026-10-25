@extends('layouts.admin')
@section('page-title', 'Apercu de l\'import')

@section('content')
<div class="max-w-4xl space-y-5" x-data="{ n: {{ count($questions) }}, all: true, toggle() { this.all = !this.all; document.querySelectorAll('.q-check').forEach(c => c.checked = this.all); this.n = this.all ? {{ count($questions) }} : 0; } }">
    <div>
        <h2 class="font-heading text-2xl font-bold text-primary-700 dark:text-white">Apercu de l'import</h2>
        <p class="text-sm text-gray-500 mt-1">Destination : <strong>{{ $label }}</strong> &mdash; {{ count($questions) }} question(s) detectee(s).</p>
    </div>

    @if($mode === 'remplacer')
    <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-300">
        Mode <strong>remplacer</strong> : toutes les anciennes questions de cette destination seront supprimees a la validation.
    </div>
    @endif
    @if($minimum > 0 && count($questions) < $minimum)
    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 text-sm text-amber-700">
        Une etape CTF necessite au moins {{ $minimum }} questions pour etre jouable (actuellement {{ count($questions) }} dans cet import).
    </div>
    @endif

    <form method="POST" action="{{ route('admin.qcm-import.save') }}" class="space-y-3">
        @csrf
        <div class="flex items-center justify-between sticky top-16 z-20 bg-surface-light dark:bg-surface-darker py-2">
            <button type="button" @click="toggle()" class="text-sm font-semibold text-primary-700 dark:text-accent-400 underline">Tout (de)selectionner</button>
            <span class="text-sm text-gray-500"><strong x-text="n"></strong> question(s) retenue(s)</span>
        </div>

        @foreach($questions as $i => $q)
        <label class="block bg-white dark:bg-surface-dark rounded-xl p-4 shadow-sm cursor-pointer">
            <div class="flex items-start gap-3">
                <input type="checkbox" name="keep[]" value="{{ $i }}" checked class="q-check mt-1 rounded border-gray-300 text-accent-500 focus:ring-accent-500"
                       @change="n += $event.target.checked ? 1 : -1">
                <div class="flex-1">
                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-100">{{ $i + 1 }}. {{ $q['texte'] }}</p>
                    <ul class="mt-2 space-y-1">
                        @foreach($q['reponses'] as $r)
                        <li class="text-xs px-2 py-1 rounded {{ $r['is_correct'] ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 font-semibold' : 'text-gray-500' }}">{{ $r['is_correct'] ? '✓' : '•' }} {{ $r['texte'] }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </label>
        @endforeach

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-3 rounded-lg bg-accent-500 text-primary-700 font-bold hover:bg-accent-400 transition">Confirmer l'import</button>
            <a href="{{ route('admin.qcm-import.index') }}" class="px-6 py-3 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold transition">Annuler</a>
        </div>
    </form>
</div>
@endsection
