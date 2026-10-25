<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodePromo extends Model
{
    use HasFactory;

    protected $table = 'codes_promo';

    protected $fillable = [
        'code', 'type', 'valeur', 'perimetre', 'formation_id', 'categorie',
        'date_debut', 'date_fin', 'nb_utilisations_max',
        'nb_utilisations_actuel', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'datetime',
            'date_fin' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function seAppliqueA(Formation $formation): bool
    {
        return match ($this->perimetre) {
            'formation' => (int) $this->formation_id === $formation->id,
            'categorie' => $this->categorie === $formation->categorie,
            default => true,
        };
    }

    public function estValide(): bool
    {
        if (!$this->is_active) return false;
        if ($this->date_debut && now()->lt($this->date_debut)) return false;
        if ($this->date_fin && now()->gt($this->date_fin)) return false;
        if ($this->nb_utilisations_max && $this->nb_utilisations_actuel >= $this->nb_utilisations_max) return false;
        return true;
    }
}
