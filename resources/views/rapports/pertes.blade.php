@extends('layouts.app')
@section('title','Rapport des pertes')
@section('page-title','Rapport des pertes & déchets')

@section('content')
    <div class="card mb-4"><div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2"><label class="form-label mb-1">Du</label><input type="date" name="debut" class="form-control form-control-sm" value="{{ $debut }}"></div>
                <div class="col-md-2"><label class="form-label mb-1">Au</label><input type="date" name="fin" class="form-control form-control-sm" value="{{ $fin }}"></div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Motif</label>
                    <select name="motif" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="perime"           {{ request('motif')==='perime'?'selected':'' }}>Périmé</option>
                        <option value="brulee"           {{ request('motif')==='brulee'?'selected':'' }}>Brûlé</option>
                        <option value="tombe"            {{ request('motif')==='tombe'?'selected':'' }}>Tombé / abîmé</option>
                        <option value="mauvaise_cuisson" {{ request('motif')==='mauvaise_cuisson'?'selected':'' }}>Mauvaise cuisson</option>
                        <option value="autre"            {{ request('motif')==='autre'?'selected':'' }}>Autre</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-amira btn-sm flex-grow-1"><i class="bi bi-search me-1"></i>Filtrer</button>
                    <a href="{{ route('rapports.pertes') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Imprimer</button>
                </div>
            </form>
        </div></div>

    @php
        $pertes     = $rapport['pertes'];
        $coutTotal  = $rapport['cout_total'];
        $parMotif   = $rapport['par_motif'];
        $parProduit = $rapport['par_produit'];
        $caDebut    = \App\Models\Commande::whereBetween('created_at',[$debut.' 00:00:00',$fin.' 23:59:59'])->whereNotIn('statut',['annule'])->sum('total_ttc');
        $tauxPerte  = $caDebut>0?round(($coutTotal/$caDebut)*100,1):0;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon red"><i class="bi bi-currency-exchange"></i></div><div class="stat-info"><div class="stat-label">Coût total pertes</div><div class="stat-value fsz-19">{{ number_format($coutTotal,0,',',' ') }}<small class="fsz-11 c-muted"> F</small></div></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon amber"><i class="bi bi-exclamation-triangle"></i></div><div class="stat-info"><div class="stat-label">Signalements</div><div class="stat-value fsz-19">{{ $pertes->count() }}</div><div class="stat-sub">{{ number_format($pertes->sum('quantite'),2,',','') }} unité(s)</div></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon {{ $tauxPerte>5?'red':($tauxPerte>2?'amber':'green') }}"><i class="bi bi-percent"></i></div><div class="stat-info"><div class="stat-label">Taux de perte</div><div class="stat-value fsz-19">{{ $tauxPerte }}%</div><div class="stat-sub">{{ $tauxPerte>5?'⚠ Élevé':($tauxPerte>2?'Modéré':'Acceptable') }}</div></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon blue"><i class="bi bi-box-seam"></i></div><div class="stat-info"><div class="stat-label">Produits impactés</div><div class="stat-value fsz-19">{{ $parProduit->count() }}</div></div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header"><span class="card-title">Répartition par motif</span></div>
                <div class="card-body">
                    @if($parMotif->isEmpty())
                        <div class="text-center py-4 text-muted"><i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>Aucune perte</div>
                    @else
                        @php $motifLabels=['perime'=>'Périmé','brulee'=>'Brûlé','tombe'=>'Tombé','mauvaise_cuisson'=>'Mauvaise cuisson','autre'=>'Autre']; @endphp
                        @foreach($parMotif as $motif => $cout)
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="motif-dot motif-{{ $motif }}"></span>
                                    <span class="fsz-13">{{ $motifLabels[$motif] ?? $motif }}</span>
                                </div>
                                <span class="fsz-13 fw-bold">{{ number_format($cout,0,',',' ') }} FCFA</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header"><span class="card-title">Produits les plus touchés</span></div>
                <div class="card-body p-0">
                    @forelse($parProduit as $item)
                        @php $maxC=$parProduit->first()['cout_total']??1; $pct=$maxC>0?round(($item['cout_total']/$maxC)*100):0; @endphp
                        <div class="px-4 py-3 {{ !$loop->last?'border-bottom':'' }} b-subtle">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fsz-20">{{ $item['produit']->emoji }}</span>
                                    <div><div class="fw-600 fsz-13">{{ $item['produit']->nom }}</div><div class="text-meta">{{ number_format($item['quantite'],2,',','') }} {{ $item['produit']->unite }} perdu(e)(s)</div></div>
                                </div>
                                <div class="text-end"><div class="fw-bold c-danger">{{ number_format($item['cout_total'],0,',',' ') }} FCFA</div><div class="text-meta">{{ $coutTotal>0?round(($item['cout_total']/$coutTotal)*100):0 }}% des pertes</div></div>
                            </div>
                            <div class="progress progress-xxs"><div class="progress-bar bg-var-danger" style="width:{{ $pct }}%"></div></div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted"><i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>Aucune perte</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Détail des signalements</span><span class="fsz-12 c-muted">{{ $pertes->count() }} signalement(s)</span></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-amira mb-0">
                    <thead><tr><th>Date</th><th>Produit</th><th class="text-center">Qté</th><th class="text-center">Coût unit.</th><th class="text-center">Coût total</th><th class="text-center">Motif</th><th>Description</th><th>Signalé par</th></tr></thead>
                    <tbody>
                    @forelse($pertes as $p)
                        @php $motifInfo=['perime'=>['Périmé','stock-critique'],'brulee'=>['Brûlé','stock-critique'],'tombe'=>['Tombé','stock-moyen'],'mauvaise_cuisson'=>['Mauvaise cuisson','stock-moyen'],'autre'=>['Autre','stock-ok']][$p->motif]??[$p->motif,'stock-ok']; @endphp
                        <tr>
                            <td class="text-nowrap"><div class="fw-600 fsz-13">{{ $p->date_perte->locale('fr')->isoFormat('D MMM') }}</div><div class="text-meta">{{ $p->created_at->format('H:i') }}</div></td>
                            <td><div class="d-flex align-items-center gap-2"><span class="fsz-18">{{ $p->produitStock->emoji }}</span><div><div class="fw-600 fsz-13">{{ $p->produitStock->nom }}</div></div></div></td>
                            <td class="text-center fw-bold">{{ number_format($p->quantite,2,',','') }} <small class="c-muted">{{ $p->produitStock->unite }}</small></td>
                            <td class="text-center c-muted fsz-13">{{ number_format($p->cout_unitaire,0,',',' ') }} F</td>
                            <td class="text-center"><span class="fw-bold c-danger">{{ number_format($p->cout_total,0,',',' ') }} F</span></td>
                            <td class="text-center"><span class="stock-badge {{ $motifInfo[1] }}">{{ $motifInfo[0] }}</span></td>
                            <td class="fsz-12 c-muted">{{ $p->description?:'—' }}</td>
                            <td class="fsz-12"><div class="fw-600">{{ $p->user->name }}</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>Aucune perte</td></tr>
                    @endforelse
                    </tbody>
                    @if($pertes->isNotEmpty())
                        <tfoot><tr class="table-total-row"><td colspan="4" class="fw-bold fsz-13 py-2 px-3">TOTAL</td><td class="text-center fw-bold c-danger fsz-14 py-2 px-3">{{ number_format($coutTotal,0,',',' ') }} FCFA</td><td colspan="3"></td></tr></tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
