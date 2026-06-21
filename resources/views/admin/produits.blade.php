@extends('layouts.app')

@section('title', 'Gestion des produits')
@section('page-title', 'Administration — Produits & Prix')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <p class="text-muted mb-0" style="font-size:13px">{{ $produits->count() }} produits au total</p>
        <button class="btn btn-amira btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreer">
            <i class="bi bi-plus-circle me-1"></i>Nouveau produit
        </button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-amira mb-0">
                    <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th>Prix vente</th>
                        <th>Coût unitaire</th>
                        <th>Seuil critique</th>
                        <th>POS</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($produits as $p)
                        <tr>
                            <td>
                                <strong class="ms-1">{{ $p->nom }}</strong>
                            </td>
                            <td>{{ $p->categorie->nom }}</td>
                            <td>{{ number_format($p->prix_vente, 0, ',', ' ') }} FCFA</td>
                            <td style="color:var(--text-muted)">{{ number_format($p->cout_unitaire, 0, ',', ' ') }} FCFA</td>
                            <td>{{ $p->seuil_critique }} {{ $p->unite }}</td>
                            <td>
                                @if($p->visible_pos)
                                    <span class="badge bg-success-subtle text-success">Oui</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Non</span>
                                @endif
                            </td>
                            <td>
                                @if($p->actif)
                                    <span class="stock-badge stock-ok">Actif</span>
                                @else
                                    <span class="stock-badge stock-critique">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary"
                                        onclick="remplirEdit({{ $p->id }}, {{ json_encode($p) }})"
                                        data-bs-toggle="modal" data-bs-target="#modalEditer">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.produits.destroy', $p) }}"
                                      class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                            data-msg="Désactiver le produit « {{ $p->nom }} » ?"
                                            onclick="confirmForm(this.closest('form'), this.dataset.msg, {type:'danger',title:'Désactiver le produit',confirmText:'Désactiver'})">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Modal Créer produit ===== --}}
    <div class="modal fade" id="modalCreer" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.produits.store') }}">
                    @csrf
                    @include('admin._produit_form', ['produit' => null])
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-amira">Créer le produit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Modal Éditer produit ===== --}}
    <div class="modal fade" id="modalEditer" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="formEditer" action="">
                    @csrf @method('PUT')
                    @include('admin._produit_form', ['produit' => null])
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-amira">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function remplirEdit(id, p) {
            const f = document.getElementById('formEditer');
            f.action = `/admin/produits/${id}`;
            f.querySelector('[name="categorie_id"]').value  = p.categorie_id;
            f.querySelector('[name="nom"]').value           = p.nom;
            f.querySelector('[name="prix_vente"]').value    = p.prix_vente;
            f.querySelector('[name="cout_unitaire"]').value = p.cout_unitaire;
            f.querySelector('[name="unite"]').value         = p.unite;
            f.querySelector('[name="seuil_critique"]').value= p.seuil_critique;
            f.querySelector('[name="visible_pos"]').checked = p.visible_pos;
            f.querySelector('[name="actif"]').checked       = p.actif;
        }
    </script>
@endpush
