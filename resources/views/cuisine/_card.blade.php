{{-- resources/views/cuisine/_card.blade.php --}}
{{-- Carte commande pour l'écran cuisine (partiel) --}}

@php
    $statut = $cmd->statut;
    $classCarte  = match($statut) { 'en_attente' => 'en-attente', 'en_preparation' => 'en-prep', default => 'pret' };
    $minutesAgo  = $cmd->created_at->diffInMinutes(now());
    $urgent      = $minutesAgo >= 15 && $statut !== 'pret';
@endphp

<div class="kitchen-card mb-3 {{ $urgent ? 'border-danger' : '' }}" data-cmd-id="{{ $cmd->id }}">
    <div class="kitchen-card-header {{ $classCarte }} d-flex align-items-center justify-content-between">
        <div>
            <span style="font-size:15px">{{ $cmd->numero }}</span>
            @if($cmd->type === 'emporter')
                <span class="badge bg-dark ms-1" style="font-size:10px">À emporter</span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($urgent)
                <span class="badge bg-danger" style="font-size:10px;animation:pulse 1s infinite">
                    <i class="bi bi-clock-history"></i> {{ $minutesAgo }}min
                </span>
            @else
                <span style="font-size:12px;opacity:.7">{{ $cmd->created_at->format('H:i') }}</span>
            @endif
        </div>
    </div>
    <div class="kitchen-card-body">
        @foreach($cmd->lignes as $ligne)
            <div class="kitchen-item">
                <span class="kitchen-item-qty">{{ $ligne->quantite }}×</span>
                <span>{{ $ligne->produit->emoji }} {{ $ligne->produit->nom }}</span>
            </div>
        @endforeach

        @if($cmd->notes)
            <div class="mt-2 p-2 rounded" style="background:#FEF3C7;font-size:12px;color:#92400E">
                <i class="bi bi-sticky me-1"></i>{{ $cmd->notes }}
            </div>
        @endif
    </div>

    {{-- Boutons d'action --}}
    @can('cuisine-actions')
        <div class="px-3 pb-3 d-flex gap-2">
            @if($statut === 'en_attente')
                <button class="btn btn-sm btn-info w-100 btn-statut-en_preparation text-white"
                        onclick="changerStatutCommande({{ $cmd->id }}, 'en_preparation')">
                    <i class="bi bi-fire me-1"></i>Démarrer
                </button>
            @elseif($statut === 'en_preparation')
                <button class="btn btn-sm btn-success w-100 btn-statut-pret"
                        onclick="changerStatutCommande({{ $cmd->id }}, 'pret')">
                    <i class="bi bi-check-circle me-1"></i>Prêt !
                </button>
            @endif
        </div>
    @endcan
</div>

{{-- ============================================================ --}}
{{-- resources/views/ventes/recu.blade.php --}}
{{-- Reçu imprimable --}}

{{-- NOTE: Ce fichier doit être séparé — inclus ici pour référence --}}

