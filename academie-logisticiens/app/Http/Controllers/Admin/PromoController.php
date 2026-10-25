<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CodePromo;
use App\Models\Formation;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        $promos = CodePromo::with('formation')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.promos.index', compact('promos'));
    }

    public function create()
    {
        $formations = Formation::where('is_published', true)->pluck('titre', 'id');
        $categories = Formation::whereNotNull('categorie')->distinct()->pluck('categorie');
        return view('admin.promos.form', ['promo' => new CodePromo(), 'formations' => $formations, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:codes_promo,code',
            'type' => 'required|in:pourcentage,fixe',
            'valeur' => ['required', 'numeric', 'min:0', \Illuminate\Validation\Rule::when($request->input('type') === 'pourcentage', ['max:100'])],
            'perimetre' => 'required|in:all,formation,categorie',
            'formation_id' => 'required_if:perimetre,formation|nullable|exists:formations,id',
            'categorie' => 'required_if:perimetre,categorie|nullable|string|max:100',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'nb_utilisations_max' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['created_by'] = auth()->id();

        CodePromo::create($data);

        return redirect()->route('admin.promos.index')->with('success', 'Code promo créé.');
    }

    public function edit(CodePromo $promo)
    {
        $formations = Formation::where('is_published', true)->pluck('titre', 'id');
        $categories = Formation::whereNotNull('categorie')->distinct()->pluck('categorie');
        return view('admin.promos.form', compact('promo', 'formations', 'categories'));
    }

    public function update(Request $request, CodePromo $promo)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:codes_promo,code,' . $promo->id,
            'type' => 'required|in:pourcentage,fixe',
            'valeur' => ['required', 'numeric', 'min:0', \Illuminate\Validation\Rule::when($request->input('type') === 'pourcentage', ['max:100'])],
            'perimetre' => 'required|in:all,formation,categorie',
            'formation_id' => 'required_if:perimetre,formation|nullable|exists:formations,id',
            'categorie' => 'required_if:perimetre,categorie|nullable|string|max:100',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'nb_utilisations_max' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $promo->update($data);

        return redirect()->route('admin.promos.index')->with('success', 'Code promo mis à jour.');
    }

    public function destroy(CodePromo $promo)
    {
        $promo->delete();
        return back()->with('success', 'Code promo supprimé.');
    }
}
