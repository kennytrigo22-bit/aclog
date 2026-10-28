<?php

namespace App\Services;

use App\Models\Certificat;
use App\Models\Formation;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\Presence;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Visite;
use App\Support\ProfilOptions;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Chiffres du tableau de bord admin pour une periode (jour, semaine, mois, annee) et un pays optionnel.
 * Les dates sont stockees en UTC ; les periodes et regroupements suivent l'heure de Brazzaville.
 */
class StatistiquesAdmin
{
    public const PERIODES = [
        'jour' => "Aujourd'hui",
        'semaine' => '7 derniers jours',
        'mois' => '30 derniers jours',
        'annee' => '12 derniers mois',
    ];

    private const FUSEAU = 'Africa/Brazzaville';

    private const MOIS = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

    private const JOURS = ['dim.', 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.'];

    // Montant reellement encaisse : prix apres code promo, moins les jetons utilises.
    private const MONTANT_ENCAISSE = 'COALESCE(montant_promo, montant) - COALESCE(jetons_utilises, 0)';

    private string $periode;

    private ?string $pays;

    private CarbonImmutable $debut;

    private CarbonImmutable $fin;

    public function calculer(string $periode, ?string $pays = null): array
    {
        $this->periode = array_key_exists($periode, self::PERIODES) ? $periode : 'mois';
        $this->pays = $pays && preg_match('/^[A-Z]{2}$/', $pays) ? $pays : null;
        [$this->debut, $this->fin, $cles, $libelles] = $this->decoupage();
        $precedent = [$this->debut->sub($this->fin->diff($this->debut)), $this->debut];

        $visiteurs = $this->compterVisiteurs($this->debut, $this->fin);
        $ventes = $this->ventes($this->debut, $this->fin)->count();
        $ca = (int) $this->ventes($this->debut, $this->fin)->sum(DB::raw(self::MONTANT_ENCAISSE));

        return [
            'periode' => $this->periode,
            'periode_libelle' => self::PERIODES[$this->periode],
            'pays' => $this->pays,
            'mis_a_jour' => now(self::FUSEAU)->format('H:i:s'),
            'libelles' => $libelles,
            'kpis' => [
                'visiteurs' => ['valeur' => $visiteurs, 'precedent' => $this->compterVisiteurs(...$precedent)],
                'pages_vues' => ['valeur' => $this->visites($this->debut, $this->fin)->count(), 'precedent' => $this->visites(...$precedent)->count()],
                'nouveaux_comptes' => ['valeur' => $this->comptes($this->debut, $this->fin)->count(), 'precedent' => $this->comptes(...$precedent)->count()],
                'ventes' => ['valeur' => $ventes, 'precedent' => $this->ventes(...$precedent)->count()],
                'chiffre_affaires' => ['valeur' => $ca, 'precedent' => (int) $this->ventes(...$precedent)->sum(DB::raw(self::MONTANT_ENCAISSE))],
                'conversion' => ['valeur' => $visiteurs > 0 ? round($ventes / $visiteurs * 100, 1) : 0.0],
                'panier_moyen' => ['valeur' => $ventes > 0 ? (int) round($ca / $ventes) : 0],
                'certificats' => ['valeur' => Certificat::whereBetween('issued_at', [$this->debut->utc(), $this->fin->utc()])->count()],
                'comptes_total' => ['valeur' => $this->filtrerPaysUtilisateur(User::where('role', 'apprenant'))->count()],
                'inscriptions_actives' => ['valeur' => Inscription::where('statut', 'active')->count()],
                'paiements_attente' => ['valeur' => Paiement::where('statut', 'en_attente')->count()],
                'tickets_ouverts' => ['valeur' => Ticket::where('statut', 'ouvert')->count()],
            ],
            'series' => [
                'visiteurs' => $this->serie($this->visites($this->debut, $this->fin), 'created_at', 'COUNT(DISTINCT visiteur)', $cles),
                'pages_vues' => $this->serie($this->visites($this->debut, $this->fin), 'created_at', 'COUNT(*)', $cles),
                'comptes' => $this->serie($this->comptes($this->debut, $this->fin), 'created_at', 'COUNT(*)', $cles),
                'chiffre_affaires' => $this->serie($this->ventes($this->debut, $this->fin), 'valide_at', 'SUM(' . self::MONTANT_ENCAISSE . ')', $cles),
            ],
            'top' => [
                'formations_vendues' => $this->formationsVendues(),
                'formations_consultees' => $this->formationsConsultees(),
                'pays' => $this->repartition('pays', 'COUNT(DISTINCT visiteur)', fn ($c) => $this->nomPays($c)),
                'appareils' => $this->repartition('appareil', 'COUNT(DISTINCT visiteur)', fn ($c) => ucfirst((string) $c)),
                'pages' => $this->repartition('chemin', 'COUNT(*)', fn ($c) => $c, 8),
                'sources' => $this->repartition('referent', 'COUNT(DISTINCT visiteur)', fn ($c) => $c ?: 'Accès direct'),
            ],
            'pays_disponibles' => Visite::query()->whereNotNull('pays')->distinct()->orderBy('pays')->pluck('pays')
                ->map(fn ($c) => ['code' => $c, 'nom' => $this->nomPays($c)])->sortBy('nom')->values(),
            'en_ligne' => $this->enLigne(),
        ];
    }

    public function enLigne(): array
    {
        $base = fn () => Presence::query()->enLigne();

        return [
            'total' => $base()->count(),
            'connectes' => $base()->whereNotNull('user_id')->count(),
            'pages' => $base()->selectRaw('chemin, COUNT(*) as n')->groupBy('chemin')->orderByDesc('n')->limit(5)->get()
                ->map(fn ($p) => ['chemin' => $p->chemin, 'n' => (int) $p->n]),
            'heure' => now(self::FUSEAU)->format('H:i:s'),
        ];
    }

    /** @return array{0:CarbonImmutable,1:CarbonImmutable,2:array<int,string>,3:array<int,string>} debut, fin, cles des tranches, libelles */
    private function decoupage(): array
    {
        $maintenant = CarbonImmutable::now(self::FUSEAU);
        [$debut, $pas, $nb] = match ($this->periode) {
            'jour' => [$maintenant->startOfDay(), 'hour', 24],
            'semaine' => [$maintenant->startOfDay()->subDays(6), 'day', 7],
            'annee' => [$maintenant->startOfMonth()->subMonths(11), 'month', 12],
            default => [$maintenant->startOfDay()->subDays(29), 'day', 30],
        };

        $cles = $libelles = [];
        for ($i = 0; $i < $nb; $i++) {
            $t = $debut->add($i, $pas);
            $cles[] = $t->format($this->formatCle());
            $libelles[] = match ($this->periode) {
                'jour' => $t->format('H') . 'h',
                'semaine' => self::JOURS[$t->dayOfWeek] . ' ' . $t->format('j'),
                'annee' => self::MOIS[$t->month - 1] . ' ' . $t->format('y'),
                default => $t->format('d/m'),
            };
        }

        $fin = $this->periode === 'jour' ? $maintenant->endOfDay() : $maintenant;

        return [$debut, $fin, $cles, $libelles];
    }

    private function formatCle(): string
    {
        return ['jour' => 'Y-m-d H', 'annee' => 'Y-m'][$this->periode] ?? 'Y-m-d';
    }

    /** Valeurs par tranche de temps (heure, jour ou mois a l'heure de Brazzaville), 0 pour les tranches vides. */
    private function serie(Builder $requete, string $colonne, string $agregat, array $cles): array
    {
        $format = ['jour' => '%Y-%m-%d %H', 'annee' => '%Y-%m'][$this->periode] ?? '%Y-%m-%d';
        $decalage = (int) now(self::FUSEAU)->getOffset();
        $table = $requete->getModel()->getTable();

        $valeurs = $requete->toBase()->reorder()
            ->selectRaw("DATE_FORMAT(DATE_ADD({$table}.{$colonne}, INTERVAL {$decalage} SECOND), '{$format}') as tranche, {$agregat} as total")
            ->groupBy('tranche')->pluck('total', 'tranche');

        return array_map(fn ($cle) => (float) ($valeurs[$cle] ?? 0), $cles);
    }

    private function visites(CarbonImmutable $debut, CarbonImmutable $fin): Builder
    {
        return Visite::query()->whereBetween('created_at', [$debut->utc(), $fin->utc()])
            ->when($this->pays, fn ($q) => $q->where('pays', $this->pays));
    }

    private function compterVisiteurs(CarbonImmutable $debut, CarbonImmutable $fin): int
    {
        return (int) $this->visites($debut, $fin)->distinct()->count('visiteur');
    }

    private function comptes(CarbonImmutable $debut, CarbonImmutable $fin): Builder
    {
        return $this->filtrerPaysUtilisateur(User::query()->where('role', 'apprenant')->whereBetween('created_at', [$debut->utc(), $fin->utc()]));
    }

    private function ventes(CarbonImmutable $debut, CarbonImmutable $fin): Builder
    {
        return Paiement::query()->where('statut', 'valide')->whereBetween('valide_at', [$debut->utc(), $fin->utc()])
            ->when($this->pays, fn ($q) => $q->whereHas('user', fn ($u) => $this->filtrerPaysUtilisateur($u)));
    }

    /** Le profil stocke le nom du pays ("Congo") ; le filtre utilise le code ISO ("CG"). */
    private function filtrerPaysUtilisateur(Builder $requete): Builder
    {
        if (!$this->pays) {
            return $requete;
        }

        return $requete->whereIn('pays', array_keys(array_filter(ProfilOptions::PAYS, fn ($code) => $code === $this->pays)) ?: ['-']);
    }

    private function formationsVendues(): array
    {
        $lignes = $this->ventes($this->debut, $this->fin)->toBase()
            ->selectRaw('formation_id, COUNT(*) as ventes, SUM(' . self::MONTANT_ENCAISSE . ') as ca')
            ->groupBy('formation_id')->orderByDesc('ventes')->orderByDesc('ca')->limit(8)->get();
        $titres = Formation::whereIn('id', $lignes->pluck('formation_id'))->pluck('titre', 'id');

        return $lignes->map(fn ($l) => ['nom' => $titres[$l->formation_id] ?? 'Formation supprimée', 'valeur' => (int) $l->ventes, 'ca' => (int) $l->ca])->all();
    }

    private function formationsConsultees(): array
    {
        $lignes = $this->visites($this->debut, $this->fin)->toBase()->whereNotNull('formation_id')
            ->selectRaw('formation_id, COUNT(*) as vues, COUNT(DISTINCT visiteur) as visiteurs')
            ->groupBy('formation_id')->orderByDesc('vues')->limit(8)->get();
        $titres = Formation::whereIn('id', $lignes->pluck('formation_id'))->pluck('titre', 'id');

        return $lignes->map(fn ($l) => ['nom' => $titres[$l->formation_id] ?? 'Formation supprimée', 'valeur' => (int) $l->vues, 'visiteurs' => (int) $l->visiteurs])->all();
    }

    /** Repartition des visites de la periode sur une colonne ; au-dela de $max, le reste est regroupe dans "Autres". */
    private function repartition(string $colonne, string $agregat, callable $libelle, int $max = 7): array
    {
        $lignes = $this->visites($this->debut, $this->fin)->toBase()
            ->selectRaw("{$colonne} as cle, {$agregat} as total")->groupBy($colonne)->orderByDesc('total')->get();

        $tete = $lignes->take($max)->map(fn ($l) => ['nom' => $libelle($l->cle), 'valeur' => (int) $l->total])->all();
        $reste = (int) $lignes->slice($max)->sum('total');
        if ($reste > 0) {
            $tete[] = ['nom' => 'Autres', 'valeur' => $reste];
        }

        return $tete;
    }

    private function nomPays(?string $code): string
    {
        if (!$code) {
            return 'Inconnu';
        }
        $nom = array_search($code, ProfilOptions::PAYS, true);

        return $nom !== false ? $nom : $code;
    }
}
