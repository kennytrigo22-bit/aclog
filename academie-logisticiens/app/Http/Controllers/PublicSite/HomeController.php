<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Support\Catalogue;

class HomeController extends Controller
{
    public function index()
    {
        $publiees = Formation::where('is_published', true)->withCount('modules')->orderBy('ordre')->get();

        // Formation a la une : la premiere mise en avant, sinon la premiere publiee
        $aLaUne = $publiees->firstWhere('is_featured', true) ?? $publiees->first();

        $nbFormations = Catalogue::total($publiees);

        return view('public.home', compact('aLaUne', 'nbFormations'));
    }
}
