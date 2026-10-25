<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'formation_id', 'code_verification', 'numero',
        'score_obtenu', 'fichier_pdf', 'issued_at', 'is_public',
    ];

    /** Ex: CFF-2026-ACLOG-CG-001 + code ISO du pays de l'etudiant (SN, CG...). */
    public static function formaterNumero(int $annee, int $sequence, ?string $pays): string
    {
        return sprintf('CFF-%d-ACLOG-CG-%03d%s', $annee, $sequence, \App\Support\ProfilOptions::codePays($pays));
    }

    public function getNumeroAfficheAttribute(): string
    {
        return $this->numero ?: self::formaterNumero(
            ($this->issued_at ?? now())->year,
            (int) $this->id,
            $this->user?->pays
        );
    }

    protected function casts(): array
    {
        return ['issued_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }
}
