<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Support\Catalogue;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function index(Request $request)
    {
        $query = Formation::where('is_published', true)->withCount('modules');

        // Anciens liens filtres (?categorie=douane, ?niveau=debutant) toujours acceptes
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }
        if ($request->filled('niveau')) {
            $query->where('niveau', $request->niveau);
        }

        $publiees = $query->orderBy('ordre')->get();
        $filieres = Catalogue::filieres($publiees);
        $vedette = $publiees->firstWhere('is_featured', true) ?? $publiees->first();

        return view('public.formations.index', compact('filieres', 'vedette'));
    }

    public function show(Formation $formation)
    {
        if (!$formation->is_published) {
            abort(404);
        }

        $formation->load(['modules.lecons', 'examen']);

        return view('public.formations.show', compact('formation'));
    }
}
