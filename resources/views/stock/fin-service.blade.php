@extends('layouts.app')

@section('title', 'Saisie fin de service')
@section('page-title', 'Saisie fin de service')

@section('content')

    <div class="row g-4">

        {{-- ===== Colonne principale : formulaire ===== --}}
        <div class="col-xl-8">

            <div class="card">
                <div class="card-header">
                    <div>
                        <span class="card-title">Quantités consommées / enlevées</span>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                            <i class="bi bi-info-circle me-1"></i>
                            Saisissez uniquement les quantités <strong>sorties</strong> ce service.
                            Laisser 0 pour les produits non touchés.
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                    <span style="font-size:12px;color:var(--text-muted)">
                        {{ now()->locale('fr')->isoFormat('dddd D MMM') }}
                    </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('stock.fin-service.store') }}" id="formFinService">
                    @csrf



                    <div class="card-body p-0">
                        <table class="table table-amira mb-0" id="tableFinService">
                            <thead>
                            <tr>
                                <th style="width:40%">Produit</th>
                                <th class="text-center">Stk actuel</th>
                                <th class="text-center" style="width:160px">Qté enlevée</th>
                                <th class="text-center">Stk après</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($produits as $index => $p)
                                <tr class="produit-row" data-cat="{{ $p->nom }}">
                                    <td>
                                        <input type="hidden" name="lignes[{{ $index }}][produit_id]" value="{{ $p->id }}">
                                        <div class="d-flex align-items-center gap-2">
                                            <div>
                                                <div style="font-weight:600;font-size:13.5px">{{ $p->nom }}</div>
                                                <div style="font-size:11px;color:var(--text-muted)">
                                                     {{ $p->unite }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                    <span class="stock-badge stock-{{ $p->statut_stock }}"
                                          id="stock-actuel-{{ $p->id }}">
                                        {{ number_format($p->stock_actuel, 2, ',', '') }} {{ $p->unite }}
                                    </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="input-group input-group-sm justify-content-center" style="width:140px;margin:0 auto">
                                            <button type="button" class="btn btn-outline-secondary"
                                                    onclick="ajusterQte({{ $p->id }}, -1, {{ $p->stock_actuel }})">−</button>
                                            <input type="number"
                                                   name="lignes[{{ $index }}][quantite]"
                                                   id="qte-{{ $p->id }}"
                                                   class="form-control text-center qte-input"
                                                   data-stock="{{ $p->stock_actuel }}"
                                                   data-produit="{{ $p->id }}"
                                                   data-unite="{{ $p->unite }}"
                                                   min="0" step="0.5" value="0"
                                                   oninput="majStockApres({{ $p->id }}, {{ $p->stock_actuel }})">
                                            <button type="button" class="btn btn-outline-secondary"
                                                    onclick="ajusterQte({{ $p->id }}, 1, {{ $p->stock_actuel }})">+</button>
                                        </div>
                                    </td>
                                    <td class="text-center" id="stock-apres-{{ $p->id }}">
                                    <span style="font-weight:700;font-size:14px;color:var(--text-muted)">
                                        {{ number_format($p->stock_actuel, 2, ',', '') }}
                                    </span>
                                        <div style="font-size:11px;color:var(--text-muted)">{{ $p->unite }}</div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-body border-top d-flex align-items-center justify-content-between flex-wrap gap-3"
                         style="border-color:var(--border-color)!important">
                        <div>
                        <span id="compteurSorties" style="font-size:13px;color:var(--text-muted)">
                            0 produit(s) avec des sorties saisies
                        </span>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser
                            </button>
                            <button type="submit" class="btn btn-amira" id="btnSoumettre">
                                <i class="bi bi-check-circle me-1"></i>Enregistrer les sorties
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>{{-- /col gauche --}}

        {{-- ===== Colonne droite : récap & alertes ===== --}}
        <div class="col-xl-4">

            {{-- Récapitulatif des saisies en cours --}}
            <div class="card mb-3" style="position:sticky;top:calc(var(--topbar-h) + 16px)">
                <div class="card-header">
                    <span class="card-title"><i class="bi bi-clipboard-check me-1"></i>Récapitulatif</span>
                </div>
                <div class="card-body p-0" id="recapBody">
                    <div class="text-center py-4 text-muted" id="recapVide" style="font-size:13px">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-40"></i>
                        Aucune sortie saisie
                    </div>
                </div>

                {{-- Stocks critiques après saisie --}}
                <div id="alertesApres" class="d-none">
                    <div class="px-3 py-2 border-top" style="border-color:var(--border-color)!important">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--danger)">
                            <i class="bi bi-exclamation-triangle me-1"></i>Stocks critiques après saisie
                        </div>
                    </div>
                    <div id="listeAlertes" class="pb-2"></div>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('scripts')
    <script>
        // Seuils critiques pour calcul alerte en temps réel
        const seuils = {
            @foreach($produits as $p)
                {{ $p->id }}: { seuil: {{ $p->seuil_critique }}, nom: "{{ $p->nom }}", unite: "{{ $p->unite }}" },
            @endforeach
        };

        function majStockApres(id, stockActuel) {
            const input   = document.getElementById('qte-' + id);
            const qte     = parseFloat(input.value) || 0;
            const apres   = Math.max(0, stockActuel - qte);
            const cell    = document.getElementById('stock-apres-' + id);
            const seuil   = seuils[id]?.seuil ?? 0;

            let couleur = 'var(--text-primary)';
            if (apres <= seuil)        couleur = 'var(--danger)';
            else if (apres <= seuil*2) couleur = 'var(--warning)';

            cell.innerHTML = `<span style="font-weight:700;font-size:14px;color:${couleur}">${apres.toLocaleString('fr', {minimumFractionDigits:0, maximumFractionDigits:2})}</span>
                      <div style="font-size:11px;color:var(--text-muted)">${seuils[id]?.unite ?? ''}</div>`;

            // Highlight ligne si valeur > 0
            const row = input.closest('tr');
            row.style.background = qte > 0 ? 'rgba(244,98,31,.04)' : '';

            majRecap();
        }

        function ajusterQte(id, delta, stockActuel) {
            const input = document.getElementById('qte-' + id);
            const val   = parseFloat(input.value) || 0;
            const nv    = Math.max(0, Math.min(stockActuel, val + delta));
            input.value = nv;
            majStockApres(id, stockActuel);
        }

        function majRecap() {
            const inputs  = document.querySelectorAll('.qte-input');
            const recapEl = document.getElementById('recapBody');
            const videEl  = document.getElementById('recapVide');
            const alertesEl     = document.getElementById('alertesApres');
            const listeAlertesEl= document.getElementById('listeAlertes');
            const compteur      = document.getElementById('compteurSorties');

            let html     = '';
            let count    = 0;
            let htmlAl   = '';
            let hasAl    = false;

            inputs.forEach(input => {
                const qte  = parseFloat(input.value) || 0;
                const id   = input.dataset.produit;
                const stock= parseFloat(input.dataset.stock);
                const apres= Math.max(0, stock - qte);
                const seuil= seuils[id]?.seuil ?? 0;

                if (qte > 0) {
                    count++;
                    html += `
            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom"
                 style="border-color:var(--border-color)!important">
                <div style="font-size:13px">
                    <span style="font-weight:600;margin-left:4px">${seuils[id]?.nom ?? id}</span>
                </div>
                <div class="text-end">
                    <span style="font-size:13px;font-weight:700;color:var(--amira-orange)">−${qte}</span>
                    <div style="font-size:10px;color:var(--text-muted)">→ ${apres.toFixed(1)} restant</div>
                </div>
            </div>`;

                    if (apres <= seuil) {
                        hasAl = true;
                        htmlAl += `
                <div class="d-flex align-items-center gap-2 px-3 py-2">
                    <span style="font-size:16px">${seuils[id]?.emoji ?? ''}</span>
                    <div>
                        <div style="font-size:12px;font-weight:600">${seuils[id]?.nom}</div>
                        <div style="font-size:11px;color:var(--danger)">Stock → ${apres.toFixed(1)} ${seuils[id]?.unite} (critique)</div>
                    </div>
                </div>`;
                    }
                }
            });

            if (count === 0) {
                videEl.classList.remove('d-none');
                recapEl.querySelectorAll('.recap-line').forEach(e => e.remove());
            } else {
                videEl.classList.add('d-none');
                // Vider les anciennes lignes
                recapEl.querySelectorAll('.recap-line').forEach(e => e.remove());
                const div = document.createElement('div');
                div.className = 'recap-line';
                div.innerHTML = html;
                recapEl.appendChild(div);
            }

            compteur.textContent = `${count} produit(s) avec des sorties saisies`;

            alertesEl.classList.toggle('d-none', !hasAl);
            listeAlertesEl.innerHTML = htmlAl;
        }

        function resetForm() {
            showConfirm('Réinitialiser toutes les quantités saisies à zéro ?', function() {
                document.querySelectorAll('.qte-input').forEach(function(input) {
                    var id    = input.dataset.produit;
                    var stock = parseFloat(input.dataset.stock);
                    input.value = 0;
                    majStockApres(id, stock);
                });
            }, {type: 'warning', title: 'Réinitialiser le formulaire', confirmText: 'Réinitialiser'});
        }

        // Filtre catégories
        document.querySelectorAll('.cat-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.cat-btn').forEach(b => {
                    b.classList.remove('active', 'btn-amira');
                    b.classList.add('btn-outline-secondary');
                });
                btn.classList.add('active', 'btn-amira');
                btn.classList.remove('btn-outline-secondary');

                const cat = btn.dataset.cat;
                document.querySelectorAll('.produit-row').forEach(row => {
                    row.style.display = (cat === 'all' || row.dataset.cat === cat) ? '' : 'none';
                });
            });
        });
    </script>
@endpush
