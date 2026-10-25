<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Module extends Model
{
    use HasFactory;

    protected $fillable = ['formation_id', 'titre', 'description', 'ordre'];

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function lecons(): HasMany
    {
        return $this->hasMany(Lecon::class)->orderBy('ordre');
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }
}
