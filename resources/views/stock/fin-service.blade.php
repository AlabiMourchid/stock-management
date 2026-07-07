@extends('layouts.app')
@section('title','Saisie fin de service')
@section('page-title','Saisie fin de service — Sorties stock')

@section('content')

    @php $estAujourdhui = $dateService === today()->toDateString(); @endphp

    {{-- ===== Sélecteur de date de service ===== --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('stock.fin-service') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1" style="font-size:12px">Service à saisir</label>
                    <input type="date" name="date_service" class="form-control form-control-sm"
                           value="{{ $dateService }}" max="{{ today()->toDateString() }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-amira btn-sm"><i class="bi bi-search me-1"></i>Charger</button>
                </div>
                @if(!$estAujourdhui)
                    <div class="col-auto">
                        <a href="{{ route('stock.fin-service') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Revenir à aujourd'hui
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    @unless($estAujourdhui)
        <div class="alert alert-warning d-flex align-items-start gap-2 mb-4" style="font-size:13px">
            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
            <div>
                Vous saisissez un <strong>service antérieur</strong> ({{ \Carbon\Carbon::parse($dateService)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}).
                Les quantités seront retirées du <strong>stock actuel réel</strong> affiché ci-dessous, pensez à ne saisir que ce qui n'a pas déjà été enregistré, mais le mouvement sera bien daté du {{ \Carbon\Carbon::parse($dateService)->locale('fr')->isoFormat('D MMM') }}.
            </div>
        </div>
    @endunless

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <div>
                        <span class="card-title">Quantités consommées / enlevées</span>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                            <i class="bi bi-info-circle me-1"></i>Saisissez uniquement les quantités
                            <strong>sorties</strong> ce service. Laisser 0 pour les produits non touchés.
                        </div>
                    </div>
                    <span style="font-size:12px;color:var(--text-muted)">
                        {{ \Carbon\Carbon::parse($dateService)->locale('fr')->isoFormat('dddd D MMM') }}
                        @unless($estAujourdhui)
                            <span class="badge" style="background:var(--warning-light);color:var(--warning);font-size:11px;font-weight:600;margin-left:4px">Antérieur</span>
                        @endunless
                    </span>
                </div>
                <form method="POST" action="{{ route('stock.fin-service.store') }}" id="formFinService">
                    @csrf
                    <input type="hidden" name="date_service" value="{{ $dateService }}">
                    <div class="card-body p-0">
                        <table class="table table-amira mb-0">
                            <thead>
                            <tr>
                                <th>Matière première</th>
                                <th class="text-center">Stock actuel</th>
                                <th class="text-center" style="width:160px">Qté enlevée</th>
                                <th class="text-center">Stock après</th>
                                <th>Note</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($produits as $i => $p)
                                <tr class="produit-row" data-cat="{{ $p->categorie_nom ?? 'Autre' }}">
                                    <td>
                                        <input type="hidden" name="lignes[{{ $i }}][produit_id]"
                                               value="{{ $p->id }}">
                                        <div class="d-flex align-items-center gap-2">
                                            <span style="font-size:20px">{{ $p->emoji }}</span>
                                            <div>
                                                <div style="font-weight:600;font-size:13px">{{ $p->nom }}</div>
                                                <div
                                                    style="font-size:11px;color:var(--text-muted)">{{ $p->unite }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="stock-badge stock-{{ $p->statut_stock }}">{{ number_format($p->stock_actuel,2,',','') }} {{ $p->unite }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="input-group input-group-sm justify-content-center"
                                             style="width:140px;margin:0 auto">
                                            <button type="button" class="btn btn-outline-secondary"
                                                    onclick="ajusterQte({{ $p->id }},-1,{{ $p->stock_actuel }})">−
                                            </button>
                                            <input type="number" name="lignes[{{ $i }}][quantite]" id="qte-{{ $p->id }}"
                                                   class="form-control text-center qte-input"
                                                   data-stock="{{ $p->stock_actuel }}" data-produit="{{ $p->id }}"
                                                   data-unite="{{ $p->unite }}"
                                                   min="0" step="0.5" value="0"
                                                   oninput="majStockApres({{ $p->id }},{{ $p->stock_actuel }})">
                                            <button type="button" class="btn btn-outline-secondary"
                                                    onclick="ajusterQte({{ $p->id }},1,{{ $p->stock_actuel }})">+
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-center" id="stock-apres-{{ $p->id }}">
                                        <span
                                            style="font-weight:700;font-size:14px;color:var(--text-muted)">{{ number_format($p->stock_actuel,2,',','') }}</span>
                                        <div style="font-size:11px;color:var(--text-muted)">{{ $p->unite }}</div>
                                    </td>
                                    <td><input type="text" name="lignes[{{ $i }}][motif]"
                                               class="form-control form-control-sm"
                                               style="min-width:120px"></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-body border-top d-flex align-items-center justify-content-between flex-wrap gap-3"
                         style="border-color:var(--border-color)!important">
                        <span id="compteurSorties" style="font-size:13px;color:var(--text-muted)">0 produit(s) avec sorties</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()"><i
                                    class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser
                            </button>
                            <button type="submit" class="btn btn-amira"><i class="bi bi-check-circle me-1"></i>Enregistrer
                                les sorties
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-3" style="position:sticky;top:calc(var(--topbar-h)+ 16px)">
                <div class="card-header"><span class="card-title">Récapitulatif</span></div>
                <div class="card-body p-0" id="recapBody">
                    <div class="text-center py-4 text-muted" id="recapVide" style="font-size:13px">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-40"></i>Aucune sortie saisie
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const seuils = {
            @foreach($produits as $p) {{ $p->id }}: {
                seuil: {{ $p->seuil_critique }},
                nom: "{{ addslashes($p->nom) }}",
                emoji: "{{ $p->emoji }}",
                unite: "{{ $p->unite }}"
            }, @endforeach };

        function majStockApres(id, stockActuel) {
            const qte = parseFloat(document.getElementById('qte-' + id).value) || 0;
            const apres = Math.max(0, stockActuel - qte);
            const seuil = seuils[id]?.seuil ?? 0;
            let col = apres <= seuil ? 'var(--danger)' : apres <= seuil * 2 ? 'var(--warning)' : 'var(--text-primary)';
            document.getElementById('stock-apres-' + id).innerHTML = `<span style="font-weight:700;font-size:14px;color:${col}">${apres.toFixed(2)}</span><div style="font-size:11px;color:var(--text-muted)">${seuils[id]?.unite ?? ''}</div>`;
            document.getElementById('qte-' + id).closest('tr').style.background = qte > 0 ? 'rgba(244,98,31,.04)' : '';
            majRecap();
        }

        function ajusterQte(id, delta, stockActuel) {
            const el = document.getElementById('qte-' + id);
            el.value = Math.max(0, Math.min(stockActuel, (parseFloat(el.value) || 0) + delta));
            majStockApres(id, stockActuel);
        }

        function majRecap() {
            let html = '', count = 0;
            document.querySelectorAll('.qte-input').forEach(input => {
                const qte = parseFloat(input.value) || 0;
                if (qte > 0) {
                    count++;
                    const id = input.dataset.produit;
                    const stock = parseFloat(input.dataset.stock);
                    html += `<div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="border-color:var(--border-color)!important"><div style="font-size:13px"><span style="font-size:16px">${seuils[id]?.emoji ?? ''}</span><span style="font-weight:600;margin-left:4px">${seuils[id]?.nom ?? id}</span></div><div class="text-end"><span style="font-size:13px;font-weight:700;color:var(--amira-orange)">−${qte}</span><div style="font-size:10px;color:var(--text-muted)">→ ${Math.max(0, stock - qte).toFixed(1)} restant</div></div></div>`;
                }
            });
            const vide = document.getElementById('recapVide');
            const body = document.getElementById('recapBody');
            vide.classList.toggle('d-none', count > 0);
            let old = body.querySelector('.recap-lines');
            if (old) old.remove();
            if (count > 0) {
                const d = document.createElement('div');
                d.className = 'recap-lines';
                d.innerHTML = html;
                body.appendChild(d);
            }
            document.getElementById('compteurSorties').textContent = `${count} produit(s) avec sorties`;
        }

        function resetForm() {
            if (!confirm('Réinitialiser ?')) return;
            document.querySelectorAll('.qte-input').forEach(el => {
                el.value = 0;
                majStockApres(el.dataset.produit, el.dataset.stock);
            });
        }

        document.querySelectorAll('.cat-btn').forEach(btn => btn.addEventListener('click', () => {
            document.querySelectorAll('.cat-btn').forEach(b => {
                b.classList.remove('active', 'btn-amira');
                b.classList.add('btn-outline-secondary');
            });
            btn.classList.add('active', 'btn-amira');
            btn.classList.remove('btn-outline-secondary');
            const cat = btn.dataset.cat;
            document.querySelectorAll('.produit-row').forEach(r => r.style.display = (cat === 'all' || r.dataset.cat === cat) ? '' : 'none');
        }));
    </script>
@endpush
