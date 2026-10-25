<?php

// '*' (tunnel de test), ou liste d'IP/CIDR separees par des virgules (plages Cloudflare / reverse proxy). Vide = aucun proxy approuve.
$valeur = env('TRUSTED_PROXIES');

return [
    'proxies' => $valeur === '*' ? '*' : ($valeur ? array_map('trim', explode(',', $valeur)) : null),
];
