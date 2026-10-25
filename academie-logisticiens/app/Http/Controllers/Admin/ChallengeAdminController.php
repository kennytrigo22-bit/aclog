<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\Parcours;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChallengeAdminController extends Controller
{
    public function index()
    {
        $parcours = Parcours::withCount('challenges')->orderBy('ordre')->get();
        return view('admin.challenges.index', compact('parcours'));
    }

    public function createParcours()
    {
        return view('admin.challenges.parcours-form', ['parcours' => null]);
    }

    public function storeParcours(Request $request)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'domaine' => 'required|string|max:100',
            'niveau' => 'required|in:debutant,intermediaire,avance',
            'is_active' => 'nullable',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data['slug'] = Str::slug($data['titre']);
        if (Parcours::where('slug', $data['slug'])->exists()) {
            $data['slug'] .= '-' . Str::lower(Str::random(4));
        }
        $data['is_active'] = isset($data['is_active']);
        $data['ordre'] = Parcours::max('ordre') + 1;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('parcours', 'public');
        }

        Parcours::create($data);

        return redirect()->route('admin.challenges.index')->with('success', 'Parcours cree avec succes.');
    }

    public function editParcours(Parcours $parcours)
    {
        return view('admin.challenges.parcours-form', compact('parcours'));
    }

    public function updateParcours(Request $request, Parcours $parcours)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'domaine' => 'required|string|max:100',
            'niveau' => 'required|in:debutant,intermediaire,avance',
            'is_active' => 'nullable',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data['is_active'] = isset($data['is_active']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('parcours', 'public');
        }

        $parcours->update($data);

        return redirect()->route('admin.challenges.index')->with('success', 'Parcours mis a jour.');
    }

    public function destroyParcours(Parcours $parcours)
    {
        $parcours->challenges()->get()->each->delete();
        $parcours->delete();
        return redirect()->route('admin.challenges.index')->with('success', 'Parcours supprime.');
    }

    public function show(Parcours $parcours)
    {
        $parcours->load(['challenges' => fn($q) => $q->withCount('questions')]);
        $captures = \App\Models\FlagCapture::whereIn('challenge_id', $parcours->challenges->pluck('id'))
            ->with('user:id,name,prenom')
            ->latest('captured_at')
            ->take(20)
            ->get();

        return view('admin.challenges.show', compact('parcours', 'captures'));
    }

    private function regles(): array
    {
        return [
            'titre' => 'required|string|max:255',
            'theme' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'points' => 'required|integer|min:1',
            'nb_questions' => 'required|integer|min:' . Challenge::MIN_QUESTIONS . '|max:50',
            'is_active' => 'nullable',
        ];
    }

    public function storeChallenge(Request $request, Parcours $parcours)
    {
        $data = $request->validate($this->regles());

        $data['is_active'] = isset($data['is_active']);
        $data['ordre'] = $parcours->challenges()->max('ordre') + 1;
        $data['flag_attendu'] = Challenge::genererFlag($data['titre']);

        $challenge = $parcours->challenges()->create($data);

        return redirect()->route('admin.qcm-import.index', ['cible' => 'etape:' . $challenge->id])
            ->with('success', 'Etape creee. Importez maintenant ses questions (minimum ' . Challenge::MIN_QUESTIONS . ') pour la rendre jouable.');
    }

    public function updateChallenge(Request $request, Challenge $challenge)
    {
        $data = $request->validate($this->regles());
        $data['is_active'] = isset($data['is_active']);
        $challenge->update($data);

        return back()->with('success', 'Etape mise a jour.');
    }

    public function destroyChallenge(Challenge $challenge)
    {
        $challenge->delete();
        return back()->with('success', 'Challenge supprime.');
    }
}
