<?php

namespace App\Http\Controllers;

use App\Services\SuiviVisites;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Signal de vie des pages ouvertes : alimente "en ligne maintenant" du tableau de bord admin. */
class PresenceController extends Controller
{
    public function __invoke(Request $request, SuiviVisites $suivi): Response
    {
        if ($request->user()?->role !== null && $request->user()->role !== 'apprenant') {
            return response()->noContent();
        }

        $chemin = (string) parse_url((string) $request->input('chemin', '/'), PHP_URL_PATH);
        $suivi->marquerPresence($request, chemin: $chemin !== '' ? $chemin : '/');

        return response()->noContent();
    }
}
