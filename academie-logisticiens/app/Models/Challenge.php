<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Challenge extends Model
{
    use HasFactory;

    public const MIN_QUESTIONS = 10;

    protected $fillable = [
        'parcours_id', 'titre', 'theme', 'description', 'flag_attendu',
        'indice', 'points', 'nb_questions', 'ordre', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::deleting(fn (Challenge $c) => $c->questions()->get()->each->delete());
    }

    public function parcours(): BelongsTo
    {
        return $this->belongsTo(Parcours::class);
    }

    public function questions(): MorphMany
    {
        return $this->morphMany(Question::class, 'quizzable')->orderBy('ordre');
    }

    public function isJouable(): bool
    {
        return $this->questions()->count() >= self::MIN_QUESTIONS;
    }

    public static function genererFlag(string $titre): string
    {
        return 'ACLOG{' . strtoupper(\Illuminate\Support\Str::slug($titre, '_')) . '_' . strtoupper(\Illuminate\Support\Str::random(5)) . '}';
    }
}
