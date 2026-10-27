<?php

namespace App\Support;

use App\Models\Formation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Catalogue public : les formations publiees en base, rangees dans les filieres de config/catalogue.php.
 */
class Catalogue
{
    /** @param Collection<int, Formation> $formations formations publiees a afficher */
    public static function filieres(Collection $formations): array
    {
        $filieres = config('catalogue', []);
        $index = collect($filieres)->pluck('cle')->flip();

        foreach ($filieres as $gi => $g) {
            $filieres[$gi]['items'] = [];
        }

        foreach ($formations as $f) {
            $gi = $index[Str::lower((string) $f->categorie)] ?? 0;
            $filieres[$gi]['items'][] = [
                'titre' => $f->titre,
                'description' => Str::limit(strip_tags((string) $f->description), 150),
                'prix' => $f->prix_effectif,
                'image' => $f->image_url,
                'url' => route('formations.show', $f),
                'bouton' => 'Voir programme',
                'meta' => '⏱ ' . ($f->duree_heures ? $f->duree_heures . ' h' : '2 mois') . ' · 🎓 Avec certificat',
                'badge' => '🎓 Certifiante',
            ];
        }

        return $filieres;
    }

    public static function total(Collection $formations): int
    {
        return $formations->count();
    }
}
