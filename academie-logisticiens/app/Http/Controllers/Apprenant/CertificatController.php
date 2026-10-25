<?php

namespace App\Http\Controllers\Apprenant;

use App\Http\Controllers\Controller;
use App\Models\Certificat;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

        // DomPDF decode l'image du cadre en memoire : 128 Mo (limite courante) ne suffit pas toujours.
        @ini_set('memory_limit', '384M');

        $certificat->load(['user', 'formation']);

        $verificationUrl = route('certificat.verifier', $certificat->code_verification);

        $qrCode = 'data:image/svg+xml;base64,' . base64_encode(
            QrCode::format('svg')->size(80)->generate($verificationUrl)
        );

        $inscription = \App\Models\Inscription::where('user_id', $certificat->user_id)
            ->where('formation_id', $certificat->formation_id)->first();

        $pdf = Pdf::loadView('pdf.certificat', compact('certificat', 'qrCode', 'inscription'))
            ->setPaper('a4', 'landscape');

        $filename = 'certificat-' . $certificat->code_verification . '.pdf';

        return $pdf->download($filename);
    }

    public function verifier(string $code)
    {
        $certificat = Certificat::with(['user', 'formation'])
            ->where('code_verification', $code)
            ->first();

        return view('public.certificat-verification', compact('certificat', 'code'));
    }
}
