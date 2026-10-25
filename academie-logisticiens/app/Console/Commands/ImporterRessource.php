<?php

namespace App\Console\Commands;

use App\Models\Formation;
use App\Models\FormationRessource;
use Illuminate\Console\Command;

class ImporterRessource extends Command
{
    protected $signature = 'formation:ressource {fichier : JSON (slug, titre, type, html, fichier, nom_fichier, mime)}';

    protected $description = 'Ajoute ou remplace une ressource (fiche d\'entretien...) d\'une formation.';

    public function handle(): int
    {
        $d = json_decode(file_get_contents($this->argument('fichier')), true);
        $formation = Formation::where('slug', $d['slug'] ?? '')->first();

        if (!$formation) {
            $this->error('Formation introuvable : ' . ($d['slug'] ?? '?'));
            return self::FAILURE;
        }

        FormationRessource::updateOrCreate(
            ['formation_id' => $formation->id, 'type' => $d['type']],
            [
                'titre' => $d['titre'],
                'contenu_html' => $d['html'] ?? null,
                'fichier' => $d['fichier'] ?? null,
                'nom_fichier' => $d['nom_fichier'] ?? null,
                'mime' => $d['mime'] ?? null,
                'ordre' => 1,
            ]
        );

        $this->info("Ressource « {$d['titre']} » enregistrée pour {$formation->titre}.");

        return self::SUCCESS;
    }
}
