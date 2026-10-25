<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['quizzable_type', 'quizzable_id', 'texte', 'type', 'ordre', 'points'];

    public function quizzable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reponses(): HasMany
    {
        return $this->hasMany(Reponse::class);
    }
}
