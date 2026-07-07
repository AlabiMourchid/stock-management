@extends('layouts.app')
@section('title','Gestion des dépenses')
@section('page-title','Dépenses & Analyse financière')

@section('content')

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-wallet2"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Aujourd'hui</div>
                    <div class="stat-value fsz-19">{{ number_format($totalJour,0,',',' ') }}<small
                            class="fsz-11 c-muted"> F</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-calendar-week"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Cette semaine</div>
                    <div class="stat-value fsz-19">{{ number_format($totalSemaine,0,',',' ') }}<small
                            class="fsz-11 c-muted"> F</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-calendar-month"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Ce mois</div>
                    <div class="stat-value fsz-19">{{ number_format($totalMois,0,',',' ') }}<small
                            class="fsz-11 c-muted"> F</small></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-4">
            <div class="card card-sticky">
                <div class="card-header"><span class="card-title"><i class="bi bi-plus-circle text-success me-2"></i>Nouvelle dépense</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.expenses.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <div class="btn-group w-100 paiement-toggle">
                                <input type="radio" class="btn-check" name="type" id="t-fixe" value="fixe" checked>
                                <label class="btn btn-outline-secondary me-2" for="t-fixe"><i
                                        class="bi bi-pin-angle me-1"></i>Fixe</label>
                                <input type="radio" class="btn-check" name="type" id="t-variable" value="variable">
                                <label class="btn btn-outline-secondary" for="t-variable"><i
                                        class="bi bi-arrow-left-right me-1"></i>Variable</label>
                            </div>
                            <div class="form-text" id="typeHint">Charge récurrente et prévisible (loyer, salaires…)
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Libellé <span class="text-danger">*</span></label>
                            <input type="text" name="libelle" class="form-control"
                                   placeholder="ex: Loyer, Achat poulets…" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catégorie <span
                                    class="text-muted fw-normal">(optionnel)</span></label>
                            <input type="text" name="categorie" class="form-control" list="liste-cat"
                                   placeholder="Choisir ou taper…">
                            <datalist id="liste-cat">
                                @foreach(\App\Models\Depense::categoriesFixes() as $c)
                                    <option value="{{ $c }}">
                                @endforeach
                                @foreach(\App\Models\Depense::categoriesVariables() as $c)
                                    <option value="{{ $c }}">
                                @endforeach
                                @foreach($categories as $c)
                                    <option value="{{ $c }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Montant (FCFA) <span class="text-danger">*</span></label>
                            <div class="input-group"><input type="number" name="montant" class="form-control"
                                                            placeholder="0" required><span
                                    class="input-group-text">FCFA</span></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date_depense" class="form-control"
                                   value="{{ today()->toDateString() }}" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Note</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Précisions…"></textarea>
                        </div>
                        <button type="submit" class="btn btn-amira w-100"><i class="bi bi-check-circle me-1"></i>Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            {{-- Filtres --}}
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-auto"><input type="date" name="debut" class="form-control form-control-sm"
                                                     value="{{ $debut }}"></div>
                        <div class="col-auto"><input type="date" name="fin" class="form-control form-control-sm"
                                                     value="{{ $fin }}"></div>
                        <div class="col-auto">
                            <select name="type" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                <option value="fixe" {{ request('type')==='fixe'?'selected':'' }}>Fixes</option>
                                <option value="variable" {{ request('type')==='variable'?'selected':'' }}>Variables
                                </option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-amira btn-sm"><i class="bi bi-search me-1"></i>Filtrer
                            </button>
                            <a href="{{ route('admin.expenses') }}" class="btn btn-outline-secondary btn-sm ms-1"><i
                                    class="bi bi-x-lg me-1"></i>Annuler</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Analyse financière --}}
            <div class="card mb-3">
                <div class="card-header">
                    <span class="card-title"><i class="bi bi-graph-up me-2 text-success"></i>Analyse financière</span>
                    <span class="fsz-12 c-muted">{{ \Carbon\Carbon::parse($debut)->locale('fr')->isoFormat('D MMM') }} → {{ \Carbon\Carbon::parse($fin)->locale('fr')->isoFormat('D MMM YYYY') }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4 text-center">
                        <div class="col-6 col-md-3">
                            <div class="finance-kpi-label">
                                CA
                            </div>
                            <div class="finance-kpi-value c-brand">{{ number_format($caPeriode,0,',',' ') }}</div>
                            <div class="finance-kpi-unit">FCFA</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="finance-kpi-label">
                                Charges fixes
                            </div>
                            <div class="finance-kpi-value c-info">{{ number_format($totalFixe,0,',',' ') }}</div>
                            <div class="finance-kpi-unit">FCFA</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="finance-kpi-label">
                                Charges variables
                            </div>
                            <div class="finance-kpi-value c-warning">{{ number_format($totalVariable,0,',',' ') }}</div>
                            <div class="finance-kpi-unit">FCFA</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="finance-kpi-label">
                                Marge nette
                            </div>
                            <div class="finance-kpi-value {{ $margeNette>=0?'c-success':'c-danger' }}">{{ $margeNette>=0?'+':'' }}{{ number_format($margeNette,0,',',' ') }}</div>
                            <div class="finance-kpi-unit {{ $margeNette>=0?'c-success':'c-danger' }}">{{ $tauxMargeNette }}
                                % du CA
                            </div>
                        </div>
                    </div>

                    @php
                        $total = max($caPeriode,1);
                        $pctF  = min(100,round($totalFixe/$total*100));
                        $pctV  = min(100-$pctF,round($totalVariable/$total*100));
                        $pctM  = max(0,100-$pctF-$pctV);
                    @endphp
                    <div class="progress progress-lg mb-2">
                        <div class="progress-bar bg-var-info" style="width:{{ $pctF }}%"
                             title="Fixes {{ $pctF }}%"></div>
                        <div class="progress-bar bg-var-warning" style="width:{{ $pctV }}%"
                             title="Variables {{ $pctV }}%"></div>
                        <div class="progress-bar {{ $margeNette>=0?'bg-var-success':'bg-var-danger' }}"
                             style="width:{{ $pctM }}%"
                             title="Marge {{ $pctM }}%"></div>
                    </div>
                    <div class="d-flex gap-3 mb-4 fsz-11">
                        <span><span class="legend-dot bg-var-info"></span>Fixes ({{ $pctF }}%)</span>
                        <span><span class="legend-dot bg-var-warning"></span>Variables ({{ $pctV }}%)</span>
                        <span><span class="legend-dot {{ $margeNette>=0?'bg-var-success':'bg-var-danger' }}"></span>Marge ({{ $pctM }}%)</span>
                    </div>

                    {{-- Seuil rentabilité --}}
                    <div class="p-3 rounded threshold-box {{ $seuilAtteint?'is-positive':'is-negative' }}">
                        <div class="d-flex align-items-start gap-3">
                            <div class="fsz-26">{{ $seuilAtteint?'✅':'⚠️' }}</div>
                            <div class="flex-grow-1">
                                <div class="fw-bold fsz-14 {{ $seuilAtteint?'c-success':'c-danger' }}">
                                    Seuil de rentabilité : {{ number_format($seuilRentabilite,0,',',' ') }} FCFA
                                </div>
                                @if($seuilRentabilite > 0)
                                    <div class="fsz-13 mt-1 c-secondary">
                                        @if($seuilAtteint)
                                            ✓ Seuil dépassé de <strong class="c-success">{{ number_format($caPeriode-$seuilRentabilite,0,',',' ') }}
                                                FCFA</strong>
                                        @else
                                            Il manque <strong class="c-danger">{{ number_format($seuilRentabilite-$caPeriode,0,',',' ') }}
                                                FCFA</strong> de CA
                                        @endif
                                    </div>
                                    @php $pctSeuil = $seuilRentabilite>0 ? min(100,round($caPeriode/$seuilRentabilite*100)) : 100; @endphp
                                    <div class="progress progress-xs mt-2">
                                        <div class="progress-bar {{ $seuilAtteint?'bg-var-success':'bg-var-danger' }}"
                                             style="width:{{ $pctSeuil }}%"></div>
                                    </div>
                                    <div class="fsz-11 c-muted mt-1">{{ $pctSeuil }}%
                                        du seuil atteint
                                    </div>
                                @else
                                    <div class="fsz-12 c-muted mt-1">Saisissez des
                                        charges fixes pour calculer le seuil
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Historique groupé par jour --}}
            @forelse($expenses->groupBy(fn($d)=>$d->date_depense->format('Y-m-d')) as $date => $groupe)
                <div class="card mb-3">
                    <div class="card-header card-header-subtle">
                        <span
                            class="card-title">{{ \Carbon\Carbon::parse($date)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</span>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="day-total-chip chip-fixe">F:{{ number_format($groupe->where('type','fixe')->sum('montant'),0,',',' ') }}</span>
                            <span class="day-total-chip chip-variable">V:{{ number_format($groupe->where('type','variable')->sum('montant'),0,',',' ') }}</span>
                            <span class="fw-bold c-danger">= {{ number_format($groupe->sum('montant'),0,',',' ') }} F</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @foreach($groupe as $d)
                            <div
                                class="d-flex align-items-center justify-content-between px-4 py-3 {{ !$loop->last?'border-bottom':'' }} b-subtle">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="type-icon {{ $d->type==='fixe'?'type-fixe':'type-variable' }}">
                                        <i class="bi bi-{{ $d->type==='fixe'?'pin-angle':'arrow-left-right' }} fsz-14"></i>
                                    </div>
                                    <div>
                                        <div class="fw-600 fsz-13">{{ $d->libelle }}</div>
                                        <div class="text-meta">
                                            <span class="type-chip {{ $d->type==='fixe'?'type-fixe':'type-variable' }}">{{ $d->type==='fixe'?'Fixe':'Variable' }}</span>
                                            @if($d->categorie)
                                                <span class="ms-1 category-chip">{{ $d->categorie }}</span>
                                            @endif
                                            @if($d->note)
                                                <span class="ms-1">• {{ $d->note }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="expense-amount">{{ number_format($d->montant,0,',',' ') }} F</span>
                                    <button class="btn btn-sm btn-outline-secondary btn-edit-dep"
                                            data-url="{{ route('admin.expenses.update', $d) }}"
                                            data-type="{{ $d->type }}" data-libelle="{{ $d->libelle }}"
                                            data-categorie="{{ $d->categorie }}" data-montant="{{ $d->montant }}"
                                            data-date="{{ $d->date_depense->format('Y-m-d') }}"
                                            data-note="{{ $d->note }}"
                                            data-bs-toggle="modal" data-bs-target="#modalEditer"><i
                                            class="bi bi-pencil"></i></button>
                                    <form method="POST" action="{{ route('admin.expenses.destroy',$d) }}"
                                          class="d-inline" onsubmit="return confirm('Supprimer ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-wallet2 fs-2 d-block mb-2 opacity-40"></i>
                        <div class="fsz-14 fw-600">Aucune dépense sur cette période</div>
                    </div>
                </div>
            @endforelse
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
                            <label class="form-label">Type</label>
                            <div class="btn-group w-100 paiement-toggle">
                                <input type="radio" class="btn-check" name="type" id="et-fixe" value="fixe"><label
                                    class="btn btn-outline-secondary me-2" for="et-fixe">Fixe</label>
                                <input type="radio" class="btn-check" name="type" id="et-variable"
                                       value="variable"><label class="btn btn-outline-secondary" for="et-variable">Variable</label>
                            </div>
                        </div>
                        <div class="mb-3"><label class="form-label">Libellé</label><input type="text" name="libelle"
                                                                                          id="e-libelle"
                                                                                          class="form-control" required>
                        </div>
                        <div class="mb-3"><label class="form-label">Catégorie</label><input type="text" name="categorie"
                                                                                            id="e-categorie"
                                                                                            class="form-control"
                                                                                            list="liste-cat"></div>
                        <div class="mb-3"><label class="form-label">Montant (FCFA)</label><input type="number"
                                                                                                 name="montant"
                                                                                                 id="e-montant"
                                                                                                 class="form-control"
                                                                                                 required></div>
                        <div class="mb-3"><label class="form-label">Date</label><input type="date" name="date_depense"
                                                                                       id="e-date" class="form-control"
                                                                                       required></div>
                        <div class="mb-1"><label class="form-label">Note</label><textarea name="note" id="e-note"
                                                                                          class="form-control"
                                                                                          rows="2"></textarea></div>
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
        const hints = {
            fixe: "Charge récurrente (loyer, salaires, internet…)",
            variable: "Charge liée à l'activité (matières premières, emballages…)"
        };
        document.querySelectorAll('[name="type"]').forEach(r => r.addEventListener('change', () => {
            const h = document.getElementById('typeHint');
            if (h) h.textContent = hints[r.value] ?? '';
        }));
        document.querySelectorAll('.btn-edit-dep').forEach(btn => btn.addEventListener('click', () => {
            const d = btn.dataset;
            document.getElementById('formEditer').action = d.url;
            document.getElementById('e-libelle').value = d.libelle;
            document.getElementById('e-categorie').value = d.categorie ?? '';
            document.getElementById('e-montant').value = d.montant;
            document.getElementById('e-date').value = d.date;
            document.getElementById('e-note').value = d.note ?? '';
            document.getElementById(d.type === 'fixe' ? 'et-fixe' : 'et-variable').checked = true;
        }));
        document.querySelectorAll('[name="periode"]').forEach(r => r.addEventListener('change', () => r.closest('form').submit()));
    </script>
@endpush
