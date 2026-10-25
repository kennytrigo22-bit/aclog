<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TestNiveau extends Model
{
    protected $table = 'tests_niveau';

    protected $fillable = ['domaine', 'niveau', 'titre', 'nb_questions', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::deleting(fn (TestNiveau $t) => $t->questions()->get()->each->delete());
    }

    public function questions(): MorphMany
    {
        return $this->morphMany(Question::class, 'quizzable')->orderBy('ordre');
    }
}
