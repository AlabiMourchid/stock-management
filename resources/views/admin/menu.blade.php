@extends('layouts.app')
@section('title', 'Carte & Menu')
@section('page-title', 'Carte & Menu')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <p class="text-muted mb-0" style="font-size:13px">{{ $articles->count() }} article(s) au menu</p>
        <button class="btn btn-amira btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreer">
            <i class="bi bi-plus-circle me-1"></i>Nouvel article
        </button>
    </div>

    {{-- Filtre catégories --}}
    <div class="d-flex gap-2 flex-wrap mb-3">
        <button class="btn btn-sm btn-amira cat-btn" data-cat="all">Tout</button>
        @foreach($categories as $cat)
            <button class="btn btn-sm btn-outline-secondary cat-btn" data-cat="{{ $cat->id }}">
                {{ $cat->emoji }} {{ $cat->nom }}
            </button>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-amira mb-0">
                    <thead>
                    <tr>
                        <th>Article</th><th>Catégorie</th><th class="text-center">Prix</th>
                        <th class="text-center">POS</th><th class="text-center">Statut</th><th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($articles as $a)
                        <tr class="art-row" data-cat="{{ $a->categorie_id }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span style="font-size:22px">{{ $a->emoji }}</span>
                                    <div>
                                        <div style="font-weight:600">{{ $a->nom }}</div>
                                        @if($a->description)
                                            <div style="font-size:11px;color:var(--text-muted)">{{ Str::limit($a->description, 50) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $a->categorie->emoji }} {{ $a->categorie->nom }}</td>
                            <td class="text-center" style="font-weight:700">{{ number_format($a->prix_vente, 0, ',', ' ') }} FCFA</td>
                            <td class="text-center">
                            <span class="badge {{ $a->disponible ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $a->disponible ? 'Visible' : 'Masqué' }}
                            </span>
                            </td>
                            <td class="text-center">
                            <span class="stock-badge {{ $a->actif ? 'stock-ok' : 'stock-critique' }}">
                                {{ $a->actif ? 'Actif' : 'Inactif' }}
                            </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-secondary btn-edit-article"
                                        data-id="{{ $a->id }}"
                                        data-nom="{{ $a->nom }}"
                                        data-emoji="{{ $a->emoji }}"
                                        data-categorie="{{ $a->categorie_id }}"
                                        data-prix="{{ $a->prix_vente }}"
                                        data-description="{{ $a->description }}"
                                        data-disponible="{{ $a->disponible ? '1' : '0' }}"
                                        data-actif="{{ $a->actif ? '1' : '0' }}"
                                        data-bs-toggle="modal" data-bs-target="#modalEditer">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.menu.destroy', $a) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                            onclick="confirmForm(this.closest('form'), 'Désactiver cet article du menu ?', {type:'danger',title:'Désactiver l\'article',confirmText:'Désactiver'})">
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

    {{-- Modal Créer --}}
    <div class="modal fade" id="modalCreer" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.menu.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle text-success me-2"></i>Nouvel article menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin._article_menu_form', ['article' => null])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-amira"><i class="bi bi-check2 me-1"></i>Créer</button>
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
                        <h5 class="modal-title"><i class="bi bi-pencil text-warning me-2"></i>Modifier l'article</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin._article_menu_form', ['article' => null])
                        <div class="mt-3 pt-3 border-top d-flex gap-4" style="border-color:var(--border-color)!important">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="disponible" id="edit-disponible" value="1">
                                <label class="form-check-label" for="edit-disponible" style="font-size:13px">Visible sur le menu</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="actif" id="edit-actif" value="1">
                                <label class="form-check-label" for="edit-actif" style="font-size:13px">Article actif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-amira"><i class="bi bi-check2 me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.cat-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.cat-btn').forEach(b => { b.classList.remove('btn-amira','active'); b.classList.add('btn-outline-secondary'); });
                btn.classList.add('btn-amira','active'); btn.classList.remove('btn-outline-secondary');
                const cat = btn.dataset.cat;
                document.querySelectorAll('.art-row').forEach(r => { r.style.display = (cat==='all'||r.dataset.cat===cat)?'':'none'; });
            });
        });
        document.querySelectorAll('.btn-edit-article').forEach(btn => {
            btn.addEventListener('click', () => {
                const f = document.getElementById('formEditer');
                f.action = `/admin/menu/${btn.dataset.id}`;
                f.querySelector('[name="nom"]').value          = btn.dataset.nom;
                f.querySelector('[name="categorie_id"]').value = btn.dataset.categorie;
                f.querySelector('[name="prix_vente"]').value   = btn.dataset.prix;
                f.querySelector('[name="description"]').value  = btn.dataset.description;
                document.getElementById('edit-disponible').checked = btn.dataset.disponible === '1';
                document.getElementById('edit-actif').checked      = btn.dataset.actif === '1';
            });
        });
    </script>
@endpush
