<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificat;
use App\Models\Formation;
use App\Services\CertificatService;
use App\Support\ProfilOptions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Certificats : liste de tous les certificats delivres et generation pour les apprenants formes en presentiel.
 */
class CertificatAdminController extends Controller
{
    public function __construct(private CertificatService $service)
    {
    }

    public function index(Request $request)
    {
        $query = Certificat::with(['user', 'formation'])->latest('issued_at')->latest('id');

        if (in_array($request->mode, [Certificat::MODE_EN_LIGNE, Certificat::MODE_PRESENTIEL], true)) {
            $query->where('mode', $request->mode);
        }
        if ($q = trim((string) $request->q)) {
            $query->where(function ($w) use ($q) {
                $w->where('titulaire_nom', 'like', "%{$q}%")
                    ->orWhere('titulaire_prenom', 'like', "%{$q}%")
                    ->orWhere('numero', 'like', "%{$q}%")
                    ->orWhere('code_verification', 'like', "%{$q}%")
                    ->orWhere('formation_titre', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('prenom', 'like', "%{$q}%"))
                    ->orWhereHas('formation', fn ($f) => $f->where('titre', 'like', "%{$q}%"));
            });
        }

        $certificats = $query->paginate(25)->withQueryString();
        $formations = Formation::orderBy('titre')->get(['id', 'titre', 'duree_heures']);
        $titresCatalogue = collect(config('catalogue'))->flatMap(fn ($g) => array_column($g['formations'], 'titre'))->sort()->values();
        $pays = array_keys(ProfilOptions::PAYS);
        $stats = [
            'total' => Certificat::count(),
            'presentiel' => Certificat::where('mode', Certificat::MODE_PRESENTIEL)->count(),
            'en_ligne' => Certificat::where('mode', Certificat::MODE_EN_LIGNE)->count(),
        ];

        return view('admin.certificats.index', compact('certificats', 'formations', 'titresCatalogue', 'pays', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->valider($request, true);
        $data['titulaire_photo'] = $request->file('photo')?->store('certificats', 'public');
        unset($data['photo']);

        $certificat = $this->service->genererPresentiel($this->formationEtDates($data));

        return redirect()->route('admin.certificats.index')
            ->with('success', "Certificat {$certificat->numero} généré pour {$certificat->nom_complet}. Il apparaît dans la liste des certifiés et se vérifie par QR Code.");
    }

    /** Generation en lot : une ligne par apprenant, "NOM ; Prénoms ; Pays ; date de naissance ; lieu de naissance". */
    public function storeLot(Request $request)
    {
        $commun = $request->validate($this->reglesFormation() + [
            'liste' => ['required', 'string', 'max:20000'],
        ], ['liste.required' => 'Collez au moins une ligne (NOM ; Prénoms ; Pays).']);

        $lignes = collect(preg_split('/\r\n|\r|\n/', $commun['liste']))->map(fn ($l) => trim($l))->filter();
        $erreurs = [];
        $apprenants = [];
        foreach ($lignes as $i => $ligne) {
            $c = array_map('trim', preg_split('/[;\t]/', $ligne));
            $nom = $c[0] ?? '';
            if ($nom === '' || mb_strlen($nom) > 255) {
                $erreurs[] = 'Ligne ' . ($i + 1) . ' : nom manquant.';
                continue;
            }
            $naissance = null;
            if (!empty($c[3])) {
                try {
                    $naissance = Carbon::createFromFormat(str_contains($c[3], '/') ? 'd/m/Y' : 'Y-m-d', $c[3])->startOfDay();
                } catch (\Throwable) {
                    $erreurs[] = 'Ligne ' . ($i + 1) . " : date de naissance « {$c[3]} » invalide (format JJ/MM/AAAA).";
                    continue;
                }
            }
            $apprenants[] = [
                'titulaire_nom' => mb_strtoupper($nom),
                'titulaire_prenom' => $c[1] ?? null ?: null,
                'titulaire_pays' => $this->paysConnu($c[2] ?? null),
                'titulaire_date_naissance' => $naissance,
                'titulaire_lieu_naissance' => $c[4] ?? null ?: null,
            ];
        }

        if ($erreurs) {
            return back()->withInput()->withErrors(['liste' => $erreurs]);
        }

        $base = $this->formationEtDates($commun);
        unset($base['liste']);
        foreach ($apprenants as $a) {
            $this->service->genererPresentiel($base + $a);
        }

        return redirect()->route('admin.certificats.index', ['mode' => Certificat::MODE_PRESENTIEL])
            ->with('success', count($apprenants) . ' certificat(s) généré(s). Ajoutez les photos depuis la liste (bouton Modifier).');
    }

    public function edit(Certificat $certificat)
    {
        abort_unless($certificat->estPresentiel(), 404);

        return view('admin.certificats.edit', [
            'certificat' => $certificat,
            'formations' => Formation::orderBy('titre')->get(['id', 'titre', 'duree_heures']),
            'titresCatalogue' => collect(config('catalogue'))->flatMap(fn ($g) => array_column($g['formations'], 'titre'))->sort()->values(),
            'pays' => array_keys(ProfilOptions::PAYS),
        ]);
    }

    public function update(Request $request, Certificat $certificat)
    {
        abort_unless($certificat->estPresentiel(), 404);

        $data = $this->valider($request, false);
        if ($request->boolean('retirer_photo') && $certificat->titulaire_photo) {
            Storage::disk('public')->delete($certificat->titulaire_photo);
            $data['titulaire_photo'] = null;
        }
        if ($request->hasFile('photo')) {
            if ($certificat->titulaire_photo) {
                Storage::disk('public')->delete($certificat->titulaire_photo);
            }
            $data['titulaire_photo'] = $request->file('photo')->store('certificats', 'public');
        }
        unset($data['photo']);

        $certificat->update($this->formationEtDates($data));

        return redirect()->route('admin.certificats.index')->with('success', "Certificat {$certificat->numero} mis à jour.");
    }

    public function pdf(Certificat $certificat)
    {
        return $this->service->pdf($certificat)->download($this->service->nomFichier($certificat));
    }

    public function visibilite(Certificat $certificat)
    {
        $certificat->update(['is_public' => !$certificat->is_public]);

        return back()->with('success', $certificat->is_public ? 'Le certifié apparaît dans la liste publique.' : 'Le certifié est retiré de la liste publique (son certificat reste vérifiable).');
    }

    public function destroy(Certificat $certificat)
    {
        abort_unless($certificat->estPresentiel(), 403, 'Seuls les certificats présentiel peuvent être supprimés ici.');

        if ($certificat->titulaire_photo) {
            Storage::disk('public')->delete($certificat->titulaire_photo);
        }
        $certificat->delete();

        return back()->with('success', 'Certificat supprimé : il n\'est plus vérifiable.');
    }

    private function reglesFormation(): array
    {
        return [
            'formation_id' => ['nullable', 'required_without:formation_titre', 'integer', 'exists:formations,id'],
            'formation_titre' => ['nullable', 'required_without:formation_id', 'string', 'max:255'],
            'duree_heures' => ['nullable', 'integer', 'min:1', 'max:2000'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ];
    }

    private function valider(Request $request, bool $creation): array
    {
        return $request->validate($this->reglesFormation() + [
            'titulaire_nom' => ['required', 'string', 'max:255'],
            'titulaire_prenom' => ['nullable', 'string', 'max:255'],
            'titulaire_pays' => ['nullable', 'string', Rule::in(array_keys(ProfilOptions::PAYS))],
            'titulaire_date_naissance' => ['nullable', 'date', 'before:today'],
            'titulaire_lieu_naissance' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'formation_id.required_without' => 'Choisissez une formation de la plateforme ou saisissez son intitulé.',
            'formation_titre.required_without' => 'Choisissez une formation de la plateforme ou saisissez son intitulé.',
        ]);
    }

    /** Une formation de la plateforme prime sur l'intitule libre ; la duree par defaut vient de la formation. */
    private function formationEtDates(array $data): array
    {
        if (!empty($data['formation_id'])) {
            $data['formation_titre'] = null;
            $data['duree_heures'] = $data['duree_heures'] ?? Formation::find($data['formation_id'])?->duree_heures;
        } else {
            $data['formation_id'] = null;
        }
        if (isset($data['titulaire_nom'])) {
            $data['titulaire_nom'] = mb_strtoupper(trim($data['titulaire_nom']));
        }

        return $data;
    }

    private function paysConnu(?string $pays): ?string
    {
        if (!$pays) {
            return null;
        }
        $normalise = fn ($p) => mb_strtolower(\Illuminate\Support\Str::ascii($p));
        foreach (array_keys(ProfilOptions::PAYS) as $connu) {
            if ($normalise($connu) === $normalise($pays)) {
                return $connu;
            }
        }

        return $pays;
    }
}
