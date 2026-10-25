<?php

namespace App\Services;

use App\Models\Formation;
use App\Models\Parametre;

class OrientationIAService
{
    public function recommander(string $domaine, string $niveau, int $score): ?array
    {
        $scoreMin = (int) Parametre::valeur('score_minimum_defaut', 70);

        $categorie = match ($domaine) {
            'logistique' => 'logistique',
            'commerce' => 'commerce',
            'douane' => 'douane',
            default => 'logistique',
        };

        if ($score < $scoreMin) {
            $niveauRecommande = 'debutant';
            $explication = "Votre score de {$score}% indique que les bases doivent être renforcées. Nous vous recommandons de commencer par une formation de niveau débutant pour consolider vos fondamentaux.";
        } elseif ($score < 80) {
            $niveauRecommande = 'debutant';
            $explication = "Bon début ! Avec un score de {$score}%, vous avez les bases mais certains concepts méritent d'être approfondis. Une formation de niveau débutant vous permettra de maîtriser tous les fondamentaux.";
        } elseif ($score < 90) {
            $niveauRecommande = 'intermediaire';
            $explication = "Très bien ! Votre score de {$score}% montre de solides connaissances. Vous êtes prêt(e) pour une formation de niveau intermédiaire qui approfondira vos compétences.";
        } else {
            $niveauRecommande = 'avance';
            $explication = "Excellent ! Avec {$score}%, vous maîtrisez très bien ce domaine. Une formation avancée vous permettra d'atteindre un niveau d'expertise professionnelle.";
        }

        $formation = Formation::where('categorie', $categorie)
            ->where('niveau', $niveauRecommande)
            ->where('is_published', true)
            ->first();

        return [
            'score' => $score,
            'passed' => $score >= $scoreMin,
            'niveau_recommande' => $niveauRecommande,
            'explication' => $explication,
            'formation' => $formation,
        ];
    }
}
