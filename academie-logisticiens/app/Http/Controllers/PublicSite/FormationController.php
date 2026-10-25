<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function index(Request $request)
    {
        $query = Formation::where('is_published', true);

        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }
        if ($request->filled('niveau')) {
            $query->where('niveau', $request->niveau);
        }

        $formations = $query->orderBy('ordre')->paginate(12);

        return view('public.formations.index', compact('formations'));
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
