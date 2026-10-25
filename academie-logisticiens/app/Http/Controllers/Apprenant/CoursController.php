<?php

namespace App\Http\Controllers\Apprenant;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use App\Models\Lecon;
use App\Models\Progression;
use Illuminate\Http\Request;

class CoursController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $formations = \App\Models\Formation::with('modules.lecons')->get();
            $inscriptions = $formations->map(function ($formation) use ($user) {
                return Inscription::firstOrCreate(
                    ['user_id' => $user->id, 'formation_id' => $formation->id],
                    ['statut' => 'active']
                );
            });
        } else {
            $inscriptions = Inscription::where('user_id', $user->id)
                ->orderByDesc('updated_at')
                ->get();
        }

        $inscriptions = new \Illuminate\Database\Eloquent\Collection($inscriptions->all());
        $inscriptions->load('formation.modules.lecons', 'formation.modules.quiz.questions', 'formation.examenTest', 'formation.examenFinal', 'formation.ressources');

        return view('apprenant.cours.index', compact('inscriptions'));
    }

    public function lecon(Inscription $inscription, Lecon $lecon)
    {
        $user = auth()->user();
        if ($user->role !== 'admin' && ($inscription->user_id !== $user->id || !in_array($inscription->statut, ['active', 'terminee']))) {
            abort(403);
        }

        abort_unless($lecon->module->formation_id === $inscription->formation_id, 404);

        $formation = $inscription->formation->load('modules.lecons', 'modules.quiz.questions', 'examenTest', 'examenFinal', 'ressources');
        $progression = Progression::where('user_id', auth()->id())
            ->where('lecon_id', $lecon->id)
            ->first();

        $progressionFormation = $this->calculerProgression($inscription);

        return view('apprenant.cours.lecon', compact(
            'inscription', 'lecon', 'formation', 'progression', 'progressionFormation'
        ));
    }

    public function sauvegarder(Request $request)
    {
        $request->validate([
            'lecon_id' => 'required|exists:lecons,id',
            'position_video' => 'integer|min:0',
            'is_completed' => 'boolean',
        ]);

        $user = auth()->user();
        $formationId = Lecon::with('module')->findOrFail($request->lecon_id)->module->formation_id;
        $autorise = $user->role === 'admin' || Inscription::where('user_id', $user->id)
            ->where('formation_id', $formationId)
            ->whereIn('statut', ['active', 'terminee'])
            ->exists();
        abort_unless($autorise, 403);

        $progression = Progression::updateOrCreate(
            ['user_id' => auth()->id(), 'lecon_id' => $request->lecon_id],
            [
                'position_video' => $request->position_video ?? 0,
                'is_completed' => $request->is_completed ?? false,
                'completed_at' => $request->is_completed ? now() : null,
            ]
        );

        return response()->json(['success' => true, 'progression' => $progression]);
    }

    private function calculerProgression(Inscription $inscription): int
    {
        $totalLecons = $inscription->formation->modules->flatMap->lecons->count();
        if ($totalLecons === 0) return 0;

        $leconsCompletees = Progression::where('user_id', auth()->id())
            ->where('is_completed', true)
            ->whereIn('lecon_id', $inscription->formation->modules->flatMap->lecons->pluck('id'))
            ->count();

        return round(($leconsCompletees / $totalLecons) * 100);
    }
}
