<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use MassPrunable;

    /** Un visiteur est "en ligne" s'il a ouvert une page ou envoye un signal de vie dans ce delai. */
    public const FENETRE_MINUTES = 3;

    protected $primaryKey = 'visiteur';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['visiteur', 'user_id', 'chemin', 'pays', 'derniere_activite'];

    protected function casts(): array
    {
        return ['derniere_activite' => 'datetime'];
    }

    public function scopeEnLigne($query)
    {
        return $query->where('derniere_activite', '>=', now()->subMinutes(self::FENETRE_MINUTES));
    }

    public function prunable()
    {
        return static::where('derniere_activite', '<', now()->subDay());
    }
}
