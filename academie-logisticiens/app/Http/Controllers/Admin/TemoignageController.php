<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Temoignage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Temoignages de l'accueil, geres depuis la page Parametres. */
class TemoignageController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'citation' => ['required', 'string', 'max:600'],
            'nom' => ['required', 'string', 'max:120'],
            'fonction' => ['nullable', 'string', 'max:160'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], ['citation' => 'témoignage', 'fonction' => 'fonction / ville']);

        // Les guillemets « » sont ajoutes a l'affichage : on retire ceux eventuellement saisis.
        $data['citation'] = trim(preg_replace('/^[«"“\s]+|[»"”\s]+$/u', '', $data['citation']));
        $data['photo'] = $request->file('photo')?->store('temoignages', 'public');
        $data['ordre'] = (int) Temoignage::max('ordre') + 1;

        Temoignage::create($data);

        return redirect(route('admin.parametres') . '#temoignages')->with('success', 'Témoignage ajouté : il apparaît sur la page d\'accueil.');
    }

    public function toggle(Temoignage $temoignage)
    {
        $temoignage->update(['actif' => !$temoignage->actif]);

        return redirect(route('admin.parametres') . '#temoignages')->with('success', $temoignage->actif ? 'Témoignage affiché sur l\'accueil.' : 'Témoignage masqué de l\'accueil.');
    }

    public function destroy(Temoignage $temoignage)
    {
        if ($temoignage->photo) {
            Storage::disk('public')->delete($temoignage->photo);
        }
        $temoignage->delete();

        return redirect(route('admin.parametres') . '#temoignages')->with('success', 'Témoignage supprimé.');
    }
}
