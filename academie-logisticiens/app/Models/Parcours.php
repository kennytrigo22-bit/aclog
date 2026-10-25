<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parcours extends Model
{
    use HasFactory;

    protected $table = 'parcours';

    protected $fillable = [
        'titre', 'slug', 'description', 'domaine', 'niveau',
        'image', 'is_active', 'ordre',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class)->orderBy('ordre');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
