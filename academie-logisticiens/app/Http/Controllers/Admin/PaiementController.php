<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Notifications\PaiementRejete;
use App\Notifications\PaiementValide;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function index(Request $request)
    {
        $query = Paiement::with(['user', 'formation']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $paiements = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.paiements.index', compact('paiements'));
    }

    public function capture(Paiement $paiement)
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        abort_unless($paiement->capture_paiement && $disk->exists($paiement->capture_paiement), 404);

        return $disk->response($paiement->capture_paiement);
    }

    public function valider(Paiement $paiement)
    {
        if ($paiement->statut === 'valide') {
            return back()->with('error', 'Ce paiement est deja valide.');
        }

        $paiement->update([
            'statut' => 'valide',
            'valide_par' => auth()->id(),
            'valide_at' => now(),
        ]);

        Inscription::updateOrCreate(
            ['user_id' => $paiement->user_id, 'formation_id' => $paiement->formation_id],
            ['statut' => 'active']
        );

        if ($paiement->jetons_utilises > 0) {
            $user = $paiement->user;
            $user->update(['jetons' => max(0, $user->jetons - $paiement->jetons_utilises)]);
        }

        try {
            $paiement->user->notify(new PaiementValide($paiement));
        } catch (\Throwable) {}

        return back()->with('success', 'Paiement validé et inscription activée.');
    }

    public function rejeter(Request $request, Paiement $paiement)
    {
        if ($paiement->statut === 'valide') {
            return back()->with('error', 'Un paiement deja valide ne peut plus etre rejete.');
        }

        $request->validate(['note_admin' => 'required|string|max:500']);

        $paiement->update([
            'statut' => 'rejete',
            'note_admin' => $request->note_admin,
            'valide_par' => auth()->id(),
            'valide_at' => now(),
        ]);

        try {
            $paiement->user->notify(new PaiementRejete($paiement));
        } catch (\Throwable) {}

        return back()->with('success', 'Paiement rejeté.');
    }
}
