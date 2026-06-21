@extends('layouts.app')
@section('title', 'Stock - Matières premières')
@section('page-title', 'Matières premières')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <p class="text-muted mb-0" style="font-size:13px">{{ $produits->count() }} matière(s) première(s)</p>
        <button class="btn btn-amira btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreer">
            <i class="bi bi-plus-circle me-1"></i>Nouvelle matière première
        </button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-amira mb-0">
                    <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Unité</th>
                        <th class="text-center">Stock actuel</th>
                        <th class="text-center">Seuil critique</th>
                        <th>Coût unit.</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($produits as $p)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span style="font-weight:600">{{ $p->nom }}</span>
                                </div>
                            </td>
                            <td style="color:var(--text-muted)">{{ $p->unite }}</td>
                            <td class="text-center">
                            <span class="stock-badge stock-{{ $p->statut_stock }}">
                                {{ number_format($p->stock_actuel, 2, ',', '') }} {{ $p->unite }}
                            </span>
                            </td>
                            <td class="text-center"
                                style="color:var(--text-muted)">{{ $p->seuil_critique }} {{ $p->unite }}</td>
                            <td>{{ number_format($p->cout_unitaire, 0, ',', ' ') }} FCFA</td>
                            <td class="text-center">
                                <span
                                    class="stock-badge {{ $p->actif ? 'stock-ok' : 'stock-critique' }}">{{ $p->actif ? 'Actif' : 'Inactif' }}</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-secondary btn-edit-mp"
                                        data-id="{{ $p->id }}" data-nom="{{ $p->nom }}"
                                        data-unite="{{ $p->unite }}" data-seuil="{{ $p->seuil_critique }}"
                                        data-cout="{{ $p->cout_unitaire }}"
                                        data-actif="{{ $p->actif ? '1' : '0' }}"
                                        data-bs-toggle="modal" data-bs-target="#modalEditer">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.stock.destroy', $p) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                            onclick="confirmForm(this.closest('form'), 'Désactiver cette matière première du stock ?', {type:'danger',title:'Désactiver la matière première',confirmText:'Désactiver'})">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Aucune matière première</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Créer --}}
    <div class="modal fade" id="modalCreer" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.stock.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle text-success me-2"></i>Nouvelle matière
                            première</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin._produit_stock_form')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-amira">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Éditer --}}
    <div class="modal fade" id="modalEditer" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="formEditer" action="">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil text-warning me-2"></i>Modifier la matière
                            première</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin._produit_stock_form')
                        <div class="mt-3 pt-3 border-top" style="border-color:var(--border-color)!important">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="actif" id="edit-actif" value="1">
                                <label class="form-check-label" for="edit-actif" style="font-size:13px">Produit
                                    actif</label>
                            </div>
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
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.btn-edit-mp').forEach(btn => {
            btn.addEventListener('click', () => {
                const f = document.getElementById('formEditer');
                f.action = `/admin/stock/${btn.dataset.id}`;
                f.querySelector('[name="nom"]').value = btn.dataset.nom;
                f.querySelector('[name="unite"]').value = btn.dataset.unite;
                f.querySelector('[name="seuil_critique"]').value = btn.dataset.seuil;
                f.querySelector('[name="cout_unitaire"]').value = btn.dataset.cout;
                f.querySelector('[name="fournisseur_id"]').value = btn.dataset.fournisseur ?? '';
                document.getElementById('edit-actif').checked = btn.dataset.actif === '1';
            });
        });
    </script>
@endpush
