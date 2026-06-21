@extends('layouts.app')

@section('title', 'Clôture de caisse')
@section('page-title', 'Clôture de caisse')

@section('content')

    @php
        $date       = today()->locale('fr')->isoFormat('dddd D MMMM YYYY');
        $estCloture = $session?->est_cloturee ?? false;

        // Calculs en temps réel depuis les commandes du jour
        $caEspeces  = $commandes->where('mode_paiement', 'especes')->sum('total_ttc');
        $caMobile   = $commandes->where('mode_paiement', 'mobile_money')->sum('total_ttc');
        $caCarte    = $commandes->where('mode_paiement', 'carte')->sum('total_ttc');
        $caTotal    = $caEspeces + $caMobile + $caCarte;
        $nbCmds     = $commandes->count();
        $panier     = $nbCmds > 0 ? $caTotal / $nbCmds : 0;

        $repartition = [
            ['label' => 'Espèces',      'val' => $caEspeces, 'pct' => $caTotal > 0 ? round($caEspeces / $caTotal * 100) : 0, 'color' => '#16A34A'],
            ['label' => 'Mobile Money', 'val' => $caMobile,  'pct' => $caTotal > 0 ? round($caMobile  / $caTotal * 100) : 0, 'color' => '#2563EB'],
            ['label' => 'Carte',        'val' => $caCarte,   'pct' => $caTotal > 0 ? round($caCarte   / $caTotal * 100) : 0, 'color' => '#7C3AED'],
        ];
    @endphp

    {{-- ===== Bandeau si déjà clôturé ===== --}}
    @if($estCloture)
        <div class="alert alert-success d-flex align-items-center gap-3 mb-4" style="border-radius:12px">
            <i class="bi bi-check-circle-fill fs-3"></i>
            <div>
                <div style="font-weight:700;font-size:15px">Caisse clôturée</div>
                <div style="font-size:13px">
                    Clôturée le {{ $session->clôturee_a?->locale('fr')->isoFormat('D MMM YYYY à HH:mm') }}
                    par {{ $session->user->name }}
                </div>
            </div>
        </div>
    @endif

    {{-- ===== En-tête date ===== --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="mb-0" style="font-weight:700">Service du {{ $date }}</h5>
            <p class="text-muted mb-0" style="font-size:13px">
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
                           <div class="stat-value" style="font-size:19px">
                               {{ number_format($caTotal, 0, ',', ' ') }}
                               <small style="font-size:11px;color:var(--text-muted)">FCFA</small>
                           </div>
                       </div>
                   </div>
               </div>
               <div class="col-6 col-md-3">
                   <div class="stat-card">
                       <div class="stat-icon blue"><i class="bi bi-receipt-cutoff"></i></div>
                       <div class="stat-info">
                           <div class="stat-label">Commandes</div>
                           <div class="stat-value" style="font-size:19px">{{ $nbCmds }}</div>
                       </div>
                   </div>
               </div>
               <div class="col-6 col-md-3">
                   <div class="stat-card">
                       <div class="stat-icon green"><i class="bi bi-graph-up"></i></div>
                       <div class="stat-info">
                           <div class="stat-label">Panier moyen</div>
                           <div class="stat-value" style="font-size:19px">
                               {{ number_format($panier, 0, ',', ' ') }}
                               <small style="font-size:11px;color:var(--text-muted)">F</small>
                           </div>
                       </div>
                   </div>
               </div>
               <div class="col-6 col-md-3">
                   <div class="stat-card">
                       <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
                       <div class="stat-info">
                           <div class="stat-label">Annulées</div>
                           <div class="stat-value" style="font-size:19px">
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
                                <span style="font-size:13px;font-weight:600">{{ $r['label'] }}</span>
                                <span style="font-size:13px;font-weight:700">
                            {{ number_format($r['val'], 0, ',', ' ') }} FCFA
                            <span style="font-size:11px;color:var(--text-muted);">({{ $r['pct'] }}%)</span>
                        </span>
                            </div>
                            <div class="progress" style="height:8px;border-radius:6px;background:var(--border-color)">
                                <div class="progress-bar" role="progressbar"
                                     style="width:{{ $r['pct'] }}%;background:{{ $r['color'] }};border-radius:6px;transition:width .6s ease">
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
                                $key_ligne = $ligne->produit_id;
                                $topProduits = $topProduits->map(function ($item, $key){
                                    if ($key == $key_ligne) {
                                        $item['qte'] += $ligne->quantite;
                                        $item['ca']  += $ligne->sous_total;
                                    } else {
                                        $item[$key] = [
                                            'nom'   => $ligne->produit->nom,
                                            'emoji' => $ligne->produit->emoji,
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
                        <div class="text-center py-4 text-muted" style="font-size:13px">
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
                                    <td style="font-weight:700;color:var(--text-muted)">{{ $i + 1 }}</td>
                                    <td>
                                        <span style="font-size:18px">{{ $p['emoji'] }}</span>
                                        <span style="font-weight:600;margin-left:6px">{{ $p['nom'] }}</span>
                                    </td>
                                    <td class="text-center">
                                <span class="badge" style="background:var(--amira-orange-light);color:var(--amira-orange);font-size:13px;font-weight:700">
                                    {{ $p['qte'] }}
                                </span>
                                    </td>
                                    <td class="text-end" style="font-weight:700">
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
            <div class="card" style="position:sticky;top:calc(var(--topbar-h) + 16px)">

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
                            <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--border-color)!important">
                                <span style="font-size:13px;color:var(--text-muted)">Fond ouverture</span>
                                <span style="font-weight:600">{{ number_format($session->fond_caisse_ouverture, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--border-color)!important">
                                <span style="font-size:13px;color:var(--text-muted)">Fond clôture (réel)</span>
                                <span style="font-weight:600">{{ number_format($session->fond_caisse_cloture, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--border-color)!important">
                                <span style="font-size:13px;color:var(--text-muted)">CA total</span>
                                <span style="font-weight:700;color:var(--amira-orange)">{{ number_format($session->ca_total, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span style="font-size:13px;color:var(--text-muted)">Écart de caisse</span>
                                <span style="font-weight:700;color:{{ $session->ecart_caisse >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                            {{ $session->ecart_caisse >= 0 ? '+' : '' }}{{ number_format($session->ecart_caisse, 0, ',', ' ') }} FCFA
                        </span>
                            </div>
                        </div>

                        @if($session->observations)
                            <div class="p-3 rounded mb-3" style="background:var(--body-bg);font-size:13px">
                                <strong>Observations :</strong><br>{{ $session->observations }}
                            </div>
                        @endif

                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary w-100" style="border-radius:8px">
                            <i class="bi bi-arrow-left me-1"></i>Retour au dashboard
                        </a>

                    @else
                        {{-- Mode saisie --}}
                        <form method="POST" action="{{ route('caisse.cloture.store') }}" id="formCloture">
                            @csrf

                            {{-- Résumé CA attendu --}}
                            <div class="p-3 rounded mb-4" style="background:var(--amira-orange-light);border:1px solid rgba(244,98,31,.2)">
                                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--amira-orange-dark);margin-bottom:6px">
                                    CA enregistré aujourd'hui
                                </div>
                                <div style="font-size:24px;font-weight:700;color:var(--amira-orange)">
                                    {{ number_format($caTotal, 0, ',', ' ') }} FCFA
                                </div>
                                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
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
                            <div class="p-3 rounded mb-4" style="background:var(--body-bg);border:1px solid var(--border-color)" id="zoneEcart">
                                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">Écart de caisse calculé</div>
                                <div style="font-size:20px;font-weight:700" id="ecartAffiche">—</div>
                                <div style="font-size:11px;color:var(--text-muted);margin-top:2px" id="ecartExplication"></div>
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
