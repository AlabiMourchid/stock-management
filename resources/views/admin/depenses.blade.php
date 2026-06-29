@extends('layouts.app')
@section('title', 'Gestion des dépenses')
@section('page-title', 'Dépenses journalières')

@section('content')

    {{-- ===== KPIs ===== --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-wallet2"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Aujourd'hui</div>
                    <div class="stat-value" style="font-size:18px">
                        {{ number_format($totalJour, 0, ',', ' ') }}
                        <small style="font-size:11px;color:var(--text-muted)">FCFA</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-calendar-week"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Cette semaine</div>
                    <div class="stat-value" style="font-size:18px">
                        {{ number_format($totalSemaine, 0, ',', ' ') }}
                        <small style="font-size:11px;color:var(--text-muted)">FCFA</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-calendar-month"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Ce mois</div>
                    <div class="stat-value" style="font-size:18px">
                        {{ number_format($totalMois, 0, ',', ' ') }}
                        <small style="font-size:11px;color:var(--text-muted)">FCFA</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- ===== Colonne gauche : Formulaire saisie ===== --}}
        <div class="col-xl-4">
            <div class="card" style="position:sticky;top:calc(var(--topbar-h) + 16px)">
                <div class="card-header">
                    <span class="card-title"><i class="bi bi-plus-circle text-success me-2"></i>Nouvelle dépense</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.expenses.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Libellé <span class="text-danger">*</span></label>
                            <input type="text" name="libelle" class="form-control"
                                   placeholder="ex: Connexion internet, Salaire, Electricité…"
                                   value="{{ old('libelle') }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Catégorie
                                <span class="text-muted fw-normal">(optionnel)</span>
                            </label>
                            {{-- Datalist = champ libre avec suggestions des catégories existantes --}}
                            <input type="text" name="categorie" class="form-control"
                                   list="liste-categories"
                                   placeholder="ex: Charges, Personnel, Divers…"
                                   value="{{ old('categorie') }}">
                            <datalist id="liste-categories">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                                <option value="Connexion internet">
                                <option value="Électricité">
                                <option value="Loyer">
                                <option value="Personnel">
                                <option value="Transport">
                                <option value="Matières premières">
                                <option value="Emballages">
                                <option value="Entretien">
                                <option value="Divers">
                            </datalist>
                            <div class="form-text">Saisissez ou choisissez parmi tes catégories existantes</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Montant (FCFA) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="montant" class="form-control" placeholder="0"
                                       value="{{ old('montant') }}" required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date_depense" class="form-control"
                                   value="{{ old('date_depense', today()->toDateString()) }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Note <span class="text-muted fw-normal">(optionnel)</span></label>
                            <textarea name="note" class="form-control" rows="2"
                                      placeholder="Précisions supplémentaires…">{{ old('note') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-amira w-100">
                            <i class="bi bi-check-circle me-1"></i>Enregistrer la dépense
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===== Colonne droite : Historique ===== --}}
        <div class="col-xl-8">

            {{-- Filtres période --}}
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <input type="date" name="debut" class="form-control form-control-sm" value="{{ $debut }}">
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="fin" class="form-control form-control-sm" value="{{ $fin }}">
                        </div>
                        <div class="col-auto">
                            <select name="categorie" class="form-select form-select-sm">
                                <option value="">Toutes catégories</option>
                                @foreach($categories as $cat)
                                    <option
                                        value="{{ $cat }}" {{ request('categorie')===$cat ? 'selected':'' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex justify-content-center">
                            <button type="submit" class="btn btn-amira btn-sm">
                                <i class="bi bi-search me-1"></i>Filtrer
                            </button>
                            <a href="{{ route('admin.expenses') }}" class="btn btn-outline-secondary btn-sm ms-1">
                                <i class="bi bi-x-lg"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Total période --}}
            @if($depenses->isNotEmpty())
                <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <span style="font-size:13px;color:var(--text-muted)">
                {{ $depenses->count() }} dépense(s) •
                Du {{ \Carbon\Carbon::parse($debut)->locale('fr')->isoFormat('D MMM') }}
                au {{ \Carbon\Carbon::parse($fin)->locale('fr')->isoFormat('D MMM YYYY') }}
            </span>
                    <span style="font-size:15px;font-weight:700;color:var(--danger)">
                Total : {{ number_format($total, 0, ',', ' ') }} FCFA
            </span>
                </div>
            @endif

            {{-- Liste groupée par jour --}}
            @forelse($depenses->groupBy(fn($d) => $d->date_depense->format('Y-m-d')) as $date => $groupe)
                <div class="card mb-3">
                    <div class="card-header" style="background:var(--body-bg)">
                <span class="card-title">
                    <i class="bi bi-calendar3 me-1"></i>
                    {{ \Carbon\Carbon::parse($date)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                </span>
                        <span style="font-weight:700;color:var(--danger)">
                    {{ number_format($groupe->sum('montant'), 0, ',', ' ') }} FCFA
                </span>
                    </div>
                    <div class="card-body p-0">
                        @foreach($groupe as $d)
                            <div class="d-flex align-items-center justify-content-between px-4 py-3
                            {{ !$loop->last ? 'border-bottom' : '' }}"
                                 style="border-color:var(--border-color)!important">
                                <div class="d-flex align-items-center gap-3">
                                    {{-- Icône catégorie --}}
                                    <div style="width:36px;height:36px;border-radius:10px;
                                    background:var(--danger-light);color:var(--danger);
                                    display:flex;align-items:center;justify-content:center;
                                    font-size:16px;flex-shrink:0">
                                        <i class="bi bi-receipt"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight:600;font-size:13.5px">{{ $d->libelle }}</div>
                                        <div style="font-size:11px;color:var(--text-muted)">
                                            @if($d->categorie)
                                                <span
                                                    style="background:var(--border-color);padding:1px 7px;border-radius:10px">
                                        {{ $d->categorie }}
                                    </span>
                                            @endif
                                            @if($d->note)
                                                <span class="ms-1">• {{ $d->note }}</span>
                                            @endif
                                            <span class="ms-1">• {{ $d->user->name }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                        <span style="font-weight:700;font-size:15px;color:var(--danger);white-space:nowrap">
                            {{ number_format($d->montant, 0, ',', ' ') }} FCFA
                        </span>
                                    {{-- Actions --}}
                                    @can('admin')
                                        <button class="btn btn-sm btn-outline-secondary"
                                                data-id="{{ $d->id }}"
                                                data-libelle="{{ $d->libelle }}"
                                                data-categorie="{{ $d->categorie }}"
                                                data-montant="{{ $d->montant }}"
                                                data-date="{{ $d->date_depense->format('Y-m-d') }}"
                                                data-note="{{ $d->note }}"
                                                data-bs-toggle="modal" data-bs-target="#modalEditer"
                                                onclick="remplirEdit(this.dataset)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                                data-id="{{ $d->id }}"
                                                data-libelle="{{ $d->libelle }}"
                                                data-url="{{ route('admin.expenses.destroy', $d) }}"
                                                onclick="confirmerSuppression(this.dataset)"
                                                data-bs-toggle="modal" data-bs-target="#modalSupprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-wallet2 fs-2 d-block mb-2 opacity-40"></i>
                        <div style="font-size:14px;font-weight:600">Aucune dépense sur cette période</div>
                        <div style="font-size:13px;margin-top:4px">Utilisez le formulaire à gauche pour en ajouter
                            une.
                        </div>
                    </div>
                </div>
            @endforelse

        </div>
    </div>

    {{-- Modal Supprimer --}}
    <div class="modal fade" id="modalSupprimer" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center px-4 pb-4">
                    <div style="width:56px;height:56px;border-radius:50%;background:var(--danger-light);
                                color:var(--danger);display:flex;align-items:center;justify-content:center;
                                font-size:24px;margin:0 auto 16px">
                        <i class="bi bi-trash3"></i>
                    </div>
                    <h5 class="mb-2">Supprimer la dépense ?</h5>
                    <p class="text-muted mb-4" id="supprimer-libelle" style="font-size:13px"></p>
                    <form method="POST" id="formSupprimer" action="">
                        @csrf @method('DELETE')
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light flex-fill"
                                    data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger flex-fill">Supprimer</button>
                        </div>
                    </form>
                </div>
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
                        <h5 class="modal-title"><i class="bi bi-pencil text-warning me-2"></i>Modifier la dépense</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Libellé</label>
                            <input type="text" name="libelle" id="edit-libelle" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catégorie</label>
                            <input type="text" name="categorie" id="edit-categorie" class="form-control"
                                   list="liste-categories">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Montant (FCFA)</label>
                            <input type="number" name="montant" id="edit-montant" class="form-control" min="1" step="50"
                                   required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date_depense" id="edit-date" class="form-control" required>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Note</label>
                            <textarea name="note" id="edit-note" class="form-control" rows="2"></textarea>
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
        function remplirEdit(d) {
            const f = document.getElementById('formEditer');
            f.action = `/admin/expenses/${d.id}`;
            document.getElementById('edit-libelle').value = d.libelle;
            document.getElementById('edit-categorie').value = d.categorie ?? '';
            document.getElementById('edit-montant').value = d.montant;
            document.getElementById('edit-date').value = d.date;
            document.getElementById('edit-note').value = d.note ?? '';
        }

        function confirmerSuppression(d) {
            document.getElementById('formSupprimer').action = d.url;
            document.getElementById('supprimer-libelle').textContent =
                `« ${d.libelle} » sera définitivement supprimée. Cette action est irréversible.`;
        }

        // Soumission auto du formulaire de filtre période
        document.querySelectorAll('[name="periode"]').forEach(r => {
            r.addEventListener('change', () => r.closest('form').submit());
        });
    </script>
@endpush
