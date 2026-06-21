@extends('layouts.app')

@section('title', 'Statistiques & Rapports')
@section('page-title', 'Statistiques & Rapports')

@section('content')

    {{-- ===== Sélecteur de période ===== --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1">Période</label>
                    <div class="btn-group paiement-toggle" role="group">
                        <input type="radio" class="btn-check" name="periode" id="p-jour"
                               value="jour" {{ $periode === 'jour' ? 'checked' : '' }}>
                        <label class="btn btn-outline-secondary mx-2" for="p-jour">Aujourd'hui</label>

                        <input type="radio" class="btn-check" name="periode" id="p-semaine"
                               value="semaine" {{ $periode === 'semaine' ? 'checked' : '' }}>
                        <label class="btn btn-outline-secondary mx-2" for="p-semaine">Cette semaine</label>

                        <input type="radio" class="btn-check" name="periode" id="p-mois"
                               value="mois" {{ $periode === 'mois' ? 'checked' : '' }}>
                        <label class="btn btn-outline-secondary mx-2" for="p-mois">Ce mois</label>

                        <input type="radio" class="btn-check" name="periode" id="p-annee"
                               value="annee" {{ $periode === 'annee' ? 'checked' : '' }}>
                        <label class="btn btn-outline-secondary mx-2" for="p-annee">Cette année</label>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-amira btn-sm">
                        <i class="bi bi-search me-1"></i>Appliquer
                    </button>
                </div>
                <div class="col-auto ms-auto">
                <span style="font-size:12px;color:var(--text-muted)">
                    <i class="bi bi-calendar-range me-1"></i>
                    Du {{ \Carbon\Carbon::parse($debut)->locale('fr')->isoFormat('D MMM YYYY') }}
                    au {{ \Carbon\Carbon::parse($fin)->locale('fr')->isoFormat('D MMM YYYY') }}
                </span>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== KPIs principaux ===== --}}
    @php
        $nbCommandes   = \App\Models\Commande::whereBetween('created_at', [$debut.' 00:00:00', $fin.' 23:59:59'])
                            ->whereNotIn('statut', ['annule'])->count();
        $nbAnnulees    = \App\Models\Commande::whereBetween('created_at', [$debut.' 00:00:00', $fin.' 23:59:59'])
                            ->where('statut', 'annule')->count();
        $panierMoyen   = $nbCommandes > 0 ? $caPeriode / $nbCommandes : 0;

        $caEspeces     = \App\Models\Commande::whereBetween('created_at', [$debut.' 00:00:00', $fin.' 23:59:59'])
                            ->whereNotIn('statut', ['annule'])->where('mode_paiement', 'especes')->sum('total_ttc');
        $caMobile      = \App\Models\Commande::whereBetween('created_at', [$debut.' 00:00:00', $fin.' 23:59:59'])
                            ->whereNotIn('statut', ['annule'])->where('mode_paiement', 'mobile_money')->sum('total_ttc');
        $caCarte       = \App\Models\Commande::whereBetween('created_at', [$debut.' 00:00:00', $fin.' 23:59:59'])
                            ->whereNotIn('statut', ['annule'])->where('mode_paiement', 'carte')->sum('total_ttc');
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-currency-exchange"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Chiffre d'affaires</div>
                    <div class="stat-value" style="font-size:20px">
                        {{ number_format($caPeriode, 0, ',', ' ') }}
                        <small style="font-size:12px;color:var(--text-muted)">FCFA</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-receipt-cutoff"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Commandes</div>
                    <div class="stat-value" style="font-size:20px">{{ $nbCommandes }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Panier moyen</div>
                    <div class="stat-value" style="font-size:20px">
                        {{ number_format($panierMoyen, 0, ',', ' ') }}
                        <small style="font-size:12px;color:var(--text-muted)">FCFA</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-phone"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Mobile Money</div>
                    <div class="stat-value" style="font-size:20px">
                        {{ number_format($caMobile, 0, ',', ' ') }}
                        <small style="font-size:12px;color:var(--text-muted)">FCFA</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">

        {{-- ===== Graphique évolution 7 jours ===== --}}
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <span class="card-title">Évolution du CA — 7 derniers jours</span>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height:260px">
                        <canvas id="chartEvolution"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Répartition paiements ===== --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <span class="card-title">Répartition paiements</span>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="chart-container mb-3" style="height:160px">
                        <canvas id="chartPaiements"></canvas>
                    </div>
                    <div class="d-flex flex-column gap-2 mt-2">
                        @foreach([
                            ['Espèces',      $caEspeces, '#16A34A'],
                            ['Mobile Money', $caMobile,  '#2563EB'],
                            ['Carte',        $caCarte,   '#7C3AED'],
                        ] as [$label, $val, $color])
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $color }};display:inline-block;flex-shrink:0"></span>
                                    <span style="font-size:13px">{{ $label }}</span>
                                </div>
                                <span style="font-size:13px;font-weight:700">
                            {{ number_format($val, 0, ',', ' ') }} FCFA
                        </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Top produits ===== --}}
    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Top produits vendus</span>
                    <span style="font-size:12px;color:var(--text-muted)">
                    {{ \Carbon\Carbon::parse($debut)->locale('fr')->isoFormat('D MMM') }}
                    → {{ \Carbon\Carbon::parse($fin)->locale('fr')->isoFormat('D MMM YYYY') }}
                </span>
                </div>
                <div class="card-body p-0">
                    @forelse($topProduits as $index => $item)
                        @php
                            $maxCa  = $topProduits->first()?->ca ?? 1;
                            $pct    = $maxCa > 0 ? round(($item->ca / $maxCa) * 100) : 0;
                            $medal  = match($index) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => '' };
                        @endphp
                        <div class="px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}"
                             style="border-color:var(--border-color)!important">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                            <span style="font-size:18px;width:24px;text-align:center">
                                {{ $medal ?: ($index + 1) }}
                            </span>
                                    <div>
                                        <div style="font-weight:600;font-size:13.5px">{{ $item->menu->nom }}</div>
                                        <div style="font-size:11px;color:var(--text-muted)">
                                            {{ $item->qte_vendue }} unité(s) vendue(s)
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div style="font-weight:700;font-size:14px">
                                        {{ number_format($item->ca, 0, ',', ' ') }} FCFA
                                    </div>
                                    <div style="font-size:11px;color:var(--text-muted)">
                                        {{ $caPeriode > 0 ? round(($item->ca / $caPeriode) * 100) : 0 }}% du CA
                                    </div>
                                </div>
                            </div>
                            <div class="progress" style="height:5px;border-radius:4px;background:var(--border-color)">
                                <div class="progress-bar"
                                     style="width:{{ $pct }}%;border-radius:4px;
                                    background:{{ match(true) { $index === 0 => '#F59E0B', $index === 1 => '#9CA3AF', $index === 2 => '#CD7C2F', default => 'var(--amira-orange)' } }}">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart fs-2 d-block mb-2 opacity-50"></i>
                            Aucune vente sur cette période
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ===== Graphique top produits (barres horizontales) ===== --}}
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <span class="card-title">CA par produit</span>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height:300px">
                        <canvas id="chartTopProduits"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";

        // ---- Graphique évolution 7 jours ----
        const evo = @json($evolution);
        new Chart(document.getElementById('chartEvolution'), {
            type: 'line',
            data: {
                labels: evo.map(e => e.date),
                datasets: [{
                    label: 'CA (FCFA)',
                    data: evo.map(e => e.total),
                    borderColor: '#F4621F',
                    backgroundColor: 'rgba(244,98,31,0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#F4621F',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4,
                    fill: true,
                }, {
                    label: 'Commandes',
                    data: evo.map(e => e.nb),
                    borderColor: '#2563EB',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointBackgroundColor: '#2563EB',
                    pointRadius: 3,
                    tension: 0.4,
                    yAxisID: 'yNb',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 12 }, boxWidth: 12 } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label === 'CA (FCFA)'
                                ? ` ${ctx.raw.toLocaleString('fr')} FCFA`
                                : ` ${ctx.raw} commandes`
                        }
                    }
                },
                scales: {
                    y:   { grid: { color: 'rgba(0,0,0,.05)' }, ticks: { font: { size: 11 }, callback: v => v.toLocaleString('fr') + ' F' } },
                    yNb: { position: 'right', grid: { drawOnChartArea: false }, ticks: { font: { size: 11 }, stepSize: 1 } },
                    x:   { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });

        // ---- Donut répartition paiements ----
        const caEspeces = {{ $caEspeces }};
        const caMobile  = {{ $caMobile }};
        const caCarte   = {{ $caCarte }};

        new Chart(document.getElementById('chartPaiements'), {
            type: 'doughnut',
            data: {
                labels: ['Espèces', 'Mobile Money', 'Carte'],
                datasets: [{
                    data: [caEspeces, caMobile, caCarte],
                    backgroundColor: ['#16A34A', '#2563EB', '#7C3AED'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.raw.toLocaleString('fr')} FCFA`
                        }
                    }
                }
            }
        });

        // ---- Barres horizontales top produits ----
        const topProduits = @json($topProduits);

        new Chart(document.getElementById('chartTopProduits'), {
            type: 'bar',
            data: {
                labels: topProduits.map(t => (t.produit?.emoji ?? '') + ' ' + (t.produit?.nom ?? '')),
                datasets: [{
                    label: 'CA (FCFA)',
                    data: topProduits.map(t => t.ca),
                    backgroundColor: topProduits.map((_, i) =>
                        i === 0 ? '#F4621F' : 'rgba(244,98,31,0.25)'
                    ),
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            }
        });
    </script>
@endpush
