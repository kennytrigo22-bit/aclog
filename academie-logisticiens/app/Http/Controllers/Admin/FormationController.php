<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormationController extends Controller
{
    public function index()
    {
        $formations = Formation::withCount(['inscriptions', 'modules'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.formations.index', compact('formations'));
    }

    public function create()
    {
        return view('admin.formations.form', ['formation' => new Formation()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'programme' => 'nullable|string',
            'categorie' => 'required|string|max:100',
            'niveau' => 'required|in:debutant,intermediaire,avance',
            'prix' => 'required|integer|min:0',
            'prix_promo' => 'nullable|integer|min:0',
            'promo_active' => 'boolean',
            'duree_heures' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'badge' => 'nullable|string|max:50',
        ]);

        $data['slug'] = $this->slugUnique($data['titre']);
        $data['promo_active'] = $request->boolean('promo_active');
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('formations', 'public');
        }

        $formation = Formation::create($data);

        return redirect()->route('admin.formations.contenu', $formation)->with('success', 'Formation creee. Ajoutez maintenant le contenu.');
    }

    public function edit(Formation $formation)
    {
        return view('admin.formations.form', compact('formation'));
    }

    public function update(Request $request, Formation $formation)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'programme' => 'nullable|string',
            'categorie' => 'required|string|max:100',
            'niveau' => 'required|in:debutant,intermediaire,avance',
            'prix' => 'required|integer|min:0',
            'prix_promo' => 'nullable|integer|min:0',
            'promo_active' => 'boolean',
            'duree_heures' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'badge' => 'nullable|string|max:50',
        ]);

        $data['promo_active'] = $request->boolean('promo_active');
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('formations', 'public');
        }

        $formation->update($data);

        return redirect()->route('admin.formations.index')->with('success', 'Formation mise à jour.');
    }

    private function slugUnique(string $titre, ?int $ignorerId = null): string
    {
        $base = Str::slug($titre) ?: 'formation';
        $slug = $base;
        $i = 2;
        while (Formation::where('slug', $slug)->when($ignorerId, fn ($q) => $q->where('id', '!=', $ignorerId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function destroy(Formation $formation)
    {
        $formation->delete();
        return back()->with('success', 'Formation supprimée.');
    }
}
