<div id="page-loader"
     class="fixed inset-0 z-[9998] flex items-center justify-center bg-white dark:bg-surface-darker transition-opacity duration-500"
     @if(request()->routeIs('home')) style="display:none" @endif>
    <div class="flex flex-col items-center gap-4">
        {{-- Animated book/graduation icon --}}
        <div class="relative w-16 h-16">
            <svg class="w-16 h-16 text-primary-700 dark:text-accent-400 animate-pulse" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M32 12L6 24l26 12 26-12L32 12z" fill="currentColor" opacity="0.2"/>
                <path d="M32 12L6 24l26 12 26-12L32 12z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                <path d="M12 28v14c0 2 8 8 20 8s20-6 20-8V28" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M52 28v18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <circle cx="52" cy="48" r="2" fill="currentColor"/>
            </svg>
            <div class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-accent-500 flex items-center justify-center">
                <svg class="w-3 h-3 text-primary-700 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.3"/>
                    <path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="font-heading font-bold text-sm text-primary-700 dark:text-white tracking-wider uppercase">Chargement</span>
            <span class="flex gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-accent-500 animate-bounce" style="animation-delay: 0s;"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-accent-500 animate-bounce" style="animation-delay: 0.15s;"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-accent-500 animate-bounce" style="animation-delay: 0.3s;"></span>
            </span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loader = document.getElementById('page-loader');
    if (!loader || loader.style.display === 'none') return;
    const site = document.getElementById('main-site');
    setTimeout(function() {
        loader.style.opacity = '0';
        setTimeout(function() {
            loader.remove();
        }, 500);
    }, 300);
});
</script>
