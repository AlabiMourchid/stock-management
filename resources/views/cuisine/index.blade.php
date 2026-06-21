@extends('layouts.app')

@section('title', 'Écran Cuisine')
@section('page-title', 'Écran de Production — Cuisine')

@section('content')

    {{-- Header avec rafraîchissement --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <div id="horloge" style="font-size:22px;font-weight:700;font-variant-numeric:tabular-nums"></div>
            <span class="badge bg-success" id="badge-connexion">
            <i class="bi bi-wifi me-1"></i>En ligne
        </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:13px;color:var(--text-muted)">Refresh auto :</span>
            <select id="refreshInterval" class="form-select form-select-sm" style="width:auto" onchange="setRefresh(this.value)">
                <option value="15000">15 sec</option>
                <option value="30000" selected>30 sec</option>
                <option value="60000">1 min</option>
                <option value="0">Manuel</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary" onclick="recharger()">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
            {{-- Signaler une perte --}}
            @can('cuisine-actions')
                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalPerte">
                    <i class="bi bi-exclamation-triangle me-1"></i>Signaler une perte
                </button>
            @endcan
        </div>
    </div>

    {{-- Colonnes Kanban --}}
    <div class="row g-3" id="cuisineBoard">

        {{-- EN ATTENTE --}}
        <div class="col-md-6 col-xl-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span style="width:10px;height:10px;border-radius:50%;background:var(--warning);display:inline-block"></span>
                <h6 class="mb-0 fw-700">En attente</h6>
                <span class="badge bg-warning text-dark" id="count-attente">
                {{ $commandes->where('statut','en_attente')->count() }}
            </span>
            </div>
            <div id="col-attente">
                @foreach($commandes->where('statut','en_attente') as $cmd)
                    @include('cuisine._card', ['cmd' => $cmd])
                @endforeach
                @if($commandes->where('statut','en_attente')->isEmpty())
                    <div class="text-center text-muted py-5" style="font-size:13px">
                        <i class="bi bi-hourglass fs-2 d-block mb-2 opacity-40"></i>Aucune commande en attente
                    </div>
                @endif
            </div>
        </div>

        {{-- EN PRÉPARATION --}}
        <div class="col-md-6 col-xl-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span style="width:10px;height:10px;border-radius:50%;background:var(--info);display:inline-block"></span>
                <h6 class="mb-0 fw-700">En préparation</h6>
                <span class="badge bg-info text-white" id="count-prep">
                {{ $commandes->where('statut','en_preparation')->count() }}
            </span>
            </div>
            <div id="col-prep">
                @foreach($commandes->where('statut','en_preparation') as $cmd)
                    @include('cuisine._card', ['cmd' => $cmd])
                @endforeach
                @if($commandes->where('statut','en_preparation')->isEmpty())
                    <div class="text-center text-muted py-5" style="font-size:13px">
                        <i class="bi bi-fire fs-2 d-block mb-2 opacity-40"></i>Aucune commande en cours
                    </div>
                @endif
            </div>
        </div>

        {{-- PRÊT --}}
        <div class="col-md-12 col-xl-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span style="width:10px;height:10px;border-radius:50%;background:var(--success);display:inline-block"></span>
                <h6 class="mb-0 fw-700">Prêt à servir</h6>
                <span class="badge bg-success" id="count-pret">0</span>
            </div>
            <div id="col-pret">
                <div class="text-center text-muted py-5" style="font-size:13px">
                    <i class="bi bi-check-circle fs-2 d-block mb-2 opacity-40"></i>Aucune commande prête
                </div>
            </div>
        </div>

    </div>

    {{-- ===== Modal Signaler Perte ===== --}}
    <div class="modal fade" id="modalPerte" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('cuisine.perte') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Signaler une perte</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Produit concerné</label>
                            <select name="produit_id" class="form-select" required>
                                <option value="">— Sélectionner —</option>
                                {{-- Charger tous les produits actifs --}}
                                @foreach(\App\Models\Produit::actif()->orderBy('nom')->get() as $p)
                                    <option value="{{ $p->id }}">{{ $p->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantité perdue</label>
                            <input type="number" name="quantite" class="form-control" min="0.1" step="0.1" required placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motif</label>
                            <select name="motif" class="form-select" required>
                                <option value="perime">Périmé</option>
                                <option value="brulee">Brûlé</option>
                                <option value="tombe">Tombé / abîmé</option>
                                <option value="mauvaise_cuisson">Mauvaise cuisson</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Description <span class="text-muted fw-normal">(optionnel)</span></label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Détails…"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger"><i class="bi bi-check2 me-1"></i>Signaler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Horloge
        function majHorloge() {
            const now = new Date();
            document.getElementById('horloge').textContent =
                now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        setInterval(majHorloge, 1000);
        majHorloge();

        // Rafraîchissement auto via AJAX
        let refreshTimer;

        function setRefresh(ms) {
            clearInterval(refreshTimer);
            if (parseInt(ms) > 0) {
                refreshTimer = setInterval(recharger, parseInt(ms));
            }
        }

        async function changerStatutCommande(cmdId, statut) {
            const btn = document.querySelector(`[data-cmd-id="${cmdId}"] .btn-statut-${statut}`);
            if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }

            try {
                const resp = await fetch(`/cuisine/${cmdId}/statut`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ statut }),
                });
                if (resp.ok) {
                    recharger();
                }
            } catch(e) {
                if (btn) { btn.disabled = false; }
            }
        }

        async function recharger() {
            try {
                const resp = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await resp.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                ['col-attente','col-prep','col-pret','count-attente','count-prep','count-pret'].forEach(id => {
                    const el = doc.getElementById(id);
                    if (el) document.getElementById(id).innerHTML = el.innerHTML;
                });
            } catch(e) {
                document.getElementById('badge-connexion').className = 'badge bg-danger';
                document.getElementById('badge-connexion').innerHTML = '<i class="bi bi-wifi-off me-1"></i>Hors ligne';
            }
        }

        setRefresh(30000); // Démarrer avec 30 sec
    </script>
@endpush
