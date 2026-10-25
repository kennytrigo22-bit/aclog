<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\Ticket;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'apprenants' => User::where('role', 'apprenant')->count(),
            'formations' => Formation::where('is_published', true)->count(),
            'inscriptions' => Inscription::where('statut', 'active')->count(),
            'revenus' => Paiement::where('statut', 'valide')->sum('montant'),
            'paiements_en_attente' => Paiement::where('statut', 'en_attente')->count(),
            'tickets_ouverts' => Ticket::where('statut', 'ouvert')->count(),
        ];

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

        return view('admin.dashboard', compact('stats', 'paiementsRecents', 'ticketsRecents'));
    }
}
