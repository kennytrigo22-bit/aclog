<?php

namespace App\Http\Controllers\Apprenant;

use App\Http\Controllers\Controller;
use App\Models\Certificat;

class CertificatController extends Controller
{
    public function index()
    {
        $certificats = Certificat::with('formation')
            ->where('user_id', auth()->id())
            ->orderByDesc('issued_at')
            ->get();

        return view('apprenant.certificats.index', compact('certificats'));
    }

    public function visibilite(Certificat $certificat)
    {
        abort_unless($certificat->user_id === auth()->id(), 403);
        $certificat->update(['is_public' => !$certificat->is_public]);

        return back()->with('success', $certificat->is_public ? 'Vous apparaissez dans l\'annuaire des certifies.' : 'Vous etes retire de l\'annuaire.');
    }

    public function download(Certificat $certificat)
    {
        if (auth()->user()->role !== 'admin' && $certificat->user_id !== auth()->id()) {
            abort(403);
        }

        $service = app(\App\Services\CertificatService::class);

        return $service->pdf($certificat)->download($service->nomFichier($certificat));
    }

    public function verifier(string $code)
    {
        $certificat = Certificat::with(['user', 'formation'])
            ->where('code_verification', $code)
            ->first();

        return view('public.certificat-verification', compact('certificat', 'code'));
    }
}
