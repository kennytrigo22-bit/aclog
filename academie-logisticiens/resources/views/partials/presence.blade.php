{{-- Signal de vie toutes les 60 s tant que l'onglet est visible : compteur "en ligne maintenant" de l'admin --}}
<script>
(function () {
    var jeton = document.querySelector('meta[name="csrf-token"]');
    if (!jeton || !window.fetch) return;
    function signal() {
        if (document.visibilityState !== 'visible') return;
        fetch('{{ route('presence') }}', {
            method: 'POST', credentials: 'same-origin', keepalive: true,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': jeton.content, 'Accept': 'application/json' },
            body: JSON.stringify({ chemin: location.pathname })
        }).catch(function () {});
    }
    setInterval(signal, 60000);
    document.addEventListener('visibilitychange', signal);
})();
</script>
