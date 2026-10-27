<?php

namespace App\Support;

use App\Models\Formation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Catalogue public : les 4 filieres de config/catalogue.php, completees par les formations publiees en base.
 * Une formation en base remplace l'entree du catalogue de meme titre et s'affiche en tete de sa filiere.
 */
class Catalogue
{
    /** @param Collection<int, Formation> $formations formations publiees a integrer */
    public static function filieres(Collection $formations): array
    {
        $filieres = config('catalogue', []);
        $index = collect($filieres)->pluck('cle')->flip();
        $titresEnBase = $formations->map(fn ($f) => Str::lower(Str::ascii($f->titre)))->all();

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
                'externe' => false,
                'meta' => '⏱ ' . ($f->duree_heures ? $f->duree_heures . ' h' : '2 mois') . ' · 🎓 Avec certificat',
                'badge' => '💻 En ligne',
            ];
        }

        $wa = app('identite')['whatsapp'] ?? '242068287915';
        foreach ($filieres as $gi => $g) {
            foreach ($g['formations'] as $f) {
                if (in_array(Str::lower(Str::ascii($f['titre'])), $titresEnBase, true)) {
                    continue;
                }
                $filieres[$gi]['items'][] = [
                    'titre' => $f['titre'],
                    'description' => $f['description'],
                    'prix' => $f['prix'],
                    'image' => 'https://images.unsplash.com/photo-' . $f['image'] . '?auto=format&fit=crop&w=640&h=360&q=70',
                    'url' => 'https://wa.me/' . $wa . '?text=' . rawurlencode("Bonjour CABINET ACLOG, je souhaite m'inscrire à la formation « {$f['titre']} ». "),
                    'bouton' => "S'inscrire",
                    'externe' => true,
                    'meta' => '⏱ 2 mois · 🎓 Avec certificat',
                    'badge' => '🎓 Certifiante',
                ];
            }
        }

        return $filieres;
    }

    public static function total(Collection $formations): int
    {
        return collect(self::filieres($formations))->sum(fn ($g) => count($g['items']));
    }
}
