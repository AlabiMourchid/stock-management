<?php

namespace App\Services;
use App\Models\Commande;
use App\Models\LigneCommande;
use App\Models\Perte;
use App\Models\Produit;

class StatistiqueService
{
    /** CA du jour */
    public function caJour(): float
    {
        return Commande::aujourdhui()
            ->whereNotIn('statut', ['annule'])
            ->sum('total_ttc');
    }

    /** CA sur une période */
    public function caPeriode(string $debut, string $fin): float
    {
        return Commande::whereBetween('created_at', [$debut, $fin])
            ->whereNotIn('statut', ['annule'])
            ->sum('total_ttc');
    }

    /** Évolution des ventes : 7 derniers jours */
    public function evolutionVentes7j(): array
    {
        $rows = Commande::selectRaw('DATE(created_at) as date, SUM(total_ttc) as total, COUNT(*) as nb')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->whereNotIn('statut', ['annule'])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $result[] = [
                'date'  => now()->subDays($i)->locale('fr')->isoFormat('ddd D'),
                'total' => $rows[$d]->total ?? 0,
                'nb'    => $rows[$d]->nb ?? 0,
            ];
        }
        return $result;
    }

    /** Top produits vendus sur une période */
    public function topProduits(string $debut, string $fin, int $limit = 10): \Illuminate\Support\Collection
    {
        return LigneCommande::selectRaw('menu_id, SUM(quantite) as qte_vendue, SUM(sous_total) as ca')
            ->whereHas('commande', fn($q) =>
            $q->whereBetween('created_at', [$debut, $fin])
                ->whereNotIn('statut', ['annule'])
            )
            ->with('menu:id,nom,categorie_id')
            ->groupBy('menu_id')
            ->orderByDesc('qte_vendue')
            ->limit($limit)
            ->get();
    }

    /** Rapport des pertes sur une période */
    public function rapportPertes(string $debut, string $fin): array
    {
        $pertes = Perte::with('produit:id,nom,emoji,unite')
            ->whereBetween('date_perte', [$debut, $fin])
            ->get();

        return [
            'pertes'       => $pertes,
            'cout_total'   => $pertes->sum('cout_total'),
            'par_motif'    => $pertes->groupBy('motif')->map->sum('cout_total'),
            'par_produit'  => $pertes->groupBy('produit_id')->map(fn($g) => [
                'produit'    => $g->first()->produit,
                'quantite'   => $g->sum('quantite'),
                'cout_total' => $g->sum('cout_total'),
            ])->values(),
        ];
    }

    /** KPIs pour le dashboard principal */
    public function kpisDashboard(): array
    {
        $caAujourd   = $this->caJour();
        $caHier      = $this->caPeriode(
            now()->subDay()->startOfDay()->toDateTimeString(),
            now()->subDay()->endOfDay()->toDateTimeString()
        );
        $tendance = $caHier > 0 ? round((($caAujourd - $caHier) / $caHier) * 100, 1) : 0;

        return [
            'ca_jour'              => $caAujourd,
            'ca_hier'              => $caHier,
            'tendance_ca'          => $tendance,
            'nb_commandes_jour'    => Commande::aujourdhui()->whereNotIn('statut', ['annule'])->count(),
            'commandes_en_cours'   => Commande::actives()->count(),
            'alertes_stock'        => Produit::stockCritique()->count(),
            'cout_pertes_semaine'  => Perte::where('date_perte', '>=', now()->startOfWeek())->sum('cout_total'),
        ];
    }
}
