<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FormationRessource extends Model
{
    protected $table = 'formation_ressources';

    protected $fillable = ['formation_id', 'titre', 'type', 'contenu_html', 'fichier', 'nom_fichier', 'mime', 'ordre'];

    protected static function booted(): void
    {
        static::deleting(function (FormationRessource $r) {
            if ($r->fichier) {
                Storage::disk('local')->delete($r->fichier);
            }
        });
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function estPdf(): bool
    {
        return $this->mime === 'application/pdf';
    }
}
