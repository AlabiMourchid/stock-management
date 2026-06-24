@extends('layouts.app')

@section('title', 'Prise de commande')
@section('page-title', 'Commande')

@section('content')

    <div class="row g-3" style="min-height:calc(100vh - 120px)">

        {{-- ===== Colonne gauche : Grille produits ===== --}}
        <div class="col-lg-8 pos-col-produits">
            {{-- Filtres catégories --}}
            <div class="d-flex gap-2 flex-wrap mb-3">
                <button class="btn btn-sm btn-amira cat-btn active" data-cat="all">Tout</button>
                @foreach($categories as $cat)
                    <button class="btn btn-sm btn-outline-secondary cat-btn" data-cat="{{ $cat->id }}">
                        {{ $cat->nom }}
                    </button>
                @endforeach
            </div>

            {{-- Grille produits --}}
            <div class="pos-grid" id="posGrid">
                @foreach($produits as $p)
                    <div class="pos-item" data-id="{{ $p->id }}" data-cat="{{ $p->categorie_id }}"
                         data-nom="{{ $p->nom }}" data-prix="{{ $p->prix_vente }}"
                         onclick="ajouterAuPanier(this)">
                        <div class="pos-item-name">{{ $p->nom }}</div>
                        <div class="pos-item-price">{{ number_format($p->prix_vente, 0, ',', ' ') }} F</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===== Colonne droite : Ticket de commande ===== --}}
        <div class="col-lg-4 pos-col-ticket" id="posTicketCol">
            <div class="order-ticket" style="position:sticky;top:calc(var(--topbar-h) + 16px)">

            {{-- Poignée drawer (mobile uniquement) --}}
            <div class="drawer-handle" onclick="fermerDrawer()">
                <div class="drawer-handle-bar"></div>
            </div>
                <div class="order-ticket-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-cart3 me-2"></i>Commande en cours</span>
                    <button class="btn btn-sm btn-outline-danger no-print" onclick="viderPanier()">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>

                <div class="order-ticket-body" id="panierBody">
                    <div class="text-center text-muted py-4" id="panierVide">
                        <i class="bi bi-cart-x fs-2 d-block mb-2 opacity-50"></i>
                        Aucun article
                    </div>
                </div>

                <div class="order-ticket-footer">
                    {{-- Sous-total articles --}}
                    <div class="order-total" id="ligneArticles" style="display:none;font-size:13px;opacity:.75">
                        <span>Articles</span>
                        <span id="sousTotal">0 FCFA</span>
                    </div>

                    {{-- Supplément optionnel --}}
                    <div class="mb-2">
                        <label class="form-label mb-1" style="font-size:12px">Supplément (optionnel)</label>
                        <input type="number" id="supplement" class="form-control form-control-sm"
                               placeholder="0" min="0" step="100" oninput="renderPanier()">
                    </div>

                    {{-- Total final --}}
                    <div class="order-total">
                        <span>Total</span>
                        <span id="totalAffiche">0 FCFA</span>
                    </div>

                    {{-- Mode paiement --}}
                    <div class="mb-3">
                        <label class="form-label mb-1">Mode de paiement</label>
                        <div class="btn-group w-100 paiement-toggle" role="group">
                            <input type="radio" class="btn-check" name="mode_paiement" id="pm-especes" value="especes" checked>
                            <label class="btn btn-outline-secondary" for="pm-especes"><i class="bi bi-cash me-1"></i>Espèces</label>

                            <input type="radio" class="btn-check" name="mode_paiement" id="pm-mobile" value="mobile_money">
                            <label class="btn btn-outline-secondary" for="pm-mobile"><i class="bi bi-phone me-1"></i>Mobile</label>

                        </div>
                    </div>

                    {{-- Montant reçu (espèces) --}}
                    <div class="mb-3" id="zoneEspeces">
                        <label class="form-label mb-1">Montant reçu (FCFA)</label>
                        <input type="number" id="montantRecu" class="form-control" placeholder="0" min="0" step="500"
                               oninput="calculerMonnaie()">
                        <div id="monnaieAffiche" class="mt-1" style="font-size:13px;color:var(--success);font-weight:600"></div>
                    </div>

                    {{-- Type commande --}}
                    <div class="mb-3">
                        <div class="btn-group w-100 paiement-toggle" role="group">
                            <input type="radio" class="btn-check" name="type_cmd" id="type-place" value="sur_place" checked>
                            <label class="btn btn-outline-secondary" for="type-place"><i class="bi bi-shop me-1"></i>Sur place</label>
                            <input type="radio" class="btn-check" name="type_cmd" id="type-emporter" value="emporter">
                            <label class="btn btn-outline-secondary" for="type-emporter"><i class="bi bi-bag me-1"></i>À emporter</label>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <textarea id="notesCommande" class="form-control" rows="2" placeholder="Notes (optionnel)…" style="font-size:13px"></textarea>
                    </div>

                    <button class="btn btn-amira w-100 btn-lg" onclick="validerCommande()" id="btnValider" disabled>
                        <i class="bi bi-check-circle me-2"></i>Valider la commande
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Spinner overlay --}}
    <div id="spinnerOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background:rgba(0,0,0,.4);z-index:2000">
        <div class="text-white text-center">
            <div class="spinner-border mb-2"></div>
            <div>Enregistrement…</div>
        </div>
    </div>

    {{-- Overlay drawer panier (mobile) --}}
    <div class="pos-drawer-overlay" id="posDrawerOverlay" onclick="fermerDrawer()"></div>

    {{-- FAB Panier (mobile uniquement) --}}
    <button class="pos-fab" id="posFab" onclick="ouvrirDrawer()" aria-label="Ouvrir le panier">
        <i class="bi bi-cart3 fs-6"></i>
        <span id="fabCount">0</span>
        <span class="pos-fab-badge" id="fabTotal">0 F</span>
    </button>

