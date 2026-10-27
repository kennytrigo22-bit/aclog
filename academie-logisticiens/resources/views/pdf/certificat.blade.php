@php
    // Coordonnees issues du modele "CFF EN LIGNE CABINET ACLOG.pptx" (EMU -> mm)
    $mm = fn ($emu) => round($emu / 36000, 2) . 'mm';
    $box = fn ($x, $y, $w, $h) => "left:{$mm($x)};top:{$mm($y)};width:{$mm($w)};height:{$mm($h)};";
    $d = fn ($date) => $date->format('d/m/Y');

    $nomComplet = $certificat->nom_complet;
    $naissance = $certificat->date_naissance?->format('d/m/Y');
    $lieu = $certificat->lieu_naissance;
    $titre = $certificat->intitule_formation;
    $tailleTitre = mb_strlen($titre) > 70 ? '12.5pt' : (mb_strlen($titre) > 55 ? '14pt' : (mb_strlen($titre) > 42 ? '15.5pt' : '17pt'));
    $fin = $certificat->date_fin ?? $certificat->issued_at;
    $debut = $certificat->date_debut ?? ($inscription?->created_at) ?? $fin;
    $heures = $certificat->duree_formation;
    $modalite = $certificat->estPresentiel() ? 'en présentiel' : 'en ligne';
    $identifiants = \App\Models\Parametre::identifiantsLegauxLigne();
    $numero = $certificat->numero_affiche;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        html, body { margin: 0; padding: 0; width: 297mm; height: 210mm; overflow: hidden; }
        body { font-family: 'Times', 'Times New Roman', serif; color: #1C2430; }
        .abs { position: absolute; margin: 0; padding: 0; white-space: nowrap; }
        .ctr { text-align: center; }
        .navy { color: #144065; }
        .gold { color: #A88130; }
        .lbl { color: #1F2A37; }
        .val { color: #144065; font-weight: bold; }
    </style>
</head>
<body>
    <img class="abs" style="left:0;top:0;width:297mm;height:210mm;" src="{{ public_path('assets/images/certificat-cadre.png') }}" alt="">
    <img class="abs" style="{{ $box(1113472, 757161, 1024395, 1024395) }}" src="{{ $qrCode }}" alt="QR">

    <div class="abs ctr navy" style="{{ $box(1959712, 798449, 6680835, 317340) }} font-size:20pt; font-weight:bold;">CERTIFICAT DE FIN DE FORMATION</div>
    <div class="abs ctr gold" style="{{ $box(2627795, 1156666, 5344668, 144751) }} font-size:9.1pt;">ACADEMIE DES LOGISTICIENS – CABINET D’EXPERTISE EN GESTION LOGISTIQUE ET TRANSPORT</div>
    <div class="abs ctr gold" style="{{ $box(2780195, 1340965, 5344668, 144751) }} font-size:9.1pt;">ETUDE - CONSEIL - FORMATION</div>
    <div class="abs ctr navy" style="{{ $box(2182406, 1528918, 6235446, 155886) }} font-size:9.8pt;">{{ $identifiants }}</div>

    <div class="abs ctr" style="{{ $box(1959712, 2214830, 6680835, 267233) }} font-size:16.8pt;">Le Directeur Général du Cabinet-ACLOG certifie que</div>

    <div class="abs" style="{{ $box(1870634, 2874986, 1068934, 278368) }} font-size:17.5pt;">M. / Mme:</div>
    <div class="abs ctr val" style="{{ $box(2942122, 2815000, 5989320, 278368) }} font-size:18pt;">{{ $nomComplet }}</div>

    <div class="abs" style="{{ $box(1870634, 3231297, 957586, 278368) }} font-size:17.5pt;">Né(e) le:</div>
    <div class="abs ctr val" style="{{ $box(2942122, 3201000, 2331720, 278368) }} font-size:16pt;">{{ $naissance }}</div>
    <div class="abs lbl" style="{{ $box(5456722, 3213876, 411480, 292608) }} font-size:15pt;">à :</div>
    <div class="abs ctr val" style="{{ $box(5913922, 3201000, 3017520, 278368) }} font-size:16pt;">{{ $lieu }}</div>

    <div class="abs ctr" style="{{ $box(914400, 3822192, 8860536, 292608) }} font-size:16pt; color:#1F2A37;">a suivi {{ $modalite }}, avec succès le programme de formation certifiante intitulé :</div>
    <div class="abs ctr val" style="{{ $box(1828800, 4232000, 7031736, 267233) }} font-size:{{ $tailleTitre }};">{{ $titre }}</div>

    <div class="abs lbl" style="{{ $box(1783080, 4617720, 1325880, 292608) }} font-size:15pt;">d’une durée de</div>
    <div class="abs ctr val" style="{{ $box(3108960, 4600000, 685800, 292608) }} font-size:15pt;">{{ $heures }}</div>
    <div class="abs lbl" style="{{ $box(3858768, 4617720, 960120, 292608) }} font-size:15pt;">heures, du</div>
    <div class="abs ctr val" style="{{ $box(4846320, 4600000, 1600200, 292608) }} font-size:15pt;">{{ $d($debut) }}</div>
    <div class="abs lbl" style="{{ $box(6528816, 4617720, 320040, 292608) }} font-size:15pt;">au</div>
    <div class="abs ctr val" style="{{ $box(6903720, 4600000, 1600200, 292608) }} font-size:15pt;">{{ $d($fin) }}</div>

    <div class="abs" style="{{ $box(6680835, 5344490, 2895028, 200425) }} font-size:12.6pt;">Fait à Brazzaville, le <span class="val">{{ $d($certificat->issued_at ?? $fin) }}</span></div>
    <div class="abs ctr navy" style="{{ $box(7518448, 5735822, 2316023, 222695) }} font-size:14pt; font-weight:bold;">Directeur Général</div>
    <div class="abs ctr navy" style="{{ $box(7571613, 6617974, 2316023, 222695) }} font-size:14pt; font-weight:bold;">Didi-Chance AMBIA</div>
    <div class="abs ctr gold" style="{{ $box(7482535, 6901571, 2494178, 139184) }} font-size:8.8pt; font-style:italic;">Expert en Gestion Logistique et Transport</div>

    <div class="abs" style="{{ $box(1370901, 6432219, 946997, 204078) }} font-size:13.3pt;">Certifié (e)</div>
    <div class="abs" style="{{ $box(998759, 6879797, 4231196, 200425) }} font-size:12pt; font-style:italic;">N° d'enregistrement : <span class="val" style="font-style:italic;">{{ $numero }}</span></div>

    <img class="abs" style="{{ $box(4506748, 5675863, 1586927, 1498589) }}" src="{{ public_path('assets/images/certificat-cachet.png') }}" alt="">
    <img class="abs" style="{{ $box(7555230, 5663541, 2138729, 1243298) }}" src="{{ public_path('assets/images/certificat-signature.png') }}" alt="">
</body>
</html>
