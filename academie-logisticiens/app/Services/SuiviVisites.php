<?php

namespace App\Services;

use App\Models\Presence;
use App\Models\Visite;
use App\Support\ProfilOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * Mesure d'audience interne (sans service externe ni adresse IP stockee) :
 * un cookie aleatoire identifie le navigateur, le pays vient de l'en-tete Cloudflare ou du profil.
 */
class SuiviVisites
{
    public const COOKIE = 'aclog_v';

    private const ROBOTS = '/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|preview|curl|wget|python|httpclient|headless|lighthouse|monitor/i';

    public function doitSuivre(Request $request): bool
    {
        if (!$request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return false;
        }
        if ($request->is('admin', 'admin/*', 'api/*', 'up', 'build/*', 'storage/*')) {
            return false;
        }
        // l'equipe (admin, gestionnaire) ne fausse pas les chiffres
        if ($request->user() && $request->user()->role !== 'apprenant') {
            return false;
        }

        return !preg_match(self::ROBOTS, (string) $request->userAgent());
    }

    public function enregistrerVisite(Request $request): void
    {
        $visiteur = $this->visiteur($request);
        $pays = $this->pays($request);
        $formation = $request->route('formation');

        Visite::create([
            'visiteur' => $visiteur,
            'user_id' => $request->user()?->id,
            'chemin' => Str::limit('/' . ltrim($request->path(), '/'), 250, ''),
            'formation_id' => $request->routeIs('formations.show') && is_object($formation) ? $formation->id : null,
            'pays' => $pays,
            'appareil' => $this->appareil((string) $request->userAgent()),
            'referent' => $this->referent($request),
        ]);

        $this->marquerPresence($request, $visiteur, $pays);
    }

    /** Signal de vie envoye par les pages ouvertes (onglet visible) pour le compteur "en ligne". */
    public function marquerPresence(Request $request, ?string $visiteur = null, ?string $pays = null, ?string $chemin = null): void
    {
        $visiteur ??= $this->visiteur($request);

        Presence::updateOrCreate(['visiteur' => $visiteur], [
            'user_id' => $request->user()?->id,
            'chemin' => Str::limit($chemin ?? '/' . ltrim($request->path(), '/'), 250, ''),
            'pays' => $pays ?? $this->pays($request),
            'derniere_activite' => now(),
        ]);
    }

    public function visiteur(Request $request): string
    {
        $id = (string) $request->cookie(self::COOKIE);
        if (!preg_match('/^[A-Za-z0-9]{40}$/', $id)) {
            $id = $request->attributes->get(self::COOKIE) ?: Str::random(40);
            $request->attributes->set(self::COOKIE, $id);
            Cookie::queue(self::COOKIE, $id, 60 * 24 * 395, null, null, null, true, false, 'Lax');
        }

        return $id;
    }

    /** Code ISO du pays : en-tete Cloudflare (site derriere Cloudflare), sinon pays du profil de l'apprenant connecte. */
    public function pays(Request $request): ?string
    {
        $cf = strtoupper((string) $request->header('CF-IPCountry'));
        if (preg_match('/^[A-Z]{2}$/', $cf) && !in_array($cf, ['XX', 'T1'], true)) {
            return $cf;
        }
        $profil = $request->user()?->pays;
        $code = $profil ? (ProfilOptions::PAYS[$profil] ?? null) : null;

        return $code && $code !== 'XX' ? $code : null;
    }

    public function appareil(string $ua): string
    {
        if (preg_match('/ipad|tablet|kindle|silk|playbook/i', $ua) || (stripos($ua, 'android') !== false && stripos($ua, 'mobile') === false)) {
            return 'tablette';
        }

        return preg_match('/mobi|iphone|ipod|android|opera mini|blackberry|windows phone/i', $ua) ? 'mobile' : 'ordinateur';
    }

    private function referent(Request $request): ?string
    {
        $hote = strtolower((string) parse_url((string) $request->headers->get('referer'), PHP_URL_HOST));
        if ($hote === '' || $hote === strtolower($request->getHost())) {
            return null;
        }

        return Str::limit(preg_replace('/^(www|m|l|lm)\./', '', $hote), 120, '');
    }
}
