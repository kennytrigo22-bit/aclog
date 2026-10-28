<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class Visite extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    protected $fillable = ['visiteur', 'user_id', 'chemin', 'formation_id', 'pays', 'appareil', 'referent'];

    /** Historique conserve 13 mois (comparaison d'une annee sur l'autre), purge par "php artisan model:prune". */
    public function prunable()
    {
        return static::where('created_at', '<', now()->subMonths(13));
    }
}
