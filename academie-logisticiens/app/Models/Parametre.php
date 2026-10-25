<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Parametre extends Model
{
    protected $fillable = ['cle', 'valeur', 'description'];

    public static function valeur(string $cle, mixed $defaut = null): mixed
    {
        $cache = Cache::get("parametre2_{$cle}");

        if ($cache === null) {
            $param = static::where('cle', $cle)->first();
            if ($param) {
                Cache::put("parametre2_{$cle}", ['v' => $param->valeur], 3600);

                return $param->valeur;
            }
            // cle absente : on ne memorise rien, pour qu'une ligne creee ailleurs (seeder, tinker) soit vue aussitot
            return $defaut;
        }

        return is_array($cache) && array_key_exists('v', $cache) ? $cache['v'] : $defaut;
    }

    public static function siteTitre(): string
    {
        return trim((string) static::valeur('site_titre', 'CABINET ACLOG')) ?: 'CABINET ACLOG';
    }

    public static function siteSlogan(): string
    {
        return trim((string) static::valeur('site_slogan', 'La compétence, rien que la compétence.'));
    }

    /** @return array<string,string> libelle => valeur (RCCM, SCIEN, SCIET, NIU) */
    public static function identifiantsLegaux(): array
    {
        return [
            'RCCM' => (string) static::valeur('legal_rccm', 'CG-BZV-01-2026-A10-01925'),
            'SCIEN' => (string) static::valeur('legal_scien', '2173996'),
            'SCIET' => (string) static::valeur('legal_sciet', '2173996017'),
            'NIU' => (string) static::valeur('legal_niu', 'P230000004669493'),
        ];
    }

    public static function identifiantsLegauxLigne(): string
    {
        return collect(static::identifiantsLegaux())->filter()->map(fn ($v, $k) => "{$k}: {$v}")->implode(' | ');
    }

    public static function definir(string $cle, string $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        Cache::forget("parametre_{$cle}");
        Cache::forget("parametre2_{$cle}");
        app()->forgetInstance('identite');
    }
}
