<?php

namespace App\Http\Controllers\Apprenant;

use App\Http\Controllers\Controller;
use App\Models\CodePromo;
use App\Models\Formation;
use App\Models\Inscription;
use App\Models\NotificationInterne;
use App\Models\Paiement;
use App\Models\Parametre;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function initier(Formation $formation)
    {
        $user = auth()->user();

        abort_unless($formation->is_published || $user->role === 'admin', 404);

        $dejaInscrit = Inscription::where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->whereIn('statut', ['active', 'terminee'])
            ->exists();

        if ($dejaInscrit) {
            return redirect()->route('apprenant.cours.index')
                ->with('info', 'Vous êtes déjà inscrit à cette formation.');
        }

        $numeroPaiement = Parametre::valeur('numero_paiement', '+242 068 287 915');
        $nomBeneficiaire = Parametre::valeur('nom_beneficiaire', 'Didi Chance AMBIA');
        $operateurs = Parametre::valeur('operateurs_mobile_money', 'MTN et Airtel');
        $delai = Parametre::valeur('delai_validation_paiement', 30);

        return view('apprenant.paiement.initier', compact(
            'formation', 'user', 'numeroPaiement', 'nomBeneficiaire', 'operateurs', 'delai'
        ));
    }

    public function confirmer(Request $request, Formation $formation)
    {
        $request->validate([
            'capture_paiement' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'code_promo' => 'nullable|string|max:50',
            'utiliser_jetons' => 'nullable|boolean',
        ]);

        $user = auth()->user();

        abort_unless($formation->is_published, 404);

        $dejaAcces = Inscription::where('user_id', $user->id)->where('formation_id', $formation->id)
            ->whereIn('statut', ['active', 'terminee'])->exists();
        if ($dejaAcces) {
            return redirect()->route('apprenant.cours.index')->with('info', 'Vous etes deja inscrit a cette formation.');
        }

        $enAttente = Paiement::where('user_id', $user->id)->where('formation_id', $formation->id)
            ->where('statut', 'en_attente')->exists();
        if ($enAttente) {
            return redirect()->route('apprenant.dashboard')->with('info', 'Un paiement est deja en attente de validation pour cette formation.');
        }

        $montant = $formation->prix_effectif;
        $codePromo = null;
        $montantPromo = null;
        $jetonsUtilises = 0;

        if ($request->filled('code_promo')) {
            $codePromo = CodePromo::where('code', $request->code_promo)->first();
            if ($codePromo && $codePromo->estValide() && $codePromo->seAppliqueA($formation)) {
                if ($codePromo->type === 'pourcentage') {
                    $montantPromo = max(0, $montant - round($montant * min(100, $codePromo->valeur) / 100));
                } else {
                    $montantPromo = max(0, $montant - $codePromo->valeur);
                }
                $montant = $montantPromo;
            } else {
                $codePromo = null;
            }
        }

        $jetonsReserves = (int) Paiement::where('user_id', $user->id)->where('statut', 'en_attente')->sum('jetons_utilises');
        $jetonsDisponibles = max(0, $user->jetons - $jetonsReserves);

        if ($request->boolean('utiliser_jetons') && $jetonsDisponibles > 0) {
            $jetonsUtilises = min($jetonsDisponibles, $montant);
            $montant -= $jetonsUtilises;
        }

        $path = $request->file('capture_paiement')->store('paiements', 'local');

        $paiement = Paiement::create([
            'user_id' => $user->id,
            'formation_id' => $formation->id,
            'montant' => $formation->prix_effectif,
            'montant_promo' => $montantPromo,
            'code_promo_id' => $codePromo?->id,
            'jetons_utilises' => $jetonsUtilises,
            'capture_paiement' => $path,
            'statut' => 'en_attente',
        ]);

        Inscription::updateOrCreate(
            ['user_id' => $user->id, 'formation_id' => $formation->id],
            ['statut' => 'en_attente']
        );

        NotificationInterne::create([
            'user_id' => $user->id,
            'type' => 'paiement_recu',
            'titre' => 'Commande reçue',
            'message' => "Votre commande pour « {$formation->titre} » a été reçue. Validation sous " . Parametre::valeur('delai_validation_paiement', 30) . " minutes maximum.",
            'created_at' => now(),
        ]);

        if ($codePromo) {
            $codePromo->increment('nb_utilisations_actuel');
        }

        return view('apprenant.paiement.confirmation', compact('paiement', 'formation'));
    }
}
