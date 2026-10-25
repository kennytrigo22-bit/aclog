<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestNiveauResultat extends Model
{
    public $timestamps = false;

    protected $table = 'test_niveaux_resultats';

    protected $fillable = ['user_id', 'domaine', 'niveau', 'score', 'reponses_json', 'passed', 'created_at'];

    protected function casts(): array
    {
        return [
            'reponses_json' => 'array',
            'passed' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
