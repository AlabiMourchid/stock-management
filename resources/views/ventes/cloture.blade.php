@extends('layouts.app')

@section('title', 'Clôture de caisse')
@section('page-title', 'Clôture de caisse')

@section('content')

    @php
        $date       = \Carbon\Carbon::parse($dateService)->locale('fr')->isoFormat('dddd D MMMM YYYY');
        $estAujourdhui = $dateService === today()->toDateString();
        $estCloture = $session?->est_cloturee ?? false;

        // Calculs en temps réel depuis les commandes du service sélectionné
        $caEspeces  = $commandes->where('mode_paiement', 'especes')->sum('total_ttc');
        $caMobile   = $commandes->where('mode_paiement', 'mobile_money')->sum('total_ttc');
        $caTotal    = $caEspeces + $caMobile;
        $nbCmds     = $commandes->count();
        $panier     = $nbCmds > 0 ? $caTotal / $nbCmds : 0;

        $repartition = [
            ['label' => 'Espèces',      'val' => $caEspeces, 'pct' => $caTotal > 0 ? round($caEspeces / $caTotal * 100) : 0, 'bar' => 'bg-var-success'],
            ['label' => 'Mobile Money', 'val' => $caMobile,  'pct' => $caTotal > 0 ? round($caMobile  / $caTotal * 100) : 0, 'bar' => 'bg-var-info'],
        ];
    @endphp

    {{-- ===== Sélecteur de date de service ===== --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('caisse.cloture') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1 fsz-12">Service à clôturer</label>
                    <input type="date" name="date_service" class="form-control form-control-sm"
                           value="{{ $dateService }}" max="{{ today()->toDateString() }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-amira btn-sm"><i class="bi bi-search me-1"></i>Charger</button>
                </div>
                @if(!$estAujourdhui)
                    <div class="col-auto">
                        <a href="{{ route('caisse.cloture') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Revenir à aujourd'hui
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- ===== Bandeau si déjà clôturé ===== --}}
    @if($estCloture)
        <div class="alert alert-success d-flex align-items-center gap-3 mb-4 rounded-lg">
            <i class="bi bi-check-circle-fill fs-3"></i>
            <div>
                <div class="fw-bold fsz-15">Caisse clôturée</div>
                <div class="fsz-13">
                    Clôturée le {{ $session->clôturee_a?->locale('fr')->isoFormat('D MMM YYYY à HH:mm') }}
                    par {{ $session->user->name }}
                </div>
            </div>
        </div>
    @endif

    {{-- ===== En-tête date ===== --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="mb-0 fw-bold">
                Service du {{ $date }}
                @unless($estAujourdhui)
                    <span class="badge badge-service-anterior">
                        <i class="bi bi-clock-history me-1"></i>Service antérieur
                    </span>
                @endunless
            </h5>
            <p class="text-muted mb-0 fsz-13">
                <i class="bi bi-clock me-1"></i>Récapitulatif en temps réel
            </p>
        </div>
        <a href="{{ route('ventes.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-list-ul me-1"></i>Voir les commandes
        </a>
    </div>

    <div class="row g-3 mb-4">

       <div class="col-12">
           {{-- KPIs du jour --}}
           <div class="row g-3 mb-3">
               <div class="col-6 col-md-3">
                   <div class="stat-card">
                       <div class="stat-icon orange"><i class="bi bi-currency-exchange"></i></div>
                       <div class="stat-info">
                           <div class="stat-label">CA Total</div>
                           <div class="stat-value fsz-19">
                               {{ number_format($caTotal, 0, ',', ' ') }}
                               <small class="fsz-11 c-muted">FCFA</small>
                           </div>
                       </div>
                   </div>
               </div>
               <div class="col-6 col-md-3">
                   <div class="stat-card">
                       <div class="stat-icon blue"><i class="bi bi-receipt-cutoff"></i></div>
                       <div class="stat-info">
                           <div class="stat-label">Commandes</div>
                           <div class="stat-value fsz-19">{{ $nbCmds }}</div>
                       </div>
                   </div>
               </div>
               <div class="col-6 col-md-3">
                   <div class="stat-card">
                       <div class="stat-icon green"><i class="bi bi-graph-up"></i></div>
                       <div class="stat-info">
                           <div class="stat-label">Panier moyen</div>
                           <div class="stat-value fsz-19">
                               {{ number_format($panier, 0, ',', ' ') }}
                               <small class="fsz-11 c-muted">F</small>
                           </div>
                       </div>
                   </div>
               </div>
               <div class="col-6 col-md-3">
                   <div class="stat-card">
                       <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
                       <div class="stat-info">
                           <div class="stat-label">Annulées</div>
                           <div class="stat-value fsz-19">
                               {{ $commandes->where('statut', 'annule')->count() }}
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       </div>

        {{-- ===== Colonne gauche : Récapitulatif ===== --}}
        <div class="col-xl-8">


            {{-- Répartition par mode de paiement --}}
            <div class="card mb-3">
                <div class="card-header">
                    <span class="card-title">Répartition par mode de paiement</span>
                </div>
                <div class="card-body">
                    @foreach($repartition as $r)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fsz-13 fw-600">{{ $r['label'] }}</span>
                                <span class="fsz-13 fw-bold">
                            {{ number_format($r['val'], 0, ',', ' ') }} FCFA
                            <span class="fsz-11 c-muted">({{ $r['pct'] }}%)</span>
                        </span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar {{ $r['bar'] }} progress-bar-transition" role="progressbar"
                                     style="width:{{ $r['pct'] }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top 5 produits du jour --}}
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Top produits du jour</span>
                </div>
                <div class="card-body p-0">
                    @php
                        $topProduits = collect();
                        foreach($commandes->whereNotIn('statut', ['annule']) as $cmd) {
                            foreach($cmd->lignes as $ligne) {
                                $key_ligne = $ligne->menu_id;
                                $topProduits = $topProduits->map(function ($item, $key){
                                    if ($key == $key_ligne) {
                                        $item['qte'] += $ligne->quantite;
                                        $item['ca']  += $ligne->sous_total;
                                    } else {
                                        $item[$key] = [
                                            'nom'   => $ligne->menu->nom,
                                            'emoji' => $ligne->menu->emoji,
                                            'qte'   => $ligne->quantite,
                                            'ca'    => $ligne->sous_total,
                                        ];
                                    }
                                    return $item;
                                });
                            }
                        }
                        $topProduits = $topProduits->sortByDesc('ca')->take(5);
                    @endphp

                    @if($topProduits->isEmpty())
                        <div class="text-center py-4 text-muted fsz-13">
                            Aucune vente enregistrée
                        </div>
                    @else
                        <table class="table table-amira mb-0">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Produit</th>
                                <th class="text-center">Qté vendue</th>
                                <th class="text-end">CA généré</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($topProduits->values() as $i => $p)
                                <tr>
                                    <td class="fw-bold c-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <span class="fsz-18">{{ $p['emoji'] }}</span>
                                        <span class="fw-600 ms-2">{{ $p['nom'] }}</span>
                                    </td>
                                    <td class="text-center">
                                <span class="badge badge-brand-soft">
                                    {{ $p['qte'] }}
                                </span>
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($p['ca'], 0, ',', ' ') }} FCFA
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>{{-- /col gauche --}}

        {{-- ===== Colonne droite : Formulaire clôture ===== --}}
        <div class="col-xl-4">
            <div class="card card-sticky">

                <div class="card-header">
                <span class="card-title">
                    <i class="bi bi-cash-stack text-success me-2"></i>
                    {{ $estCloture ? 'Récapitulatif clôture' : 'Clôturer la caisse' }}
                </span>
                </div>

                <div class="card-body">

                    @if($estCloture)
                        {{-- Mode lecture --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between py-2 border-bottom b-subtle">
                                <span class="fsz-13 c-muted">Fond ouverture</span>
                                <span class="fw-600">{{ number_format($session->fond_caisse_ouverture, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom b-subtle">
                                <span class="fsz-13 c-muted">Fond clôture (réel)</span>
                                <span class="fw-600">{{ number_format($session->fond_caisse_cloture, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom b-subtle">
                                <span class="fsz-13 c-muted">CA total</span>
                                <span class="fw-bold c-brand">{{ number_format($session->ca_total, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span class="fsz-13 c-muted">Écart de caisse</span>
                                <span class="fw-bold {{ $session->ecart_caisse >= 0 ? 'c-success' : 'c-danger' }}">
                            {{ $session->ecart_caisse >= 0 ? '+' : '' }}{{ number_format($session->ecart_caisse, 0, ',', ' ') }} FCFA
                        </span>
                            </div>
                        </div>

                        @if($session->observations)
                            <div class="p-3 rounded mb-3 card-header-subtle fsz-13">
                                <strong>Observations :</strong><br>{{ $session->observations }}
                            </div>
                        @endif

                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary w-100 rounded-md">
                            <i class="bi bi-arrow-left me-1"></i>Retour au dashboard
                        </a>

                    @else
                        {{-- Mode saisie --}}
                        <form method="POST" action="{{ route('caisse.cloture.store') }}" id="formCloture">
                            @csrf
                            <input type="hidden" name="date_service" value="{{ $dateService }}">

                            {{-- Résumé CA attendu --}}
                            <div class="p-3 rounded mb-4 ca-summary-box">
                                <div class="fsz-11 fw-bold text-uppercase ls-06 mb-2 ca-summary-label">
                                    CA enregistré {{ $estAujourdhui ? "aujourd'hui" : 'ce jour-là' }}
                                </div>
                                <div class="fsz-24 fw-bold c-brand">
                                    {{ number_format($caTotal, 0, ',', ' ') }} FCFA
                                </div>
                                <div class="fsz-12 c-muted mt-1">
                                    dont {{ number_format($caEspeces, 0, ',', ' ') }} FCFA en espèces
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Fond de caisse à l'ouverture (FCFA)</label>
                                <input type="number" name="fond_ouverture" class="form-control"
                                       value="{{ $session?->fond_caisse_ouverture ?? 0 }}"
                                       min="0" step="500" placeholder="0"
                                       id="fondOuverture" oninput="calculerEcart()">
                                <div class="form-text">Montant qui était en caisse ce matin</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Fond de caisse réel en ce moment (FCFA)</label>
                                <input type="number" name="fond_reel" class="form-control" required
                                       min="0" step="500" placeholder="0"
                                       id="fondReel" oninput="calculerEcart()">
                                <div class="form-text">Comptez physiquement la caisse</div>
                            </div>

                            {{-- Écart calculé en temps réel --}}
                            <div class="p-3 rounded mb-4 ecart-box" id="zoneEcart">
                                <div class="fsz-12 c-muted mb-1">Écart de caisse calculé</div>
                                <div class="fsz-20 fw-bold" id="ecartAffiche">—</div>
                                <div class="fsz-11 c-muted mt-1" id="ecartExplication"></div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Observations <span class="text-muted fw-normal">(optionnel)</span></label>
                                <textarea name="observations" class="form-control" rows="3"
                                          placeholder="Notes sur le service, incidents, manques…"></textarea>
                            </div>

                            <button type="button" class="btn btn-amira w-100 btn-lg"
                                    onclick="confirmForm(this.closest('form'), 'Cette action est définitive et ne peut pas être annulée. Assurez-vous que toutes les données sont correctes.', {type:'warning',title:'Confirmer la clôture de caisse',confirmText:'Clôturer la caisse'})">
                                <i class="bi bi-lock-fill me-2"></i>Clôturer la caisse
                            </button>
                        </form>
                    @endif

                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        const caEspeces   = {{ $caEspeces }};
        const fondOuv     = document.getElementById('fondOuverture');
        const fondReel    = document.getElementById('fondReel');
        const ecartEl     = document.getElementById('ecartAffiche');
        const ecartExp    = document.getElementById('ecartExplication');

        function calculerEcart() {
            const fo = parseFloat(fondOuv?.value) || 0;
            const fr = parseFloat(fondReel?.value) || 0;

            if (fr === 0) {
                ecartEl.textContent  = '—';
                ecartEl.style.color  = 'var(--text-muted)';
                ecartExp.textContent = '';
                return;
            }

            // Écart = fond réel - (fond ouverture + CA espèces)
            const attendu = fo + caEspeces;
            const ecart   = fr - attendu;

            ecartEl.textContent = (ecart >= 0 ? '+' : '') + ecart.toLocaleString('fr') + ' FCFA';
            ecartEl.style.color = ecart === 0
                ? 'var(--success)'
                : ecart > 0
                    ? 'var(--info)'
                    : 'var(--danger)';

            ecartExp.textContent = ecart === 0
                ? 'Caisse parfaitement équilibrée ✓'
                : ecart > 0
                    ? `Excédent (fond attendu : ${attendu.toLocaleString('fr')} FCFA)`
                    : `Manque (fond attendu : ${attendu.toLocaleString('fr')} FCFA)`;
        }
    </script>
@endpush
