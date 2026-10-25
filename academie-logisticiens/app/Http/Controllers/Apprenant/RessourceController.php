<?php

namespace App\Http\Controllers\Apprenant;

use App\Http\Controllers\Controller;
use App\Models\FormationRessource;
use App\Models\Inscription;
use Illuminate\Support\Facades\Storage;

class RessourceController extends Controller
{
    public function show(FormationRessource $ressource)
    {
        $this->autoriser($ressource);
        $ressource->load('formation');

        return view('apprenant.ressources.show', compact('ressource'));
    }

    public function telecharger(FormationRessource $ressource)
    {
        $this->autoriser($ressource);

        $disk = Storage::disk('local');
        abort_unless($ressource->fichier && $disk->exists($ressource->fichier), 404);

        return $disk->download($ressource->fichier, $ressource->nom_fichier ?: basename($ressource->fichier), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function afficherPdf(FormationRessource $ressource)
    {
        $this->autoriser($ressource);

        $disk = Storage::disk('local');
        abort_unless($ressource->estPdf() && $ressource->fichier && $disk->exists($ressource->fichier), 404);

        return $disk->response($ressource->fichier, $ressource->nom_fichier, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function autoriser(FormationRessource $ressource): void
    {
        $user = auth()->user();
        if ($user->role === 'admin') {
            return;
        }

        $inscrit = Inscription::where('user_id', $user->id)
            ->where('formation_id', $ressource->formation_id)
            ->whereIn('statut', ['active', 'terminee'])
            ->exists();

        abort_unless($inscrit, 403);
    }
}
