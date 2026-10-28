// Tableau de bord admin : graphiques (Chart.js), filtres periode / pays et actualisation en direct.
import {
    Chart, LineController, BarController, LineElement, BarElement, PointElement,
    CategoryScale, LinearScale, Tooltip, Legend, Filler,
} from 'chart.js';

Chart.register(LineController, BarController, LineElement, BarElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler);

const racine = document.getElementById('tableau-stats');

if (racine) {
    // Palette de reference validee (series 1 et 2), une teinte par mode clair / sombre.
    const PALETTES = {
        clair: { serie1: '#2a78d6', serie2: '#eb6834', texte: '#52514e', grille: '#e7e6e2', surface: '#ffffff', bon: '#0a8a0a', mauvais: '#d03b3b' },
        sombre: { serie1: '#3987e5', serie2: '#d95926', texte: '#c3c2b7', grille: '#33332f', surface: '#1e293b', bon: '#0ca30c', mauvais: '#e66767' },
    };
    const palette = () => (document.documentElement.classList.contains('dark') ? PALETTES.sombre : PALETTES.clair);

    const nombre = new Intl.NumberFormat('fr-FR');
    const formater = (v, format) => {
        if (format === 'fcfa') return `${nombre.format(Math.round(v))} FCFA`;
        if (format === 'pourcent') return `${nombre.format(v)} %`;
        return nombre.format(v);
    };
    const echapper = (t) => String(t ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    const couper = (t, n = 34) => (String(t).length > n ? `${String(t).slice(0, n - 1)}…` : String(t));

    let stats = JSON.parse(document.getElementById('stats-initiales').textContent);
    const filtres = { periode: stats.periode, pays: stats.pays || '' };
    const graphiques = {};

    function optionsCommunes(p, { horizontal = false, legende = false, format = 'nombre' } = {}) {
        const axeValeur = {
            beginAtZero: true,
            grid: { color: p.grille, drawTicks: false },
            border: { display: false },
            ticks: { color: p.texte, precision: 0, padding: 6, callback: (v) => (format === 'fcfa' ? nombre.format(v) : nombre.format(v)) },
        };
        const axeCategorie = {
            grid: { display: false },
            border: { color: p.grille },
            ticks: { color: p.texte, autoSkip: true, autoSkipPadding: 12, maxTicksLimit: horizontal ? undefined : 10, maxRotation: 0, callback(v) { return couper(this.getLabelForValue(v), horizontal ? 28 : 12); } },
        };

        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 300 },
            indexAxis: horizontal ? 'y' : 'x',
            interaction: horizontal ? { mode: 'nearest', intersect: true } : { mode: 'index', intersect: false },
            plugins: {
                legend: { display: legende, position: 'top', align: 'start', labels: { color: p.texte, usePointStyle: true, pointStyle: 'line', boxWidth: 24 } },
                tooltip: {
                    backgroundColor: p.surface, titleColor: p.texte, bodyColor: p.texte, borderColor: p.grille, borderWidth: 1, padding: 10,
                    callbacks: { label: (ctx) => ` ${ctx.dataset.label} : ${formater(horizontal ? ctx.parsed.x : ctx.parsed.y, format)}` },
                },
            },
            scales: horizontal ? { x: axeValeur, y: axeCategorie } : { x: axeCategorie, y: axeValeur },
        };
    }

    // Une definition par graphique : type, series et format, a partir des donnees JSON.
    const DEFINITIONS = {
        'g-audience': (s, p) => ({
            type: 'line',
            libelles: s.libelles,
            series: [
                { label: 'Visiteurs uniques', data: s.series.visiteurs, borderColor: p.serie1, backgroundColor: p.serie1 },
                { label: 'Pages vues', data: s.series.pages_vues, borderColor: p.serie2, backgroundColor: p.serie2 },
            ],
            legende: true,
        }),
        'g-ca': (s, p) => ({ type: 'bar', libelles: s.libelles, series: [{ label: "Chiffre d'affaires", data: s.series.chiffre_affaires, backgroundColor: p.serie1 }], format: 'fcfa' }),
        'g-comptes': (s, p) => ({ type: 'bar', libelles: s.libelles, series: [{ label: 'Nouveaux comptes', data: s.series.comptes, backgroundColor: p.serie1 }] }),
        'g-vendues': (s, p) => barresTop(s.top.formations_vendues, 'Ventes', p),
        'g-consultees': (s, p) => barresTop(s.top.formations_consultees, 'Pages vues', p),
        'g-pays': (s, p) => barresTop(s.top.pays, 'Visiteurs', p),
        'g-appareils': (s, p) => barresTop(s.top.appareils, 'Visiteurs', p),
        'g-sources': (s, p) => barresTop(s.top.sources, 'Visiteurs', p),
        'g-pages': (s, p) => barresTop(s.top.pages, 'Pages vues', p),
    };

    function barresTop(lignes, libelle, p) {
        return {
            type: 'bar',
            horizontal: true,
            libelles: lignes.map((l) => l.nom),
            series: [{ label: libelle, data: lignes.map((l) => l.valeur), backgroundColor: p.serie1 }],
        };
    }

    function dessiner(id) {
        const p = palette();
        const def = DEFINITIONS[id](stats, p);
        const canvas = document.getElementById(id);
        const vide = def.series.every((serie) => serie.data.every((v) => !v));
        canvas.parentElement.querySelector('.vide').classList.toggle('hidden', !vide);
        canvas.classList.toggle('invisible', vide);

        const datasets = def.series.map((serie) => (def.type === 'line'
            ? { ...serie, borderWidth: 2, pointRadius: 0, pointHoverRadius: 5, pointHoverBorderWidth: 2, pointHoverBorderColor: p.surface, tension: 0.25, fill: false }
            : { ...serie, borderRadius: 4, borderSkipped: 'start', maxBarThickness: def.horizontal ? 22 : 28, categoryPercentage: 0.8, barPercentage: 0.9 }));

        if (graphiques[id]) {
            const g = graphiques[id];
            g.data.labels = def.libelles;
            g.data.datasets = datasets;
            g.options = optionsCommunes(p, { horizontal: def.horizontal, legende: def.legende, format: def.format });
            g.update();
        } else {
            graphiques[id] = new Chart(canvas, {
                type: def.type,
                data: { labels: def.libelles, datasets },
                options: optionsCommunes(p, { horizontal: def.horizontal, legende: def.legende, format: def.format }),
            });
        }
        remplirTableau(id, def);
    }

    // Alternative accessible : les memes chiffres en tableau sous chaque graphique.
    function remplirTableau(id, def) {
        const table = racine.querySelector(`[data-table="${id}"]`);
        const entete = `<thead><tr><th class="text-left py-1 pr-3"></th>${def.series.map((s) => `<th class="text-right py-1 px-2">${echapper(s.label)}</th>`).join('')}</tr></thead>`;
        const lignes = def.libelles.map((l, i) => `<tr class="border-t border-gray-100 dark:border-gray-800"><td class="py-1 pr-3">${echapper(l)}</td>${def.series.map((s) => `<td class="text-right py-1 px-2 tabular-nums">${formater(s.data[i] || 0, def.format)}</td>`).join('')}</tr>`).join('');
        table.innerHTML = entete + `<tbody>${lignes || '<tr><td class="py-1">Aucune donnée</td></tr>'}</tbody>`;
    }

    function majIndicateurs() {
        const p = palette();
        racine.querySelectorAll('[data-kpi]').forEach((el) => {
            const k = stats.kpis[el.dataset.kpi];
            if (k) el.textContent = formater(k.valeur, el.dataset.format);
        });
        // Variation par rapport a la periode precedente : fleche + texte, jamais la couleur seule.
        racine.querySelectorAll('[data-delta]').forEach((el) => {
            const k = stats.kpis[el.dataset.delta];
            if (!k || k.precedent === undefined) return;
            if (!k.precedent) {
                el.textContent = k.valeur ? 'Nouveau sur la période' : 'Aucune activité';
                el.style.color = '';
                el.className = 'text-xs mt-1 text-gray-400';
                return;
            }
            const variation = Math.round(((k.valeur - k.precedent) / k.precedent) * 100);
            const hausse = variation >= 0;
            el.className = 'text-xs mt-1 font-semibold';
            el.style.color = variation === 0 ? '' : (hausse ? p.bon : p.mauvais);
            el.textContent = `${variation === 0 ? '=' : (hausse ? '▲' : '▼')} ${hausse && variation ? '+' : ''}${variation} % vs période précédente`;
        });
        document.getElementById('periode-libelle').textContent = stats.periode_libelle;
        document.getElementById('maj').textContent = stats.mis_a_jour;
        racine.querySelectorAll('[data-periode]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.periode === filtres.periode)));
        majPaysDisponibles();
    }

    function majPaysDisponibles() {
        const select = document.getElementById('filtre-pays');
        const connus = new Set([...select.options].map((o) => o.value));
        stats.pays_disponibles.forEach((p) => {
            if (!connus.has(p.code)) select.add(new Option(p.nom, p.code));
        });
        select.value = filtres.pays;
    }

    function majEnLigne(enLigne) {
        document.getElementById('en-ligne-total').textContent = nombre.format(enLigne.total);
        document.getElementById('en-ligne-connectes').textContent = nombre.format(enLigne.connectes);
        document.getElementById('en-ligne-pages').innerHTML = enLigne.pages
            .map((p) => `<li class="flex justify-between gap-2"><span class="truncate">${echapper(p.chemin)}</span><span class="tabular-nums">${p.n}</span></li>`).join('');
    }

    function toutDessiner() {
        Object.keys(DEFINITIONS).forEach(dessiner);
        majIndicateurs();
        majEnLigne(stats.en_ligne);
    }

    async function charger() {
        const chargement = document.getElementById('chargement');
        chargement.classList.remove('hidden');
        try {
            const url = new URL(racine.dataset.urlStats);
            url.searchParams.set('periode', filtres.periode);
            if (filtres.pays) url.searchParams.set('pays', filtres.pays);
            const reponse = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!reponse.ok) return;
            stats = await reponse.json();
            toutDessiner();
            // l'adresse de la page garde les filtres (lien partageable, rechargement)
            const page = new URL(window.location.href);
            page.searchParams.set('periode', filtres.periode);
            if (filtres.pays) page.searchParams.set('pays', filtres.pays); else page.searchParams.delete('pays');
            window.history.replaceState(null, '', page);
        } catch (e) {
            // reseau coupe : on reessaiera au prochain cycle
        } finally {
            chargement.classList.add('hidden');
        }
    }

    async function chargerEnLigne() {
        try {
            const reponse = await fetch(racine.dataset.urlEnLigne, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (reponse.ok) majEnLigne(await reponse.json());
        } catch (e) { /* prochain cycle */ }
    }

    racine.querySelectorAll('[data-periode]').forEach((b) => b.addEventListener('click', () => {
        filtres.periode = b.dataset.periode;
        charger();
    }));
    document.getElementById('filtre-pays').addEventListener('change', (e) => {
        filtres.pays = e.target.value;
        charger();
    });

    // Temps reel : "en ligne" toutes les 15 s, tous les chiffres toutes les 60 s (onglet visible seulement).
    setInterval(() => document.visibilityState === 'visible' && chargerEnLigne(), 15000);
    setInterval(() => document.visibilityState === 'visible' && charger(), 60000);

    // Bascule clair / sombre de l'admin : on redessine avec la palette du mode.
    new MutationObserver(() => Object.keys(DEFINITIONS).forEach(dessiner))
        .observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    toutDessiner();
}
