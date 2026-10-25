<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partenaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartenaireController extends Controller
{
    public function index()
    {
        $partenaires = Partenaire::orderBy('ordre')->get();
        return view('admin.partenaires.index', compact('partenaires'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $path = $request->file('logo')->store('partenaires', 'public');

        Partenaire::create([
            'nom' => $request->nom,
            'logo' => $path,
            'ordre' => Partenaire::max('ordre') + 1,
        ]);

        return back()->with('success', 'Partenaire ajouté.');
    }

    public function destroy(Partenaire $partenaire)
    {
        Storage::disk('public')->delete($partenaire->logo);
        $partenaire->delete();

        return back()->with('success', 'Partenaire supprimé.');
    }

    public function updateOrdre(Request $request)
    {
        $request->validate(['ordre' => 'required|array']);

        foreach ($request->ordre as $position => $id) {
            Partenaire::where('id', $id)->update(['ordre' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    public function toggleActif(Partenaire $partenaire)
    {
        $partenaire->update(['actif' => !$partenaire->actif]);
        return back()->with('success', 'Statut mis à jour.');
    }
}
