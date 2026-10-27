<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Certificat;
use Illuminate\Support\Collection;

class AnnuaireController extends Controller
{
    public function aPropos()
    {
        $certifies = $this->certifiesPublics()->get();
        $statsAnnuaire = $this->stats($certifies);

        return view('public.a-propos', compact('certifies', 'statsAnnuaire'));
    }

    public function annuaire()
    {
        $query = $this->certifiesPublics();

        if ($q = trim((string) request('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('titulaire_nom', 'like', "%{$q}%")
                    ->orWhere('titulaire_prenom', 'like', "%{$q}%")
                    ->orWhere('titulaire_pays', 'like', "%{$q}%")
                    ->orWhere('formation_titre', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('prenom', 'like', "%{$q}%")->orWhere('pays', 'like', "%{$q}%"))
                    ->orWhereHas('formation', fn ($f) => $f->where('titre', 'like', "%{$q}%"));
            });
        }

        $certifiesPublics = $query->paginate(30)->withQueryString();

        $certificatRecherche = null;
        if ($code = trim((string) request('code'))) {
            $certificatRecherche = Certificat::with(['user', 'formation'])
                ->where('code_verification', $code)
                ->orWhere('numero', $code)
                ->first();
        }

        $statsAnnuaire = $this->stats($this->certifiesPublics()->get());

        return view('public.annuaire-certifies', compact('certifiesPublics', 'certificatRecherche', 'statsAnnuaire'));
    }

    /** Tous les certifies (en ligne et presentiel), sauf ceux qui ont choisi d'etre masques. */
    private function certifiesPublics()
    {
        return Certificat::with(['user', 'formation'])
            ->where('is_public', true)
            ->orderByDesc('issued_at')
            ->orderByDesc('id');
    }

    private function stats(Collection $certifies): array
    {
        return [
            'etudiants' => $certifies->map(fn ($c) => $c->user_id ? 'u' . $c->user_id : 'p' . mb_strtolower($c->nom_complet))->unique()->count(),
            'pays' => $certifies->pluck('pays')->filter()->unique()->count(),
            'formations' => $certifies->pluck('intitule_formation')->filter()->unique()->count(),
        ];
    }
}
