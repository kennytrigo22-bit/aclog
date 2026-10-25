<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TentativeExamen extends Model
{
    public $timestamps = false;
    protected $table = 'tentatives_examen';
    protected $fillable = ['user_id', 'examen_id', 'score', 'reponses_donnees', 'is_passed', 'numero_tentative', 'is_active', 'created_at'];
    protected function casts(): array
    {
        return ['reponses_donnees' => 'array', 'is_passed' => 'boolean', 'is_active' => 'boolean', 'created_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function examen()
    {
        return $this->belongsTo(\App\Models\Examen::class);
    }
}
