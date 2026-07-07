@extends('layouts.app')
@section('title','Mouvements de stock')
@section('page-title','Historique des mouvements de stock')

@section('content')
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2"><label class="form-label mb-1">Du</label><input type="date" name="debut"
                                                                                      class="form-control form-control-sm"
                                                                                      value="{{ request('debut',today()->toDateString()) }}">
                </div>
                <div class="col-md-2"><label class="form-label mb-1">Au</label><input type="date" name="fin"
                                                                                      class="form-control form-control-sm"
                                                                                      value="{{ request('fin',today()->toDateString()) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="entree" {{ request('type')==='entree'?'selected':'' }}>Entrée</option>
                        <option value="sortie" {{ request('type')==='sortie'?'selected':'' }}>Sortie</option>
                        <option value="inventaire" {{ request('type')==='inventaire'?'selected':'' }}>Inventaire
                        </option>
                        <option value="perte" {{ request('type')==='perte'?'selected':'' }}>Perte</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Produit</label>
                    <select name="produit_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach(\App\Models\Produit::actif()->orderBy('nom')->get() as $p)
                            <option
                                value="{{ $p->id }}" {{ request('produit_id')==$p->id?'selected':'' }}>{{ $p->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-amira btn-sm flex-grow-1"><i class="bi bi-search me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('stock.mouvement') }}" class="btn btn-outline-secondary btn-sm"><i
                            class="bi bi-x-lg"></i></a>
                </div>
            </form>
        </div>
    </div>

    @php
        $totalEntrees  = $mouvements->getCollection()->where('type','entree')->sum('quantite');
        $totalSorties  = $mouvements->getCollection()->where('type','sortie')->sum('quantite');
        $totalPertes   = $mouvements->getCollection()->where('type','perte')->sum('quantite');
        $coutPertes    = $mouvements->getCollection()->where('type','perte')->sum('cout_total');
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-arrow-up-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Entrées</div>
                    <div class="stat-value" style="font-size:20px">{{ number_format($totalEntrees,2,',','') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-arrow-down-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Sorties</div>
                    <div class="stat-value" style="font-size:20px">{{ number_format($totalSorties,2,',','') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Pertes</div>
                    <div class="stat-value" style="font-size:20px">{{ number_format($totalPertes,2,',','') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-list-ul"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total mouvements</div>
                    <div class="stat-value" style="font-size:20px">{{ $mouvements->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Historique des mouvements</span>
            <div class="d-flex gap-2" style="font-size:11px">
                <span class="mouv-badge mouv-entree">Entrée</span>
                <span class="mouv-badge mouv-sortie">Sortie</span>
                <span class="mouv-badge mouv-inventaire">Inventaire</span>
                <span class="mouv-badge mouv-perte">Perte</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-amira mb-0">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Produit</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Quantité</th>
                        <th class="text-center">Avant</th>
                        <th class="text-center">Après</th>
                        <th>Fournisseur</th>
                        <th>Motif</th>
                        <th>Par</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($mouvements as $m)
                        <tr>
                            <td style="white-space:nowrap">
                                <div
                                    style="font-weight:600;font-size:13px">{{ $m->date_mouvement->locale('fr')->isoFormat('D MMM') }}</div>
                                <div
                                    style="font-size:11px;color:var(--text-muted)">{{ $m->created_at->format('H:i') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <div style="font-weight:600;font-size:13px">{{ $m->produit->nom }}</div>
                                        <div
                                            style="font-size:11px;color:var(--text-muted)">{{ $m->produit->unite }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center"><span
                                    class="mouv-badge mouv-{{ $m->type }}">{{ match($m->type){'entree'=>'Entrée','sortie'=>'Sortie','inventaire'=>'Inventaire','perte'=>'Perte'} }}</span>
                            </td>
                            <td class="text-center">
                            <span
                                style="font-weight:700;font-size:14px;color:{{ $m->type==='entree'?'var(--success)':($m->type==='inventaire'?'var(--info)':'var(--danger)') }}">
                                {{ $m->type==='entree'?'+':($m->type==='inventaire'?'≈':'−') }}{{ number_format($m->quantite,2,',','') }}
                            </span>
                            </td>
                            <td class="text-center"
                                style="font-size:13px;color:var(--text-muted)">{{ number_format($m->stock_avant,2,',','') }}</td>
                            <td class="text-center">
                                @php $col=$m->stock_apres<=$m->produit->seuil_critique?'var(--danger)':($m->stock_apres<=$m->produit->seuil_critique*2?'var(--warning)':'var(--success)'); @endphp
                                <span
                                    style="font-weight:700;font-size:13px;color:{{ $col }}">{{ number_format($m->stock_apres,2,',','') }}</span>
                            </td>
                            <td style="font-size:12px;color:var(--text-muted)">{{ $m->fournisseur?->nom??'—' }}</td>
                            <td style="font-size:12px;max-width:160px">
                                <span class="text-truncate d-inline-block" style="max-width:150px"
                                      title="{{ $m->motif }}">{{ $m->motif?:'—' }}</span>
                                @if($m->type==='perte'&&$m->cout_total>0)
                                    <div
                                        style="font-size:11px;color:var(--danger);font-weight:600">{{ number_format($m->cout_total,0,',',' ') }}
                                        FCFA
                                    </div>
                                @endif
                            </td>
                            <td style="font-size:12px">
                                <div style="font-weight:600">{{ $m->user->name }}</div>
                                <div style="font-size:10px;color:var(--text-muted)">{{ ucfirst($m->user->role) }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted"><i
                                    class="bi bi-arrow-left-right fs-2 d-block mb-2"></i>Aucun mouvement
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($mouvements->hasPages())
            <div class="card-body border-top"
                 style="border-color:var(--border-color)!important;padding:12px 20px">{{ $mouvements->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection
