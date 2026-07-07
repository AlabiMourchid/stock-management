@extends('layouts.app')

@section('title', 'Vue d\'ensemble')
@section('page-title', 'Vue d\'ensemble')

@section('content')

    {{-- ===== KPI Cards ===== --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-currency-exchange"></i></div>
                <div class="stat-info">
                    <div class="stat-label">CA Aujourd'hui</div>
                    <div class="stat-value">{{ number_format($kpis['ca_jour'], 0, ',', ' ') }}&nbsp;<small class="fsz-14 fw-600 c-muted">FCFA</small></div>
                    @if($kpis['tendance_ca'] != 0)
                        <div class="stat-trend {{ $kpis['tendance_ca'] >= 0 ? 'up' : 'down' }}">
                            <i class="bi bi-arrow-{{ $kpis['tendance_ca'] >= 0 ? 'up' : 'down' }}-short"></i>
                            {{ abs($kpis['tendance_ca']) }}% vs hier
                        </div>
                    @else
                        <div class="stat-sub">—</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-receipt-cutoff"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Commandes du jour</div>
                    <div class="stat-value">{{ $kpis['nb_commandes_jour'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon {{ $kpis['alertes_stock'] > 0 ? 'red' : 'green' }}">
                    <i class="bi bi-{{ $kpis['alertes_stock'] > 0 ? 'exclamation-triangle' : 'check-circle' }}"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Alertes stock</div>
                    <div class="stat-value">{{ $kpis['alertes_stock'] }}</div>
                    <div class="stat-sub">produit{{ $kpis['alertes_stock'] > 1 ? 's' : '' }} en stock critique</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-trash3"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Pertes</div>
                    <div class="stat-value">{{ number_format($kpis['cout_pertes_semaine'], 0, ',', ' ') }}&nbsp;<small class="fsz-14 fw-600 c-muted">FCFA</small></div>
                    <div class="stat-sub">Coût matière perdue</div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-3 mb-4">
        {{-- ===== Graphique ventes 7j ===== --}}
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <span class="card-title">Évolution des ventes — 7 derniers jours</span>
                    <a href="{{ route('rapports.index') }}" class="btn btn-sm btn-light">Voir rapports <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <div class="chart-container chart-h-240">
                        <canvas id="chartVentes"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Alertes stock ===== --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <span class="card-title"><i class="bi bi-exclamation-triangle text-danger me-1"></i> Stock critique</span>
                    <a href="{{ route('stock.index') }}?filtre=critique" class="btn btn-sm btn-light">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    @forelse($alertes as $p)
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom b-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <div>
                                    <div class="fsz-13 fw-600">{{ $p->nom }}</div>
                                </div>
                            </div>
                            <span class="stock-badge stock-critique">{{ $p->stock_actuel }} {{ $p->unite }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted fsz-13">
                            <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                            Tous les stocks sont OK
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- ===== Dernières commandes ===== --}}
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Dernières commandes</span>
                    <a href="{{ route('ventes.index') }}" class="btn btn-sm btn-light">Historique</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-amira mb-0">
                            <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Heure</th>
                                <th>Montant</th>
                                <th>Paiement</th>
                                <th>Statut</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($dernieres as $cmd)
                                <tr>
                                    <td><a href="{{ route('ventes.recu', $cmd) }}" class="link-order-number">{{ $cmd->numero }}</a></td>
                                    <td class="c-muted">{{ $cmd->created_at->format('H:i') }}</td>
                                    <td class="fw-600">{{ number_format($cmd->total_ttc, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        @php $icons = ['especes' => 'cash', 'mobile_money' => 'phone']; @endphp
                                        <i class="bi bi-{{ $icons[$cmd->mode_paiement] ?? 'cash' }}"></i>
                                        {{ ucfirst(str_replace('_', ' ', $cmd->mode_paiement)) }}
                                    </td>
                                    <td><span class="badge-status badge-{{ str_replace('_', '-', $cmd->statut) }}">{{ $cmd->statut_libelle }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Aucune commande aujourd'hui</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Accès rapides ===== --}}
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header"><span class="card-title">Accès rapide</span></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('access-pos')
                            <a href="{{ route('ventes.pos') }}" class="btn btn-amira btn-lg d-flex align-items-center gap-2 justify-content-center">
                                <i class="bi bi-cart3 fs-5"></i> Prise de commande
                            </a>
                        @endcan
                            {{-- <a href="{{ route('cuisine.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2 justify-content-center rounded-md"
                            <i class="bi bi-fire fs-5"></i> Écran cuisine
                        </a>--}}
                        @can('manage-stock')
                            <a href="{{ route('stock.fin-service') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2 justify-content-center rounded-md"
                                <i class="bi bi-arrow-left-right fs-5"></i> Saisie fin de service
                            </a>
                        @endcan
                        @can('view-reports')
                            <a href="{{ route('rapports.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2 justify-content-center rounded-md"
                                <i class="bi bi-bar-chart-line fs-5"></i> Statistiques & rapports
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const evo = @json($evolution);
        const ctx = document.getElementById('chartVentes').getContext('2d');

        Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: evo.map(e => e.date),
                datasets: [
                    {
                        label: 'CA (FCFA)',
                        data: evo.map(e => e.total),
                        backgroundColor: 'rgba(244,98,31,0.15)',
                        borderColor: '#F4621F',
                        borderWidth: 2,
                        borderRadius: 6,
                        type: 'bar',
                        yAxisID: 'yCA',
                        order: 2,
                    },
                    {
                        label: 'Commandes',
                        data: evo.map(e => e.nb),
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37,99,235,0)',
                        borderWidth: 2,
                        pointBackgroundColor: '#2563EB',
                        pointRadius: 4,
                        tension: 0.4,
                        type: 'line',
                        yAxisID: 'yNb',
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 12 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label === 'CA (FCFA)'
                                ? ` ${ctx.raw.toLocaleString('fr')} FCFA`
                                : ` ${ctx.raw} commandes`
                        }
                    }
                },
                scales: {
                    yCA: {
                        type: 'linear',
                        position: 'left',
                        grid: { color: 'rgba(0,0,0,.05)' },
                        ticks: { font: { size: 11 }, callback: v => v.toLocaleString('fr') + ' F' }
                    },
                    yNb: {
                        type: 'linear',
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { font: { size: 11 }, stepSize: 1 }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 12 } } }
                }
            }
        });
    </script>
@endpush
