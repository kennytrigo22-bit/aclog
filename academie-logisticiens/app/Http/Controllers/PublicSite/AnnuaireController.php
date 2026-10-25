<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Certificat;
use App\Models\Formation;

class AnnuaireController extends Controller
{
    public function aPropos()
    {
        $totalCertifies = Certificat::count();

        return view('public.a-propos', compact('totalCertifies'));
    }

    public function annuaire()
    {
        $query = Certificat::with(['user', 'formation'])
            ->where('is_public', true)
            ->orderByDesc('issued_at');

        if ($q = request('q')) {
            $query->whereHas('user', function ($u) use ($q) {
                $u->where('name', 'like', "%{$q}%");
            });
        }

        $certifiesPublics = $query->paginate(20)->withQueryString();

        $certificatRecherche = null;
        if ($code = request('code')) {
            $certificatRecherche = Certificat::with(['user', 'formation'])
                ->where('code_verification', $code)
                ->first();
        }

        $totalCertifies = Certificat::count();
        $totalFormations = Formation::count();
        $totalPublics = Certificat::where('is_public', true)->count();

        return view('public.annuaire-certifies', compact(
            'certifiesPublics', 'certificatRecherche',
            'totalCertifies', 'totalFormations', 'totalPublics'
        ));
    }
}
