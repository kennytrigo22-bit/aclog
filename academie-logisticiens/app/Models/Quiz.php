<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Quiz extends Model
{
    use HasFactory;

    protected $table = 'quizzes';

    protected $fillable = ['module_id', 'titre'];

    protected static function booted(): void
    {
        static::deleting(fn (Quiz $q) => $q->questions()->get()->each->delete());
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function questions(): MorphMany
    {
        return $this->morphMany(Question::class, 'quizzable')->orderBy('ordre');
    }
}
