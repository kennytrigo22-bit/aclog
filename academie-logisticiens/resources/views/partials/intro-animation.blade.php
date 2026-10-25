<div id="intro-overlay"
     class="fixed inset-0 z-[9999] bg-white dark:bg-surface-darker transition-opacity duration-700"
     x-data="introAnimation()"
     x-init="init()">

    <div class="w-full h-full flex items-center justify-center">
        <img id="intro-img" alt="" width="480" height="270" decoding="async" fetchpriority="low"
             class="max-w-[90vw] max-h-[80vh] md:max-w-[600px] md:max-h-[500px] object-contain">
    </div>

    <button type="button" @click="finish()" class="absolute bottom-6 right-6 px-4 py-2 rounded-full text-xs font-bold bg-primary-700 text-white dark:bg-accent-500 dark:text-primary-800 shadow hover:opacity-90 transition">
        Passer l'animation
    </button>
</div>

<script>
function introAnimation() {
    return {
        init() {
            // Deja vue pendant cette session : ne rien telecharger
            if (document.documentElement.classList.contains('intro-seen')) return;
            const dark = document.documentElement.classList.contains('dark');
            const img = document.getElementById('intro-img');
            let started = false;
            const lancer = () => { if (started) return; started = true; setTimeout(() => this.finish(), 4500); };
            img.addEventListener('load', lancer);
            img.addEventListener('error', () => this.finish());
            img.src = '{{ asset('assets/animations') }}/' + (dark ? 'intro-dark.webp' : 'intro-light.webp');
            if (img.complete && img.naturalWidth) lancer();
            // Securite : ne jamais bloquer le site plus de 5 secondes sur une connexion lente
            setTimeout(() => this.finish(), 5000);
        },
        finish() {
            const overlay = document.getElementById('intro-overlay');
            const site = document.getElementById('main-site');
            if (!overlay || !site || overlay.dataset.done) return;
            overlay.dataset.done = '1';
            try { sessionStorage.setItem('intro_seen', '1'); } catch (e) {}
            site.style.display = 'block';
            site.style.opacity = '0';
            site.style.transition = 'opacity 0.5s ease';
            overlay.style.opacity = '0';
            setTimeout(() => {
                site.style.opacity = '1';
                overlay.style.display = 'none';
            }, 500);
        }
    }
}
</script>
