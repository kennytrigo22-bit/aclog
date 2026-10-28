<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\Ticket;
use App\Services\StatistiquesAdmin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, StatistiquesAdmin $statistiques)
    {
        $stats = $statistiques->calculer((string) $request->query('periode', 'mois'), $request->query('pays'));
        $periodes = StatistiquesAdmin::PERIODES;

        $paiementsRecents = Paiement::with(['user', 'formation'])
            ->where('statut', 'en_attente')
            ->latest()
            ->take(10)
            ->get();

        $ticketsRecents = Ticket::with('user')
            ->where('statut', '!=', 'ferme')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'periodes', 'paiementsRecents', 'ticketsRecents'));
    }

    /** Rafraichissement des graphiques et indicateurs (filtres periode / pays). */
    public function statistiques(Request $request, StatistiquesAdmin $statistiques): JsonResponse
    {
        return response()->json($statistiques->calculer((string) $request->query('periode', 'mois'), $request->query('pays')));
    }

    /** Compteur "en ligne maintenant", interroge toutes les 15 secondes. */
    public function enLigne(StatistiquesAdmin $statistiques): JsonResponse
    {
        return response()->json($statistiques->enLigne());
    }
}
