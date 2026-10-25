<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lecon extends Model
{
    use HasFactory;

    protected $table = 'lecons';

    protected $fillable = [
        'module_id', 'titre', 'description', 'video_url',
        'duree_minutes', 'contenu_texte', 'fichier_pdf', 'ordre', 'is_preview',
    ];

    protected function casts(): array
    {
        return ['is_preview' => 'boolean'];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function progressions(): HasMany
    {
        return $this->hasMany(Progression::class);
    }
}
