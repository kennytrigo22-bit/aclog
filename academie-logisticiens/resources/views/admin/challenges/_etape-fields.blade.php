@php $inp = 'w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500'; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Titre de l'etape</label>
        <input type="text" name="titre" value="{{ $challenge?->titre }}" required class="{{ $inp }}" placeholder="Ex: Les documents de transport">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Theme abord&eacute;</label>
        <input type="text" name="theme" value="{{ $challenge?->theme }}" class="{{ $inp }}" placeholder="Ex: Incoterms">
    </div>
</div>
<div>
    <label class="block text-xs font-semibold text-gray-500 mb-1">Briefing (affiche avant les questions)</label>
    <textarea name="description" rows="2" class="{{ $inp }}">{{ $challenge?->description }}</textarea>
</div>
<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Points du drapeau</label>
        <input type="number" name="points" value="{{ $challenge?->points ?? 5 }}" min="1" required class="{{ $inp }}">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Questions posees (min. {{ \App\Models\Challenge::MIN_QUESTIONS }})</label>
        <input type="number" name="nb_questions" value="{{ $challenge?->nb_questions ?? 10 }}" min="{{ \App\Models\Challenge::MIN_QUESTIONS }}" max="50" required class="{{ $inp }}">
    </div>
</div>
<label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
    <input type="checkbox" name="is_active" {{ ($challenge?->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-accent-500 focus:ring-accent-500"> Etape active
</label>
