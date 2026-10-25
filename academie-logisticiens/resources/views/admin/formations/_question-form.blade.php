<form method="POST" action="{{ $actionUrl }}" class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg space-y-3">
    @csrf
    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Question *</label>
        <input type="text" name="texte" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Type</label>
            <select name="type" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                <option value="qcm">QCM</option>
                <option value="vrai_faux">Vrai / Faux</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Points</label>
            <input type="number" name="points" value="1" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
        </div>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Reponses (cocher la/les bonne(s))</label>
        <div class="space-y-2" x-data="{ count: 4 }">
            <template x-for="i in count" :key="i">
                <div class="flex items-center gap-2">
                    <input type="checkbox" :name="'reponses[' + (i-1) + '][is_correct]'" class="rounded text-emerald-500 focus:ring-emerald-500">
                    <input type="text" :name="'reponses[' + (i-1) + '][texte]'" required placeholder="Reponse..." class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                </div>
            </template>
            <button type="button" @click="count++" class="text-xs text-accent-500 font-semibold hover:underline">+ Ajouter une reponse</button>
        </div>
    </div>
    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-500 transition">Ajouter la question</button>
</form>
