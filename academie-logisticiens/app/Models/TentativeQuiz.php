<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TentativeQuiz extends Model
{
    public $timestamps = false;
    protected $table = 'tentatives_quiz';
    protected $fillable = ['user_id', 'quiz_id', 'score', 'reponses_donnees', 'is_passed', 'created_at'];
    protected function casts(): array
    {
        return ['reponses_donnees' => 'array', 'is_passed' => 'boolean', 'created_at' => 'datetime'];
    }
}
