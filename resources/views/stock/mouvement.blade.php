@extends('layouts.app')

@section('title', 'Historique des mouvements')
@section('page-title', 'Mouvements de stock')

@section('content')

    {{-- ===== Filtres ===== --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">

                <div class="col-md-2 col-sm-6">
                    <label class="form-label mb-1">Du</label>
                    <input type="date" name="debut" class="form-control form-control-sm"
                           value="{{ request('debut', today()->toDateString()) }}">
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label mb-1">Au</label>
                    <input type="date" name="fin" class="form-control form-control-sm"
                           value="{{ request('fin', today()->toDateString()) }}">
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label mb-1">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="entree"     {{ request('type') === 'entree'     ? 'selected' : '' }}>Entrée</option>
                        <option value="sortie"     {{ request('type') === 'sortie'     ? 'selected' : '' }}>Sortie</option>
                        <option value="inventaire" {{ request('type') === 'inventaire' ? 'selected' : '' }}>Inventaire</option>
                        <option value="perte"      {{ request('type') === 'perte'      ? 'selected' : '' }}>Perte</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label mb-1">Produit</label>
                    <select name="produit_id" class="form-select form-select-sm">
                        <option value="">Tous les produits</option>
                        @foreach(\App\Models\Produit::actif()->orderBy('nom')->get() as $p)
                            <option value="{{ $p->id }}" {{ request('produit_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-amira btn-sm flex-grow-1">
                            <i class="bi bi-search me-1"></i>Filtrer
                        </button>
                        <a href="{{ route('stock.mouvement') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- ===== KPIs de la période ===== --}}
    @php
        $totalEntrees  = $mouvements->getCollection()->where('type', 'entree')->sum('quantite');
        $totalSorties  = $mouvements->getCollection()->where('type', 'sortie')->sum('quantite');
        $totalPertes   = $mouvements->getCollection()->where('type', 'perte')->sum('quantite');
        $coutPertes    = $mouvements->getCollection()->where('type', 'perte')->sum('cout_total');
        $nbInventaires = $mouvements->getCollection()->where('type', 'inventaire')->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-arrow-up-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Entrées</div>
                    <div class="stat-value" style="font-size:20px">{{ number_format($totalEntrees, 2, ',', '') }}</div>
                    <div class="stat-sub">approvisionnements</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-arrow-down-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Sorties</div>
                    <div class="stat-value" style="font-size:20px">{{ number_format($totalSorties, 2, ',', '') }}</div>
                    <div class="stat-sub">fins de service</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Pertes</div>
                    <div class="stat-value" style="font-size:20px">{{ number_format($totalPertes, 2, ',', '') }}</div>
                    <div class="stat-sub">{{ number_format($coutPertes, 0, ',', ' ') }} FCFA</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-pencil-square"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Inventaires</div>
                    <div class="stat-value" style="font-size:20px">{{ $nbInventaires }}</div>
                    <div class="stat-sub">corrections manuelles</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Tableau des mouvements ===== --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Historique des mouvements ({{ $mouvements->total() }})</span>

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
                        <th class="text-center">Stock avant</th>
                        <th class="text-center">Stock après</th>
                        <th>Motif</th>
                        <th>Auteur</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($mouvements as $m)
                        <tr>
                            {{-- Date --}}
                            <td style="white-space:nowrap">
                                <div style="font-weight:600;font-size:13px">
                                    {{ $m->date_mouvement->locale('fr')->isoFormat('D MMM') }}
                                </div>
                                <div style="font-size:11px;color:var(--text-muted)">
                                    {{ $m->created_at->format('H:i') }}
                                </div>
                            </td>

                            {{-- Produit --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <div style="font-weight:600;font-size:13px">{{ $m->produit->nom }}</div>
                                        <div style="font-size:11px;color:var(--text-muted)">
                                            {{ $m->produit->categorie->nom ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Type --}}
                            <td class="text-center">
                            <span class="mouv-badge mouv-{{ $m->type }}">
                                @switch($m->type)
                                    @case('entree')      Entrée      @break
                                    @case('sortie')      Sortie      @break
                                    @case('inventaire')  Inventaire  @break
                                    @case('perte')       Perte       @break
                                @endswitch
                            </span>
                            </td>

                            {{-- Quantité --}}
                            <td class="text-center">
                            <span style="font-weight:700;font-size:15px;
                                color:{{ in_array($m->type, ['entree']) ? 'var(--success)' : (in_array($m->type, ['sortie','perte']) ? 'var(--danger)' : 'var(--info)') }}">
                                {{ in_array($m->type, ['entree']) ? '+' : (in_array($m->type, ['sortie','perte']) ? '−' : '≈') }}{{ number_format($m->quantite, 2, ',', '') }}
                            </span>
                                <div style="font-size:11px;color:var(--text-muted)">{{ $m->produit->unite }}</div>
                            </td>

                            {{-- Stock avant --}}
                            <td class="text-center">
                            <span style="font-size:13px;color:var(--text-secondary)">
                                {{ number_format($m->stock_avant, 2, ',', '') }}
                            </span>
                                <div style="font-size:10px;color:var(--text-muted)">{{ $m->produit->unite }}</div>
                            </td>

                            {{-- Stock après --}}
                            <td class="text-center">
                                @php
                                    $couleurApres = $m->stock_apres <= $m->produit->seuil_critique
                                        ? 'var(--danger)'
                                        : ($m->stock_apres <= $m->produit->seuil_critique * 2
                                            ? 'var(--warning)'
                                            : 'var(--success)');
                                @endphp
                                <span style="font-size:13px;font-weight:700;color:{{ $couleurApres }}">
                                {{ number_format($m->stock_apres, 2, ',', '') }}
                            </span>
                                <div style="font-size:10px;color:var(--text-muted)">{{ $m->produit->unite }}</div>
                            </td>


                            {{-- Motif --}}
                            <td style="font-size:12px;max-width:180px">
                            <span class="text-truncate d-inline-block" style="max-width:170px"
                                  title="{{ $m->motif }}">
                                {{ $m->motif ?: '—' }}
                            </span>
                                @if($m->type === 'perte' && $m->cout_total > 0)
                                    <div style="font-size:11px;color:var(--danger);font-weight:600">
                                        Coût : {{ number_format($m->cout_total, 0, ',', ' ') }} FCFA
                                    </div>
                                @endif
                            </td>

                            {{-- Opérateur --}}
                            <td style="font-size:12px">
                                <div style="font-weight:600">{{ $m->user->name }}</div>
                                <div style="font-size:10px;color:var(--text-muted)">{{ ucfirst($m->user->role) }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-arrow-left-right fs-2 d-block mb-2 opacity-50"></i>
                                Aucun mouvement pour cette période
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($mouvements->hasPages())
            <div class="card-body border-top" style="border-color:var(--border-color)!important;padding:12px 20px">
                {{ $mouvements->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection
