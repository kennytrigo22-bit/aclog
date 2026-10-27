<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use Illuminate\Http\Request;

class ParametreController extends Controller
{
    public function index()
    {
        $parametres = Parametre::orderBy('cle')->get();
        $temoignages = \App\Models\Temoignage::orderBy('ordre')->orderBy('id')->get();

        return view('admin.parametres', compact('parametres', 'temoignages'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'parametres' => 'required|array',
            'parametres.*.cle' => 'required|string',
            'parametres.*.valeur' => 'nullable|string',
        ]);

        foreach ($request->parametres as $param) {
            Parametre::definir($param['cle'], $param['valeur'] ?? '');
        }

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