@endsection

@push('scripts')
    <script>
        let panier = {};

        // ---- Drawer mobile ----
        function ouvrirDrawer() {
            document.getElementById('posTicketCol').classList.add('drawer-open');
            document.getElementById('posDrawerOverlay').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function fermerDrawer() {
            document.getElementById('posTicketCol').classList.remove('drawer-open');
            document.getElementById('posDrawerOverlay').classList.remove('show');
            document.body.style.overflow = '';
        }
        function updateFab() {
            const keys = Object.keys(panier);
            const totalQte = keys.reduce((s, id) => s + panier[id].qte, 0);
            const totalMt  = keys.reduce((s, id) => s + panier[id].prix * panier[id].qte, 0);
            document.getElementById('fabCount').textContent = totalQte;
            document.getElementById('fabTotal').textContent = totalQte > 0 ? totalMt.toLocaleString('fr') + ' F' : '0 F';
        }

        // Filtre catégories
        document.querySelectorAll('.cat-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active','btn-amira'));
                document.querySelectorAll('.cat-btn').forEach(b => b.classList.add('btn-outline-secondary'));
                btn.classList.add('active','btn-amira');
                btn.classList.remove('btn-outline-secondary');

                const cat = btn.dataset.cat;
                document.querySelectorAll('.pos-item').forEach(item => {
                    item.style.display = (cat === 'all' || item.dataset.cat === cat) ? '' : 'none';
                });
            });
        });

        // Cacher la zone espèces si paiement non-espèces
        document.querySelectorAll('[name="mode_paiement"]').forEach(r => {
            r.addEventListener('change', () => {
                document.getElementById('zoneEspeces').style.display =
                    document.querySelector('[name="mode_paiement"]:checked').value === 'especes' ? '' : 'none';
            });
        });

        function ajouterAuPanier(el) {
            const id    = el.dataset.id;
            const nom   = el.dataset.nom;
            const prix  = parseFloat(el.dataset.prix);

            if (panier[id]) {
                panier[id].qte++;
            } else {
                panier[id] = { id, nom, prix, qte: 1 };
            }
            renderPanier();
        }

        function changerQte(id, delta) {
            if (!panier[id]) return;
            panier[id].qte += delta;
            if (panier[id].qte <= 0) delete panier[id];
            renderPanier();
        }

        function retirerLigne(id) {
            delete panier[id];
            renderPanier();
        }

        function viderPanier() {
            panier = {};
            document.getElementById('montantRecu').value = '';
            document.getElementById('supplement').value  = '';
            renderPanier();
        }

        function renderPanier() {
            const body = document.getElementById('panierBody');
            const keys = Object.keys(panier);

            if (keys.length === 0) {
                body.innerHTML = `<div class="text-center text-muted py-4" id="panierVide">
            <i class="bi bi-cart-x fs-2 d-block mb-2 opacity-50"></i>Aucun article</div>`;
                document.getElementById('totalAffiche').textContent = '0 FCFA';
                document.getElementById('sousTotal').textContent    = '0 FCFA';
                document.getElementById('ligneArticles').style.display = 'none';
                document.getElementById('btnValider').disabled = true;
                document.getElementById('monnaieAffiche').textContent = '';
                updateFab();
                return;
            }

            let html = '';
            let sousTot = 0;
            keys.forEach(id => {
                const item = panier[id];
                const ss   = item.prix * item.qte;
                sousTot += ss;
                html += `<div class="order-line">
            <span class="order-line-qty">${item.qte}</span>
            <span class="order-line-name">${item.nom}</span>
            <span class="order-line-price">${ss.toLocaleString('fr')} F</span>
            <div class="d-flex align-items-center gap-1 ms-1">
                <button class="btn-sm btn border-0 p-0 px-1" onclick="changerQte('${id}',-1)" style="line-height:1">−</button>
                <button class="btn-sm btn border-0 p-0 px-1" onclick="changerQte('${id}',1)"  style="line-height:1">+</button>
                <button class="order-line-del" onclick="retirerLigne('${id}')"><i class="bi bi-x"></i></button>
            </div>
        </div>`;
            });

            const supplement = parseFloat(document.getElementById('supplement').value) || 0;
            const total      = sousTot + supplement;

            body.innerHTML = html;
            document.getElementById('sousTotal').textContent    = sousTot.toLocaleString('fr') + ' FCFA';
            document.getElementById('ligneArticles').style.display = supplement > 0 ? '' : 'none';
            document.getElementById('totalAffiche').textContent = total.toLocaleString('fr') + ' FCFA';
            document.getElementById('btnValider').disabled = false;
            calculerMonnaie(total);
            updateFab();
        }

        function calculerMonnaie(total) {
            if (total === undefined) {
                const supplement = parseFloat(document.getElementById('supplement').value) || 0;
                const sousTot    = Object.values(panier).reduce((s, i) => s + i.prix * i.qte, 0);
                total = sousTot + supplement;
            }
            const recu    = parseFloat(document.getElementById('montantRecu').value) || 0;
            const monnaie = recu - total;
            const el      = document.getElementById('monnaieAffiche');
            if (recu > 0) {
                el.textContent = monnaie >= 0
                    ? `✓ Monnaie à rendre : ${monnaie.toLocaleString('fr')} FCFA`
                    : `⚠ Manque : ${Math.abs(monnaie).toLocaleString('fr')} FCFA`;
                el.style.color = monnaie >= 0 ? 'var(--success)' : 'var(--danger)';
            } else {
                el.textContent = '';
            }
        }

        async function validerCommande() {
            const lignes = Object.values(panier).map(item => ({
                menu_id:  item.id,
                quantite: item.qte,
            }));
            if (!lignes.length) return;

            const modePaiement = document.querySelector('[name="mode_paiement"]:checked').value;
            const type         = document.querySelector('[name="type_cmd"]:checked').value;
            const montantRecu  = parseFloat(document.getElementById('montantRecu').value) || 0;
            const supplement   = parseFloat(document.getElementById('supplement').value)  || 0;
            const notes        = document.getElementById('notesCommande').value;

            document.getElementById('spinnerOverlay').classList.remove('d-none');

            try {
                const resp = await fetch('{{ route("ventes.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ lignes, mode_paiement: modePaiement, type, montant_recu: montantRecu, supplement, notes }),
                });

                const data = await resp.json();
                if (data.success) {
                    viderPanier();
                    fermerDrawer();
                    // Ouvrir reçu dans un nouvel onglet
                    //window.open(data.recu_url, '_blank');
                } else {
                    showAlert("Erreur lors de l'enregistrement de la commande.", 'danger');
                }
            } catch (e) {
                showAlert('Erreur réseau. Vérifiez votre connexion et réessayez.', 'danger');
            } finally {
                document.getElementById('spinnerOverlay').classList.add('d-none');
            }
        }
    </script>
@endpush
