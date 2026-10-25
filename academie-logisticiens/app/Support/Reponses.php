<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class Reponses
{
    public static function exigerBonneReponse(array $reponses): void
    {
        if (!collect($reponses)->contains(fn ($r) => !empty($r['is_correct']))) {
            throw ValidationException::withMessages(['reponses' => 'Cochez au moins une bonne reponse.']);
        }
    }
}
