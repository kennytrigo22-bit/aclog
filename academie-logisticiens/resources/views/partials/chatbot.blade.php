{{-- Chatbot Widget --}}
<div x-data="chatbot()">
    {{-- Bouton "Assistant ACLOG" (modele du site) --}}
    <button type="button" class="ia" @click="open = !open" :aria-expanded="open.toString()" aria-controls="chat-window">
        <i>IA</i><span><b>Assistant ACLOG</b><small x-text="open ? 'Fermer la discussion' : 'Posez vos questions'">Posez vos questions</small></span>
    </button>

    {{-- Chat Window --}}
    <div id="chat-window" x-show="open" x-cloak x-transition.origin.bottom.right class="chatw bg-white dark:bg-surface-dark rounded-2xl shadow-2xl border dark:border-gray-800 overflow-hidden flex flex-col">
        <div class="bg-primary-700 text-white p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-accent-500 text-primary-700 flex items-center justify-center font-bold text-sm">IA</div>
            <div>
                <p class="font-heading font-bold text-sm">Assistant ACLOG</p>
                <p class="text-xs text-gray-300">Posez vos questions</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3" x-ref="messages">
            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user' ? 'bg-primary-700 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300'" class="rounded-xl px-3 py-2 max-w-[80%] text-sm">
                        <span x-text="msg.content"></span>
                    </div>
                </div>
            </template>
            <div x-show="loading" class="flex justify-start">
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl px-3 py-2 text-sm text-gray-500">
                    <span class="animate-pulse">...</span>
                </div>
            </div>
        </div>

        <div class="p-3 border-t dark:border-gray-800">
            <div class="flex gap-2">
                <input type="text" x-model="input" @keydown.enter="send()" placeholder="Votre question..." class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-surface-darker text-sm focus:ring-accent-500 focus:border-accent-500">
                <button @click="send()" :disabled="!input.trim() || loading" class="px-3 py-2 rounded-lg bg-accent-500 text-primary-700 font-bold text-sm hover:bg-accent-400 transition disabled:opacity-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </div>
            <div class="mt-2 flex flex-wrap gap-1">
                <template x-for="q in suggestions" :key="q">
                    <button @click="input = q; send()" x-show="messages.length <= 1" class="text-xs px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition" x-text="q"></button>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function chatbot() {
    return {
        open: false,
        input: '',
        loading: false,
        messages: [
            { role: 'assistant', content: 'Bonjour ! Je suis l\'assistant IA de {{ $siteTitre }}. Comment puis-je vous aider ?' }
        ],
        suggestions: ['Quelles formations proposez-vous ?', 'Comment s\'inscrire ?', 'Comment payer ?'],
        async send() {
            if (!this.input.trim() || this.loading) return;
            const question = this.input.trim();
            this.messages.push({ role: 'user', content: question });
            this.input = '';
            this.loading = true;
            this.$nextTick(() => { this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight; });

            try {
                const res = await fetch('/api/chatbot', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: JSON.stringify({ question })
                });
                const data = await res.json();
                this.messages.push({ role: 'assistant', content: data.reponse || 'Désolé, je n\'ai pas pu traiter votre demande.' });
            } catch (e) {
                this.messages.push({ role: 'assistant', content: 'Une erreur est survenue. Veuillez réessayer.' });
            }

            this.loading = false;
            this.$nextTick(() => { this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight; });
        }
    };
}
</script>
