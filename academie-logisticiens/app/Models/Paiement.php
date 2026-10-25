<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'formation_id', 'montant', 'montant_promo',
        'code_promo_id', 'jetons_utilises', 'capture_paiement',
        'statut', 'valide_par', 'valide_at', 'note_admin',
    ];

    protected function casts(): array
    {
        return ['valide_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function codePromo(): BelongsTo
    {
        return $this->belongsTo(CodePromo::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}
