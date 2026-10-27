<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Temoignage extends Model
{
    protected $fillable = ['citation', 'nom', 'fonction', 'photo', 'ordre', 'actif'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function scopeActifs($query)
    {
        return $query->where('actif', true)->orderBy('ordre')->orderBy('id');
    }

    /** "Clarisse Moukala" -> "CM" (pastille affichee quand il n'y a pas de photo). */
    public function getInitialesAttribute(): string
    {
        $mots = preg_split('/[\s\-]+/u', trim($this->nom), -1, PREG_SPLIT_NO_EMPTY);
        $premier = $mots[0] ?? '';
        $dernier = count($mots) > 1 ? end($mots) : mb_substr($premier, 1);

        return mb_strtoupper(mb_substr($premier, 0, 1) . mb_substr($dernier, 0, 1));
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}
