@extends('layouts.app')
@section('title', 'Statistiques & Rapports')
@section('page-title', 'Statistiques & Rapports')

@section('content')

    {{-- Sélecteur période --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1 fsz-12">Du</label>
                    <input type="date" name="debut" class="form-control form-control-sm" value="{{ $debut }}" max="{{ today()->toDateString() }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1 fsz-12">Au</label>
                    <input type="date" name="fin" class="form-control form-control-sm" value="{{ $fin }}" max="{{ today()->toDateString() }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-amira btn-sm"><i class="bi bi-search me-1"></i>Appliquer</button>
                    <a href="{{ route('rapports.index') }}" class="btn btn-outline-secondary btn-sm ms-1"><i class="bi bi-x-lg me-1"></i> Annuler</a>
                </div>
            </form>
        </div>
    </div>

    @php
        $nbCommandes = \App\Models\Commande::whereBetween('created_at',[$debut.' 00:00:00',$fin.' 23:59:59'])->whereNotIn('statut',['annule'])->count();
        $nbAnnulees  = \App\Models\Commande::whereBetween('created_at',[$debut.' 00:00:00',$fin.' 23:59:59'])->where('statut','annule')->count();
        $tauxDepenses  = $caPeriode > 0 ? round(($totalDepenses/$caPeriode)*100,1) : 0;
        $depensesParCategorie = \App\Models\Depense::whereBetween('date_depense',[$debut,$fin])
            ->selectRaw('categorie, SUM(montant) as total, COUNT(*) as nb')->groupBy('categorie')->orderByDesc('total')->get();
    @endphp

    {{-- KPIs financiers --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-currency-exchange"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Chiffre d'affaires</div>
                    <div class="stat-value fsz-19">{{ number_format($caPeriode,0,',',' ') }}<small class="fsz-11 c-muted"> FCFA</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-wallet2"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total dépenses</div>
                    <div class="stat-value fsz-19">{{ number_format($totalDepenses,0,',',' ') }}<small class="fsz-11 c-muted"> FCFA</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon {{ $margeNette>=0?'green':'red' }}"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Marge nette</div>
                    <div class="stat-value fsz-19 {{ $margeNette>=0?'c-success':'c-danger' }}">
                        {{ $margeNette>=0?'+':'' }}{{ number_format($margeNette,0,',',' ') }}<small class="fsz-11"> FCFA</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-receipt-cutoff"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Commandes</div>
                    <div class="stat-value fsz-19">{{ $nbCommandes }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Résumé financier --}}
    <div class="card mb-4">
        <div class="card-header">
            <span class="card-title">Résumé financier</span>
            <span class="fsz-12 c-muted">
            {{ \Carbon\Carbon::parse($debut)->locale('fr')->isoFormat('D MMM') }} → {{ \Carbon\Carbon::parse($fin)->locale('fr')->isoFormat('D MMM YYYY') }}
        </span>
        </div>
        <div class="card-body">
            <div class="row g-4 align-items-center">
                <div class="col-md-8">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="summary-row-label">Chiffre d'affaires</span>
                            <span class="summary-row-value c-brand">{{ number_format($caPeriode,0,',',' ') }} FCFA</span>
                        </div>
                        <div class="progress progress-md">
                            <div class="progress-bar bg-var-brand" style="width:100%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="summary-row-label">Charges fixes</span>
                            <span class="summary-row-value c-info">− {{ number_format($totalFixe,0,',',' ') }} FCFA</span>
                        </div>
                        <div class="progress progress-md">
                            <div class="progress-bar bg-var-info" style="width:{{ $caPeriode>0 ? min(100,round($totalFixe/$caPeriode*100)) : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="summary-row-label">Charges variables</span>
                            <span class="summary-row-value c-warning">− {{ number_format($totalVariable,0,',',' ') }} FCFA</span>
                        </div>
                        <div class="progress progress-md">
                            <div class="progress-bar bg-var-warning" style="width:{{ $caPeriode>0 ? min(100,round($totalVariable/$caPeriode*100)) : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fsz-13 fw-bold">Marge nette</span>
                            <span class="summary-row-value-lg {{ $margeNette>=0?'c-success':'c-danger' }}">
                            {{ $margeNette>=0?'+':'' }}{{ number_format($margeNette,0,',',' ') }} FCFA
                        </span>
                        </div>
                        @php $pctMarge = $caPeriode>0 ? min(100,max(0,round(($margeNette/$caPeriode)*100))) : 0; @endphp
                        <div class="progress progress-md">
                            <div class="progress-bar {{ $margeNette>=0?'bg-var-success':'bg-var-danger' }}" style="width:{{ $pctMarge }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="fsz-11 c-muted text-uppercase ls-06 mb-2">Marge nette</div>
                    <div class="finance-margin-big {{ $margeNette>=0?'c-success':'c-danger' }}">
                        {{ $tauxMargeNette }}%
                    </div>
                    <div class="fsz-12 c-muted">du chiffre d'affaires</div>
                </div>
            </div>

            {{-- Seuil de rentabilité --}}
            <hr class="hr-plain">
            <div class="p-3 rounded threshold-box {{ $seuilAtteint?'is-positive':'is-negative' }}">
                <div class="d-flex align-items-start gap-3">
                    <div class="fsz-26">{{ $seuilAtteint?'✅':'⚠️' }}</div>
                    <div class="flex-grow-1">
                        <div class="fw-bold fsz-14 {{ $seuilAtteint?'c-success':'c-danger' }}">
                            Seuil de rentabilité : {{ number_format($seuilRentabilite,0,',',' ') }} FCFA
                        </div>
                        @if($seuilRentabilite > 0)
                            <div class="fsz-13 mt-1 c-secondary">
                                @if($seuilAtteint)
                                    ✓ Seuil dépassé de <strong class="c-success">{{ number_format($caPeriode-$seuilRentabilite,0,',',' ') }} FCFA</strong>
                                @else
                                    Il manque <strong class="c-danger">{{ number_format($seuilRentabilite-$caPeriode,0,',',' ') }} FCFA</strong> de CA
                                @endif
                            </div>
                        @else
                            <div class="fsz-12 c-muted mt-1">Saisissez des charges fixes pour calculer le seuil</div>
                        @endif
                    </div>
                </div>
            </div>

            @if($depensesParCategorie->isNotEmpty())
                <hr class="hr-plain">
                <div class="fsz-12 fw-bold text-uppercase ls-06 c-muted mb-3">Dépenses par catégorie</div>
                <div class="row g-2">
                    @foreach($depensesParCategorie as $cat)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between p-2 rounded card-header-subtle">
                                <div>
                                    <div class="fsz-13 fw-600">{{ $cat->categorie ?: 'Sans catégorie' }}</div>
                                    <div class="text-meta">{{ $cat->nb }} dépense(s)</div>
                                </div>
                                <span class="fw-bold c-danger fsz-13">{{ number_format($cat->total,0,',',' ') }} FCFA</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Graphique évolution + Top produits --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header"><span class="card-title">Évolution du CA — 7 derniers jours</span></div>
                <div class="card-body"><div class="chart-container chart-h-240"><canvas id="chartEvolution"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header"><span class="card-title">Top produits</span></div>
                <div class="card-body p-0">
                    @forelse($topProduits->take(6) as $i => $item)
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 {{ !$loop->last?'border-bottom':'' }} b-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fsz-12-5 fw-600">{{ $item->menu->nom ?? '—' }}</span>
                            </div>
                            <span class="fsz-12 fw-bold c-brand">{{ number_format($item->ca,0,',',' ') }} F</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted fsz-13">Aucune vente</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";
        const evo = @json($evolution);
        new Chart(document.getElementById('chartEvolution'), {
            type: 'line',
            data: {
                labels: evo.map(e => e.date),
                datasets: [{
                    label: 'CA (FCFA)', data: evo.map(e => e.total),
                    borderColor: '#F4621F', backgroundColor: 'rgba(244,98,31,0.08)',
                    borderWidth: 2.5, pointBackgroundColor: '#F4621F', pointRadius: 4, tension: 0.4, fill: true,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: 'rgba(0,0,0,.05)' }, ticks: { callback: v => v.toLocaleString('fr') + ' F' } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
@endpush
