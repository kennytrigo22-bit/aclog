<?php

namespace App\Services;

use App\Models\Certificat;
use App\Models\Formation;
use App\Models\NotificationInterne;
use App\Models\User;
use Illuminate\Support\Str;

class CertificatService
{
    public function generer(User $user, Formation $formation, int $score): Certificat
    {
        $code = Str::upper(Str::random(8)) . '-' . Str::upper(Str::random(8));

        $certificat = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $formation, $code, $score) {
            $annee = now()->year;
            $max = Certificat::whereYear('issued_at', $annee)->lockForUpdate()->pluck('numero')
                ->map(fn ($n) => preg_match('/-(\d{3,})[A-Z]{2}$/', (string) $n, $m) ? (int) $m[1] : 0)->max() ?? 0;
            $sequence = $max + 1;

            return Certificat::create([
                'user_id' => $user->id,
                'formation_id' => $formation->id,
                'code_verification' => $code,
                'numero' => Certificat::formaterNumero($annee, $sequence, $user->pays),
                'score_obtenu' => $score,
                'issued_at' => now(),
            ]);
        });

        NotificationInterne::create([
            'user_id' => $user->id,
            'type' => 'certificat_obtenu',
            'titre' => 'Certificat obtenu !',
            'message' => "Félicitations ! Vous avez obtenu votre certificat pour la formation « {$formation->titre} » avec un score de {$score}%.",
            'created_at' => now(),
        ]);

        return $certificat;
    }
}
