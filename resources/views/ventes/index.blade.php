@extends('layouts.app')

@section('title', 'Historique des ventes')
@section('page-title', 'Historique des ventes')

@section('content')

    {{-- ===== Filtres & Toolbar ===== --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label mb-1">Date</label>
                    <input type="date" name="date" class="form-control form-control-sm"
                           value="{{ request('date', today()->toDateString()) }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label mb-1">Statut</label>
                    <select name="statut" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="en_preparation" {{ request('statut') === 'en_preparation' ? 'selected' : '' }}>En préparation</option>
                       <option value="livre"          {{ request('statut') === 'livre'          ? 'selected' : '' }}>Livré</option>
                        <option value="annule"         {{ request('statut') === 'annule'         ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label mb-1">Paiement</label>
                    <select name="mode_paiement" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="especes"      {{ request('mode_paiement') === 'especes'      ? 'selected' : '' }}>Espèces</option>
                        <option value="mobile_money" {{ request('mode_paiement') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label mb-1">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="sur_place" {{ request('type') === 'sur_place' ? 'selected' : '' }}>Sur place</option>
                        <option value="emporter"  {{ request('type') === 'emporter'  ? 'selected' : '' }}>À emporter</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-12 d-flex gap-2">
                    <button type="submit" class="btn btn-amira btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('ventes.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== KPIs du filtre courant ===== --}}
    @php
        $ca       = $commandes->getCollection()->whereNotIn('statut', ['annule'])->sum('total_ttc');
        $nbValide = $commandes->getCollection()->whereNotIn('statut', ['annule'])->count();
        $nbAnnule = $commandes->getCollection()->where('statut', 'annule')->count();
        $moyenne  = $nbValide > 0 ? $ca / $nbValide : 0;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-currency-exchange"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Chiffre d'affaire</div>
                    <div class="stat-value" style="font-size:18px">{{ number_format($ca, 0, ',', ' ') }}<small class="text-muted" style="font-size:11px"> FCFA</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-receipt-cutoff"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Commandes</div>
                    <div class="stat-value" style="font-size:18px">{{ $nbValide }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-graph-up"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Panier moyen</div>
                    <div class="stat-value" style="font-size:18px">{{ number_format($moyenne, 0, ',', ' ') }}<small class="text-muted" style="font-size:11px"> FCFA</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Annulées</div>
                    <div class="stat-value" style="font-size:18px">{{ $nbAnnule }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Tableau des commandes ===== --}}
    <div class="card">
        <div class="card-header">
        <span class="card-title">
            Commandes —
            <span class="text-muted fw-normal" style="font-size:13px">
                {{ \Carbon\Carbon::parse(request('date', today()))->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
            </span>
        </span>
            <span style="font-size:12px;color:var(--text-muted)">{{ $commandes->total() }} résultat(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-amira mb-0">
                    <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Heure</th>
                        <th>Articles</th>
                        <th>Type</th>
                        <th>Paiement</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Caissier</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($commandes as $cmd)
                        <tr>
                            <td>
                                <a href="{{ route('ventes.recu', $cmd) }}" target="_blank"
                                   style="font-weight:700;color:var(--amira-orange);text-decoration:none">
                                    {{ $cmd->numero }}
                                </a>
                            </td>
                            <td style="color:var(--text-muted)">
                                {{ $cmd->created_at->format('H:i') }}
                            </td>
                            <td>
                                <div style="max-width:200px">
                                    @foreach($cmd->lignes->take(2) as $ligne)
                                        <div style="font-size:12px">
                                            {{ $ligne->quantite }}× {{ $ligne->menu->nom }}
                                        </div>
                                    @endforeach
                                    @if($cmd->lignes->count() > 2)
                                        <span style="font-size:11px;color:var(--text-muted)">
                                        +{{ $cmd->lignes->count() - 2 }} autre(s)
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($cmd->type === 'sur_place')
                                    <span style="font-size:12px">Sur place</span>
                                @else
                                    <span style="font-size:12px">À emporter</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $pmIcons = [
                                        'especes'      => ['Espèces'],
                                        'mobile_money' => ['Mobile'],
                                        'carte'        => ['Carte'],
                                    ];
                                    [$label] = $pmIcons[$cmd->mode_paiement] ?? [ $cmd->mode_paiement];
                                @endphp
                                <span style="font-size:12px"> {{ $label }}</span>
                            </td>
                            <td style="font-weight:700;white-space:nowrap">
                                {{ number_format($cmd->total_ttc, 0, ',', ' ') }}
                                <small style="font-weight:400;color:var(--text-muted)">FCFA</small>
                            </td>
                            <td>
                            <span class="badge-status badge-{{ str_replace('_', '-', $cmd->statut) }}">
                                {{ $cmd->statut_libelle }}
                            </span>
                            </td>
                            <td style="color:var(--text-muted);font-size:12px">
                                {{ $cmd->user->name }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- Reçu
                                    <a href="{{ route('ventes.recu', $cmd) }}" target="_blank"
                                       class="btn btn-sm btn-outline-secondary" title="Imprimer reçu">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    --}}

                                    {{-- Changer statut (pas pour annulé/livré) --}}
                                    @if(!in_array($cmd->statut, ['annule', 'livre']))
                                        <div class="dropup">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    data-bs-toggle="dropdown" title="Changer statut">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" style="font-size:13px;min-width:160px">
                                                {{--@if($cmd->statut === 'en_attente')
                                                    <li>
                                                        <form method="POST" action="{{ route('ventes.statut', $cmd) }}">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="statut" value="en_preparation">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="bi bi-fire text-info me-2"></i>En préparation
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                @if(in_array($cmd->statut, ['en_attente', 'en_preparation']))
                                                    <li>
                                                        <form method="POST" action="{{ route('ventes.statut', $cmd) }}">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="statut" value="pret">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="bi bi-check-circle text-success me-2"></i>Prêt
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif--}}
                                                @if($cmd->statut === 'en_preparation')
                                                    <li>
                                                        <form method="POST" action="{{ route('ventes.statut', $cmd) }}">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="statut" value="livre">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="bi bi-bag-check text-secondary me-2"></i>Livré
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('ventes.statut', $cmd) }}">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="statut" value="annule">
                                                        <button type="button" class="dropdown-item text-danger"
                                                                onclick="confirmForm(this.closest('form'), 'Annuler cette commande ? Cette action ne peut pas être défaite.', {type:'danger',title:'Annuler la commande',confirmText:'Annuler la commande'})">
                                                            <i class="bi bi-x-circle me-2"></i>Annuler
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-2 d-block mb-2 opacity-50"></i>
                                Aucune commande pour ce filtre
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($commandes->hasPages())
            <div class="card-body border-top" style="border-color:var(--border-color)!important;padding:12px 20px">
                {{ $commandes->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection
