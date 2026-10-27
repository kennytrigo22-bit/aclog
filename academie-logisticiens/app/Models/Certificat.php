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
        'mode', 'titulaire_nom', 'titulaire_prenom', 'titulaire_pays', 'titulaire_date_naissance',
        'titulaire_lieu_naissance', 'titulaire_photo', 'formation_titre', 'duree_heures', 'date_debut', 'date_fin',
    ];

    public const MODE_EN_LIGNE = 'en_ligne';
    public const MODE_PRESENTIEL = 'presentiel';

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
            $this->pays
        );
    }

    public function estPresentiel(): bool
    {
        return $this->mode === self::MODE_PRESENTIEL;
    }

    // Identite du titulaire : saisie sur le certificat (presentiel) sinon reprise du compte apprenant.
    public function getNomAttribute(): string
    {
        return (string) ($this->titulaire_nom ?: $this->user?->name);
    }

    public function getPrenomAttribute(): string
    {
        return (string) ($this->titulaire_prenom ?: $this->user?->prenom);
    }

    public function getPaysAttribute(): ?string
    {
        return $this->titulaire_pays ?: $this->user?->pays;
    }

    public function getDateNaissanceAttribute(): ?\Carbon\CarbonInterface
    {
        return $this->titulaire_date_naissance ?? $this->user?->date_naissance;
    }

    public function getLieuNaissanceAttribute(): ?string
    {
        return $this->titulaire_lieu_naissance ?: $this->user?->lieu_naissance;
    }

    public function getNomCompletAttribute(): string
    {
        return trim(($this->prenom ? $this->prenom . ' ' : '') . mb_strtoupper($this->nom));
    }

    public function getInitialesAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->nom, 0, 1) . mb_substr($this->prenom ?: mb_substr($this->nom, 1), 0, 1));
    }

    public function getPhotoUrlAttribute(): ?string
    {
        $chemin = $this->titulaire_photo ?: $this->user?->avatar;

        return $chemin ? asset('storage/' . $chemin) : null;
    }

    public function getIntituleFormationAttribute(): string
    {
        return (string) ($this->formation_titre ?: $this->formation?->titre);
    }

    public function getDureeFormationAttribute(): ?int
    {
        return $this->duree_heures ?: $this->formation?->duree_heures;
    }

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'is_public' => 'boolean',
            'titulaire_date_naissance' => 'date',
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
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
