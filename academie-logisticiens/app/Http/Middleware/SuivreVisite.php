<?php

namespace App\Http\Middleware;

use App\Services\SuiviVisites;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Enregistre chaque page HTML affichee pour les statistiques du tableau de bord admin. */
class SuivreVisite
{
    public function __construct(private SuiviVisites $suivi)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 200
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            && $this->suivi->doitSuivre($request)) {
            try {
                $this->suivi->enregistrerVisite($request);
            } catch (\Throwable $e) {
                // la mesure d'audience ne doit jamais empecher l'affichage d'une page
                report($e);
            }
        }

        return $response;
    }
}
