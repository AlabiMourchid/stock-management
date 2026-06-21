@extends('layouts.app')

@section('title', 'Gestion des fournisseurs')
@section('page-title', 'Administration — Fournisseurs')

@section('content')

    {{-- ===== Toolbar ===== --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <p class="text-muted mb-0" style="font-size:13px">
            {{ $fournisseurs->count() }} fournisseur(s) enregistré(s)
        </p>
        <button class="btn btn-amira btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreer">
            <i class="bi bi-plus-circle me-1"></i>Nouveau fournisseur
        </button>
    </div>

    {{-- ===== KPIs ===== --}}
    @php
        $nbActifs   = $fournisseurs->where('actif', true)->count();
        $nbInactifs = $fournisseurs->where('actif', false)->count();

        // Fournisseurs les plus actifs (via mouvements de stock)
        $topFournisseurId = \App\Models\StockMouvement::whereNotNull('fournisseur_id')
            ->select('fournisseur_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as nb'))
            ->groupBy('fournisseur_id')
            ->orderByDesc('nb')
            ->value('fournisseur_id');

        $topFournisseur = $topFournisseurId
            ? \App\Models\Fournisseur::find($topFournisseurId)?->nom
            : '—';

        $nbLivraisonsTotal = \App\Models\StockMouvement::whereNotNull('fournisseur_id')
            ->where('type', 'entree')
            ->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-truck"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Fournisseurs actifs</div>
                    <div class="stat-value" style="font-size:22px">{{ $nbActifs }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-truck-flatbed"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Fournisseurs Inactifs</div>
                    <div class="stat-value" style="font-size:22px">{{ $nbInactifs }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-box-seam"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Livraisons enregistrées</div>
                    <div class="stat-value" style="font-size:22px">{{ $nbLivraisonsTotal }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-star"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Principal fournisseur</div>
                    <div class="stat-value" style="font-size:14px;margin-top:4px">{{ $topFournisseur }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Grille des fournisseurs ===== --}}
    <div class="row g-3 mb-4" id="gridFournisseurs">
        @forelse($fournisseurs as $f)
            @php
                $nbLivraisons = \App\Models\StockMouvement::where('fournisseur_id', $f->id)
                    ->where('type', 'entree')->count();
                $derniereDate = \App\Models\StockMouvement::where('fournisseur_id', $f->id)
                    ->latest('date_mouvement')->value('date_mouvement');
            @endphp
            <div class="col-md-6 col-xl-4 fournisseur-card-wrap">
                <div class="card h-100 {{ !$f->actif ? 'opacity-60' : '' }}" style="transition:.2s">
                    <div class="card-body">

                        {{-- En-tête --}}
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div style="
                            width:44px;height:44px;border-radius:12px;
                            background:{{ $f->actif ? 'var(--amira-orange-light)' : 'var(--border-color)' }};
                            display:flex;align-items:center;justify-content:center;
                            font-size:22px;flex-shrink:0">
                                    🚚
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:14px">{{ $f->nom }}</div>
                                    @if($f->actif)
                                        <span class="stock-badge stock-ok" style="font-size:10px">Actif</span>
                                    @else
                                        <span class="stock-badge stock-critique" style="font-size:10px">Inactif</span>
                                    @endif
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="dropdown" title="Actions">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="font-size:13px">
                                    <li>
                                        <button class="dropdown-item btn-edit-fournisseur"
                                                data-id="{{ $f->id }}"
                                                data-nom="{{ $f->nom }}"
                                                data-telephone="{{ $f->telephone }}"
                                                data-email="{{ $f->email }}"
                                                data-adresse="{{ $f->adresse }}"
                                                data-actif="{{ $f->actif ? '1' : '0' }}"
                                                data-bs-toggle="modal" data-bs-target="#modalEditer">
                                            <i class="bi bi-pencil me-2 text-warning"></i>Modifier
                                        </button>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                           href="{{ route('stock.mouvement') }}?fournisseur_id={{ $f->id }}">
                                            <i class="bi bi-clock-history me-2 text-info"></i>Voir livraisons
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form method="POST"
                                              action="{{ route('admin.fournisseurs.destroy', $f) }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="dropdown-item text-danger"
                                                    data-msg="{{ $f->actif ? 'Désactiver' : 'Réactiver' }} le fournisseur « {{ $f->nom }} » ?"
                                                    data-confirm-text="{{ $f->actif ? 'Désactiver' : 'Réactiver' }}"
                                                    onclick="confirmForm(this.closest('form'), this.dataset.msg, {type:'danger',title:'Fournisseur',confirmText:this.dataset.confirmText})">
                                                <i class="bi bi-slash-circle me-2"></i>
                                                {{ $f->actif ? 'Désactiver' : 'Réactiver' }}
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Infos de contact --}}
                        <div class="d-flex flex-column gap-2 mb-3">
                            @if($f->telephone)
                                <div class="d-flex align-items-center gap-2" style="font-size:13px">
                                    <i class="bi bi-telephone" style="color:var(--text-muted);width:16px"></i>
                                    <a href="tel:{{ $f->telephone }}"
                                       style="color:var(--text-primary);text-decoration:none">
                                        {{ $f->telephone }}
                                    </a>
                                </div>
                            @endif
                            @if($f->email)
                                <div class="d-flex align-items-center gap-2" style="font-size:13px">
                                    <i class="bi bi-envelope" style="color:var(--text-muted);width:16px"></i>
                                    <a href="mailto:{{ $f->email }}"
                                       style="color:var(--amira-orange);text-decoration:none">
                                        {{ $f->email }}
                                    </a>
                                </div>
                            @endif
                            @if($f->adresse)
                                <div class="d-flex align-items-start gap-2" style="font-size:13px">
                                    <i class="bi bi-geo-alt" style="color:var(--text-muted);width:16px;margin-top:1px"></i>
                                    <span style="color:var(--text-secondary)">{{ $f->adresse }}</span>
                                </div>
                            @endif
                            @if(!$f->telephone && !$f->email && !$f->adresse)
                                <div style="font-size:12px;color:var(--text-muted);font-style:italic">
                                    Aucune coordonnée renseignée
                                </div>
                            @endif
                        </div>

                        {{-- Statistiques livraisons --}}
                        <div class="border-top pt-3 d-flex justify-content-between"
                             style="border-color:var(--border-color)!important">
                            <div class="text-center">
                                <div style="font-size:18px;font-weight:700;color:var(--amira-orange)">
                                    {{ $nbLivraisons }}
                                </div>
                                <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">
                                    Livraison(s)
                                </div>
                            </div>
                            <div class="text-center">
                                <div style="font-size:13px;font-weight:600;color:var(--text-primary)">
                                    @if($derniereDate)
                                        {{ \Carbon\Carbon::parse($derniereDate)->locale('fr')->isoFormat('D MMM YY') }}
                                    @else
                                        —
                                    @endif
                                </div>
                                <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">
                                    Dernière livraison
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('stock.mouvement') }}?fournisseur_id={{ $f->id }}"
                                   class="btn btn-sm btn-outline-secondary" style="font-size:12px">
                                    <i class="bi bi-clock-history me-1"></i>Historique
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-truck fs-1 d-block mb-3 opacity-40"></i>
                        <div style="font-size:15px;font-weight:600">Aucun fournisseur enregistré</div>
                        <div style="font-size:13px;margin-top:4px">
                            Ajoutez votre premier fournisseur pour commencer à suivre vos approvisionnements.
                        </div>
                        <button class="btn btn-amira mt-3"
                                data-bs-toggle="modal" data-bs-target="#modalCreer">
                            <i class="bi bi-plus-circle me-1"></i>Ajouter un fournisseur
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- ===== Modal Créer fournisseur ===== --}}
    <div class="modal fade" id="modalCreer" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.fournisseurs.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-circle text-success me-2"></i>Nouveau fournisseur
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin._fournisseur_form')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-amira">
                            <i class="bi bi-check2 me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Modal Éditer fournisseur ===== --}}
    <div class="modal fade" id="modalEditer" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="formEditer" action="">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-pencil text-warning me-2"></i>Modifier le fournisseur
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin._fournisseur_form')
                        {{-- Champ actif uniquement en édition --}}
                        <div class="mt-3 pt-3 border-top" style="border-color:var(--border-color)!important">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       name="actif" id="edit-actif" value="1">
                                <label class="form-check-label" for="edit-actif" style="font-size:13px">
                                    Fournisseur actif
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-amira">
                            <i class="bi bi-check2 me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.btn-edit-fournisseur').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const f = document.getElementById('formEditer');
                    f.action = '/admin/fournisseurs/' + btn.dataset.id;
                    f.querySelector('[name="nom"]').value       = btn.dataset.nom       ?? '';
                    f.querySelector('[name="telephone"]').value = btn.dataset.telephone ?? '';
                    f.querySelector('[name="email"]').value     = btn.dataset.email     ?? '';
                    f.querySelector('[name="adresse"]').value   = btn.dataset.adresse   ?? '';
                    document.getElementById('edit-actif').checked = btn.dataset.actif === '1';
                });
            });
        });
    </script>
@endpush
