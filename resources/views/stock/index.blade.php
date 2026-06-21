@extends('layouts.app')
@section('title', 'Inventaire Stock')
@section('page-title', 'Inventaire — Matières premières')

@section('content')


    {{-- Toolbar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <select name="filtre" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <option value="critique" {{ request('filtre')==='critique'?'selected':'' }}>🔴 Stock critique</option>
            </select>
            <div class="input-group input-group-sm" style="width:220px">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Chercher…" value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-sm btn-outline-secondary">Filtrer</button>
        </form>
        <div class="d-flex gap-2">
            @can('manage-stock')
                <a href="{{ route('stock.fin-service') }}" class="btn btn-sm btn-amira">
                    <i class="bi bi-arrow-down-circle me-1"></i>Saisie fin de service
                </a>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEntree">
                    <i class="bi bi-plus-circle me-1"></i>Approvisionnement
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-amira mb-0">
                    <thead>
                    <tr>
                        <th>Matière première</th><th class="text-center">Stock actuel</th>
                        <th style="width:180px">Niveau</th><th class="text-center">Statut</th>
                        <th>Coût unit.</th><th>Fournisseur</th>
                        @can('manage-stock')<th></th>@endcan
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($produits as $p)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span style="font-size:22px">{{ $p->emoji }}</span>
                                    <div>
                                        <div style="font-weight:600">{{ $p->nom }}</div>
                                        <div style="font-size:11px;color:var(--text-muted)">Seuil : {{ $p->seuil_critique }} {{ $p->unite }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center" style="font-weight:700;font-size:15px">
                                {{ number_format($p->stock_actuel, 2, ',', '') }}
                                <small style="font-weight:400;color:var(--text-muted)">{{ $p->unite }}</small>
                            </td>
                            <td>
                                <div class="stock-progress-wrap">
                                    <div class="progress flex-grow-1">
                                        <div class="progress-bar progress-bar-{{ $p->statut_stock }}" style="width:{{ $p->pourcentage_stock }}%"></div>
                                    </div>
                                    <span style="font-size:11px;width:32px;text-align:right;color:var(--text-muted)">{{ $p->pourcentage_stock }}%</span>
                                </div>
                            </td>
                            <td class="text-center">
                            <span class="stock-badge stock-{{ $p->statut_stock }}">
                                {{ match($p->statut_stock){ 'ok'=>'OK','moyen'=>'Moyen','critique'=>'Critique' } }}
                            </span>
                            </td>
                            <td style="font-size:13px">{{ number_format($p->cout_unitaire, 0, ',', ' ') }} FCFA</td>
                            <td style="font-size:12px;color:var(--text-muted)">{{ $p->fournisseur?->nom ?? '—' }}</td>
                            @can('manage-stock')
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary"
                                            data-produit-id="{{ $p->id }}" data-produit-nom="{{ $p->nom }}" data-produit-unite="{{ $p->unite }}"
                                            onclick="document.getElementById('entree-produit').value=this.dataset.produitId; document.getElementById('entree-unite').textContent='('+this.dataset.produitUnite+')';"
                                            data-bs-toggle="modal" data-bs-target="#modalEntree">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning ms-1"
                                            data-id="{{ $p->id }}" data-nom="{{ $p->nom }}" data-unite="{{ $p->unite }}" data-stock="{{ $p->stock_actuel }}"
                                            onclick="remplirInventaire(this.dataset)"
                                            data-bs-toggle="modal" data-bs-target="#modalInventaire">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>Aucun produit trouvé
                            </td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Entrée --}}
    <div class="modal fade" id="modalEntree" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('stock.entree') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle text-success me-2"></i>Approvisionnement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Matière première</label>
                            <select name="produit_stock_id" id="entree-produit" class="form-select" required>
                                @foreach($produits as $p)
                                    <option value="{{ $p->id }}">{{ $p->emoji }} {{ $p->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantité <span id="entree-unite" class="text-muted fw-normal"></span></label>
                            <input type="number" name="quantite" class="form-control" min="0.01" step="0.01" required placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fournisseur <span class="text-muted fw-normal">(optionnel)</span></label>
                            <select name="fournisseur_id" class="form-select">
                                <option value="">— Aucun —</option>
                                @foreach($fournisseurs as $f)
                                    <option value="{{ $f->id }}">{{ $f->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date_mouvement" class="form-control" value="{{ today()->toDateString() }}">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Note</label>
                            <input type="text" name="motif" class="form-control" placeholder="ex: Livraison matin">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-amira">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Inventaire --}}
    <div class="modal fade" id="modalInventaire" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('stock.inventaire') }}">
                    @csrf
                    <input type="hidden" name="produit_stock_id" id="inv-id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil text-warning me-2"></i>Correction inventaire</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning py-2" style="font-size:13px">
                            <i class="bi bi-info-circle me-1"></i>Remplace le stock actuel par la valeur saisie.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Produit</label>
                            <input type="text" id="inv-nom" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock actuel</label>
                            <input type="text" id="inv-stock" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nouvelle quantité réelle <span id="inv-unite" class="text-muted fw-normal"></span></label>
                            <input type="number" name="nouvelle_qte" class="form-control" min="0" step="0.01" required placeholder="0">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Motif</label>
                            <input type="text" name="motif" class="form-control" placeholder="ex: Inventaire physique">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Corriger</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function remplirInventaire(d) {
            document.getElementById('inv-id').value    = d.id;
            document.getElementById('inv-nom').value   = d.nom;
            document.getElementById('inv-stock').value = d.stock + ' ' + d.unite;
            document.getElementById('inv-unite').textContent = '(' + d.unite + ')';
        }
    </script>
@endpush
