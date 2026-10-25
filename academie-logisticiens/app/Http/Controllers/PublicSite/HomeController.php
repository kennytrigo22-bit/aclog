<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\Parametre;
use App\Models\Partenaire;

class HomeController extends Controller
{
    public function index()
    {
        $formationsPopulaires = Formation::where('is_published', true)
            ->where('is_featured', true)
            ->orderBy('ordre')
            ->take(4)
            ->get();

        $stats = [
            'apprenants' => \App\Models\User::where('role', 'apprenant')->count(),
            'formations' => Formation::where('is_published', true)->count(),
            'certificats' => \App\Models\Certificat::count(),
        ];

        $partenaires = Partenaire::actifs()->get();

        return view('public.home', compact('formationsPopulaires', 'stats', 'partenaires'));
    }
}
