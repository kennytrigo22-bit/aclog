<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Examen extends Model
{
    use HasFactory;

    protected $table = 'examens';

    protected $fillable = [
        'formation_id', 'type', 'titre', 'duree_minutes', 'score_minimum',
        'nb_tentatives_max', 'delai_entre_tentatives_heures', 'nb_questions',
    ];

    protected static function booted(): void
    {
        static::deleting(fn (Examen $e) => $e->questions()->get()->each->delete());
    }

    public function isTest(): bool
    {
        return $this->type === 'test';
    }

    public function isFinal(): bool
    {
        return $this->type === 'final';
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function questions(): MorphMany
    {
        return $this->morphMany(Question::class, 'quizzable')->orderBy('ordre');
    }
}
