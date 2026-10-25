<?php

namespace App\Http\Controllers\Apprenant;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\Inscription;
use App\Services\FormationProgressService;

class DashboardController extends Controller
{
    public function redirection()
    {
        return redirect()->route(auth()->user()->isGestionnaire() ? 'admin.dashboard' : 'apprenant.dashboard');
    }

    public function ping()
    {
        return response()->noContent();
    }

    public function index(FormationProgressService $progress)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $inscriptions = Formation::all()->map(fn($f) => Inscription::firstOrCreate(
                ['user_id' => $user->id, 'formation_id' => $f->id],
                ['statut' => 'active']
            ));
            $inscriptions = new \Illuminate\Database\Eloquent\Collection($inscriptions->all());
        } else {
            $inscriptions = Inscription::with('formation')
                ->where('user_id', $user->id)
                ->whereIn('statut', ['active', 'terminee'])
                ->orderByDesc('updated_at')
                ->get();
        }

        $inscriptions->load('formation.modules.lecons', 'formation.modules.quiz.questions');
        $ctx = $progress->contexte($user);

        $cartes = $inscriptions->map(function ($inscription) use ($user, $progress, $ctx) {
            $status = $progress->status($user, $inscription->formation, $ctx);
            $lecon = $status['next_lecon'];

            return [
                'inscription' => $inscription,
                'formation' => $inscription->formation,
                'status' => $status,
                'url' => $lecon
                    ? route('apprenant.cours.lecon', [$inscription, $lecon])
                    : route('apprenant.cours.index'),
            ];
        });

        $certificatsCount = $user->certificats()->count();
        $flagsCount = $user->flagsCaptures()->count();

        $reprise = $cartes->first(fn($c) => $c['status']['pct'] > 0 && $c['status']['pct'] < 100)
            ?? $cartes->first(fn($c) => $c['status']['pct'] < 100);

        $notificationsNonLues = $user->notificationsInternes()
            ->where('is_read', false)
            ->count();

        return view('apprenant.dashboard', compact(
            'user', 'cartes', 'certificatsCount', 'flagsCount',
            'reprise', 'notificationsNonLues'
        ));
    }
}
