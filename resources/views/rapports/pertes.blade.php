@extends('layouts.app')

@section('title', 'Rapport des pertes')
@section('page-title', 'Rapport des pertes & déchets')

@section('content')

    {{-- ===== Filtres période ===== --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2 col-sm-6">
                    <label class="form-label mb-1">Du</label>
                    <input type="date" name="debut" class="form-control form-control-sm"
                           value="{{ $debut }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label mb-1">Au</label>
                    <input type="date" name="fin" class="form-control form-control-sm"
                           value="{{ $fin }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label mb-1">Produit</label>
                    <select name="produit_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach(\App\Models\Produit::actif()->orderBy('nom')->get() as $p)
                            <option value="{{ $p->id }}" {{ request('produit_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label mb-1">Motif</label>
                    <select name="motif" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="perime" {{ request('motif') === 'perime'           ? 'selected' : '' }}>Périmé
                        </option>
                        <option value="brulee" {{ request('motif') === 'brulee'           ? 'selected' : '' }}>Brûlé
                        </option>
                        <option value="tombe" {{ request('motif') === 'tombe'            ? 'selected' : '' }}>Tombé /
                            abîmé
                        </option>
                        <option
                            value="mauvaise_cuisson" {{ request('motif') === 'mauvaise_cuisson' ? 'selected' : '' }}>
                            Mauvaise cuisson
                        </option>
                        <option value="autre" {{ request('motif') === 'autre'            ? 'selected' : '' }}>Autre
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-amira btn-sm flex-grow-1">
                            <i class="bi bi-search me-1"></i>Filtrer
                        </button>
                        <a href="{{ route('rapports.pertes') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-lg"></i>
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i>Imprimer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== KPIs ===== --}}
    @php
        $pertes      = $rapport['pertes'];
        $coutTotal   = $rapport['cout_total'];
        $parMotif    = $rapport['par_motif'];
        $parProduit  = $rapport['par_produit'];
        $nbSignalements = $pertes->count();
        $qteTotal    = $pertes->sum('quantite');

        // CA du jour pour comparer le taux de perte
        $caDebut = \App\Models\Commande::whereBetween('created_at', [$debut.' 00:00:00', $fin.' 23:59:59'])
        ->whereNotIn('statut', ['annule'])->sum('total_ttc');
        $tauxPerte = $caDebut > 0 ? round(($coutTotal / $caDebut) * 100, 1) : 0;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-currency-exchange"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Coût total</div>
                    <div class="stat-value" style="font-size:19px">
                        {{ number_format($coutTotal, 0, ',', ' ') }}
                        <small style="font-size:12px;color:var(--text-muted)">FCFA</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Signalements</div>
                    <div class="stat-value" style="font-size:19px">{{ $nbSignalements }} - {{ number_format($qteTotal, 2, ',', '') }} <span class="stat-sub">unité</span></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon {{ $tauxPerte > 5 ? 'red' : ($tauxPerte > 2 ? 'amber' : 'green') }}">
                    <i class="bi bi-percent"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Taux de perte</div>
                    <div class="stat-value" style="font-size:19px">{{ $tauxPerte }}%</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-box-seam"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Produits impactés</div>
                    <div class="stat-value" style="font-size:19px">{{ $parProduit->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">

        {{-- ===== Graphique par motif ===== --}}
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <span class="card-title">Répartition par motif</span>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    @if($parMotif->isEmpty())
                        <div class="text-center py-4 text-muted" style="font-size:13px">
                            <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
                            Aucune perte sur cette période
                        </div>
                    @else
                        <div class="chart-container mb-3" style="height:180px">
                            <canvas id="chartMotifs"></canvas>
                        </div>
                        @php
                            $motifLabels = [
                            'perime'           => ['Périmé',           '#DC2626'],
                            'brulee'           => ['Brûlé',            '#EA580C'],
                            'tombe'            => ['Tombé / abîmé',    '#D97706'],
                            'mauvaise_cuisson' => ['Mauvaise cuisson', '#7C3AED'],
                            'autre'            => ['Autre',            '#6B7280'],
                            ];
                        @endphp
                        <div class="d-flex flex-column gap-2">
                            @foreach($parMotif as $motif => $cout)
                                @php [$label, $color] = $motifLabels[$motif] ?? [$motif, '#6B7280']; @endphp
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span
                                            style="width:10px;height:10px;border-radius:50%;background:{{ $color }};flex-shrink:0;display:inline-block"></span>
                                        <span style="font-size:13px">{{ $label }}</span>
                                    </div>
                                    <span style="font-size:13px;font-weight:700">
                            {{ number_format($cout, 0, ',', ' ') }} FCFA
                        </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== Top produits perdus ===== --}}
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header">
                    <span class="card-title">Produits les plus touchés</span>
                </div>
                <div class="card-body p-0">
                    @forelse($parProduit as $item)
                        @php
                            $maxCout = $parProduit->first()['cout_total'] ?? 1;
                            $pct     = $maxCout > 0 ? round(($item['cout_total'] / $maxCout) * 100) : 0;
                        @endphp
                        <div class="px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}"
                             style="border-color:var(--border-color)!important">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <div style="font-weight:600;font-size:13.5px">{{ $item['produit']->nom }}</div>
                                        <div style="font-size:11px;color:var(--text-muted)">
                                            {{ number_format($item['quantite'], 2, ',', '') }}
                                            {{ $item['produit']->unite }} perdu(e)(s)
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div style="font-weight:700;font-size:14px;color:var(--danger)">
                                        {{ number_format($item['cout_total'], 0, ',', ' ') }} FCFA
                                    </div>
                                    <div style="font-size:11px;color:var(--text-muted)">
                                        {{ $coutTotal > 0 ? round(($item['cout_total'] / $coutTotal) * 100) : 0 }}% des
                                        pertes
                                    </div>
                                </div>
                            </div>
                            <div class="progress" style="height:5px;border-radius:4px;background:var(--border-color)">
                                <div class="progress-bar"
                                     style="width:{{ $pct }}%;border-radius:4px;background:var(--danger)">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
                            Aucune perte enregistrée sur cette période
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    {{-- ===== Tableau détaillé des signalements ===== --}}
    <div class="card no-print-hide">
        <div class="card-header">
            <span class="card-title">Détail des signalements</span>
            <span style="font-size:12px;color:var(--text-muted)">{{ $nbSignalements }} signalement(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-amira mb-0">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Produit</th>
                        <th class="text-center">Quantité</th>
                        <th class="text-center">Coût unit.</th>
                        <th class="text-center">Coût total</th>
                        <th class="text-center">Motif</th>
                        <th>Description</th>
                        <th>Signalé par</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($pertes as $p)
                        @php
                            $motifInfo = [
                            'perime'           => ['Périmé',           'stock-critique'],
                            'brulee'           => ['Brûlé',            'stock-critique'],
                            'tombe'            => ['Tombé / abîmé',    'stock-moyen'],
                            'mauvaise_cuisson' => ['Mauvaise cuisson', 'stock-moyen'],
                            'autre'            => ['Autre',            'stock-ok'],
                            ][$p->motif] ?? [$p->motif, 'stock-ok'];
                        @endphp
                        <tr>
                            <td style="white-space:nowrap">
                                <div style="font-weight:600;font-size:13px">
                                    {{ $p->date_perte->locale('fr')->isoFormat('D MMM') }}
                                </div>
                                <div style="font-size:11px;color:var(--text-muted)">
                                    {{ $p->created_at->format('H:i') }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <div style="font-weight:600;font-size:13px">{{ $p->produit->nom }}</div>
                                        <div style="font-size:11px;color:var(--text-muted)">
                                            {{ $p->produit->categorie->nom }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center" style="font-weight:700">
                                {{ number_format($p->quantite, 2, ',', '') }}
                                <small style="color:var(--text-muted);font-weight:400">{{ $p->produit->unite }}</small>
                            </td>
                            <td class="text-center" style="color:var(--text-muted);font-size:13px">
                                {{ number_format($p->cout_unitaire, 0, ',', ' ') }} F
                            </td>
                            <td class="text-center">
                            <span style="font-weight:700;color:var(--danger)">
                                {{ number_format($p->cout_total, 0, ',', ' ') }} FCFA
                            </span>
                            </td>
                            <td class="text-center">
                            <span class="stock-badge {{ $motifInfo[1] }}">
                                {{ $motifInfo[0] }}
                            </span>
                            </td>
                            <td style="font-size:12px;color:var(--text-muted);max-width:180px">
                            <span class="text-truncate d-inline-block" style="max-width:170px"
                                  title="{{ $p->description }}">
                                {{ $p->description ?: '—' }}
                            </span>
                            </td>
                            <td style="font-size:12px">
                                <div style="font-weight:600">{{ $p->user->name }}</div>
                                <div style="font-size:10px;color:var(--text-muted)">{{ ucfirst($p->user->role) }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
                                Aucune perte enregistrée sur cette période
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                    @if($pertes->isNotEmpty())
                        <tfoot>
                        <tr style="background:var(--body-bg)">
                            <td colspan="4" style="font-weight:700;padding:10px 14px;font-size:13px">
                                TOTAL
                            </td>
                            <td class="text-center"
                                style="font-weight:700;color:var(--danger);font-size:14px;padding:10px 14px">
                                {{ number_format($coutTotal, 0, ',', ' ') }} FCFA
                            </td>
                            <td colspan="3"></td>
                        </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";

        @if($parMotif->isNotEmpty())
        const parMotif = @json($parMotif);
        const motifMeta = {
            perime: {label: 'Périmé', color: '#DC2626'},
            brulee: {label: 'Brûlé', color: '#EA580C'},
            tombe: {label: 'Tombé / abîmé', color: '#D97706'},
            mauvaise_cuisson: {label: 'Mauvaise cuisson', color: '#7C3AED'},
            autre: {label: 'Autre', color: '#6B7280'},
        };

        const labels = Object.keys(parMotif).map(k => motifMeta[k]?.label ?? k);
        const values = Object.values(parMotif);
        const colors = Object.keys(parMotif).map(k => motifMeta[k]?.color ?? '#6B7280');

        new Chart(document.getElementById('chartMotifs'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {display: false},
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.raw.toLocaleString('fr')} FCFA`
                        }
                    }
                }
            }
        });
        @endif
    </script>
@endpush
