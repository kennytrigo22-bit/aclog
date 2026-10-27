<?php

namespace App\Services;

use App\Models\Certificat;
use App\Models\Formation;
use App\Models\Inscription;
use App\Models\NotificationInterne;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificatService
{
    public function generer(User $user, Formation $formation, int $score): Certificat
    {
        $certificat = $this->creer([
            'user_id' => $user->id,
            'formation_id' => $formation->id,
            'score_obtenu' => $score,
        ], $user->pays);

        NotificationInterne::create([
            'user_id' => $user->id,
            'type' => 'certificat_obtenu',
            'titre' => 'Certificat obtenu !',
            'message' => "Félicitations ! Vous avez obtenu votre certificat pour la formation « {$formation->titre} » avec un score de {$score}%.",
            'created_at' => now(),
        ]);

        return $certificat;
    }

    /**
     * Certificat d'un apprenant forme en presentiel (sans compte obligatoire) : meme numerotation et meme
     * code de verification que les certificats en ligne, il apparait donc dans l'annuaire et se verifie par QR Code.
     */
    public function genererPresentiel(array $donnees): Certificat
    {
        return $this->creer(array_merge(['score_obtenu' => 100], $donnees, ['mode' => Certificat::MODE_PRESENTIEL]), $donnees['titulaire_pays'] ?? null);
    }

    public function pdf(Certificat $certificat): \Barryvdh\DomPDF\PDF
    {
        // DomPDF decode l'image du cadre en memoire : 128 Mo (limite courante) ne suffit pas toujours.
        @ini_set('memory_limit', '384M');

        $certificat->load(['user', 'formation']);

        $qrCode = 'data:image/svg+xml;base64,' . base64_encode(
            QrCode::format('svg')->size(80)->generate(route('certificat.verifier', $certificat->code_verification))
        );

        $inscription = $certificat->user_id && $certificat->formation_id
            ? Inscription::where('user_id', $certificat->user_id)->where('formation_id', $certificat->formation_id)->first()
            : null;

        return Pdf::loadView('pdf.certificat', compact('certificat', 'qrCode', 'inscription'))->setPaper('a4', 'landscape');
    }

    public function nomFichier(Certificat $certificat): string
    {
        return 'certificat-' . $certificat->code_verification . '.pdf';
    }

    private function creer(array $attributs, ?string $pays): Certificat
    {
        $code = Str::upper(Str::random(8)) . '-' . Str::upper(Str::random(8));

        return DB::transaction(function () use ($attributs, $pays, $code) {
            $annee = now()->year;
            $max = Certificat::whereYear('issued_at', $annee)->lockForUpdate()->pluck('numero')
                ->map(fn ($n) => preg_match('/-(\d{3,})[A-Z]{2}$/', (string) $n, $m) ? (int) $m[1] : 0)->max() ?? 0;

            return Certificat::create(array_merge($attributs, [
                'code_verification' => $code,
                'numero' => Certificat::formaterNumero($annee, $max + 1, $pays),
                'issued_at' => $attributs['issued_at'] ?? now(),
            ]));
        });
    }
}
