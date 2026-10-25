<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\FlagCapture;
use App\Models\NotificationInterne;
use App\Models\Parametre;
use App\Models\User;

class GamificationService
{
    public function ajouterPoints(User $user, int $points, string $raison): void
    {
        $user->increment('points', $points);

        NotificationInterne::create([
            'user_id' => $user->id,
            'type' => 'points_gagnes',
            'titre' => "+{$points} points gagnés !",
            'message' => $raison,
            'created_at' => now(),
        ]);

        $this->verifierSeuilJetons($user->fresh());
    }

    private function verifierSeuilJetons(User $user): void
    {
        $seuil = (int) Parametre::valeur('seuil_jetons', 100);
        $valeur = (int) Parametre::valeur('valeur_jetons_fcfa', 5000);

        if ($user->points >= $seuil) {
            $nbConversions = intdiv($user->points, $seuil);
            $jetonsGagnes = $nbConversions * $valeur;
            $pointsRestants = $user->points % $seuil;

            $user->update([
                'points' => $pointsRestants,
                'jetons' => $user->jetons + $jetonsGagnes,
            ]);

            NotificationInterne::create([
                'user_id' => $user->id,
                'type' => 'jetons_credites',
                'titre' => "{$jetonsGagnes} FCFA de jetons crédités !",
                'message' => "Félicitations ! Vos {$seuil} points ont été convertis en {$jetonsGagnes} FCFA de jetons utilisables sur vos prochaines formations.",
                'created_at' => now(),
            ]);
        }
    }

    /** Capture le drapeau d'une étape (une seule fois). Retourne les points gagnés, 0 si déjà capturé. */
    public function capturerEtape(User $user, Challenge $challenge): int
    {
        $capture = FlagCapture::firstOrCreate(
            ['user_id' => $user->id, 'challenge_id' => $challenge->id],
            ['flag_soumis' => $challenge->flag_attendu, 'captured_at' => now()]
        );

        if (!$capture->wasRecentlyCreated) {
            return 0;
        }

        $points = (int) ($challenge->points ?: Parametre::valeur('points_flag_capture', 5));
        $this->ajouterPoints($user, $points, "Drapeau capturé : {$challenge->titre}");

        return $points;
    }

    public function testReussi(User $user): void
    {
        $points = (int) Parametre::valeur('points_test_reussi', 2);
        $this->ajouterPoints($user, $points, 'Test de niveau réussi');
    }
}
