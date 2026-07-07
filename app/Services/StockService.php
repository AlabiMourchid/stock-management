<?php

namespace App\Services;
use App\Models\Produit;
use App\Models\StockMouvement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Enregistre une ENTRÉE (approvisionnement).
     */
    public function entree(
        Produit $produit,
        float   $quantite,
        int     $userId,
        ?int    $fournisseurId = null,
        ?string $motif = null,
        ?string $date = null
    ): StockMouvement {
        return DB::transaction(function () use ($produit, $quantite, $userId, $fournisseurId, $motif, $date) {
            $avant = $produit->stock_actuel;
            $apres = $avant + $quantite;

            $produit->update(['stock_actuel' => $apres]);

            return StockMouvement::create([
                'produit_id'     => $produit->id,
                'user_id'        => $userId,
                'fournisseur_id' => $fournisseurId,
                'type'           => 'entree',
                'quantite'       => $quantite,
                'stock_avant'    => $avant,
                'stock_apres'    => $apres,
                'motif'          => $motif,
                'date_mouvement' => $date ?? today(),
            ]);
        });
    }

    /**
     * Enregistre une SORTIE fin de service (saisie manuelle du gestionnaire).
     */
    public function sortie(
        Produit $produit,
        float   $quantite,
        int     $userId,
        ?string $motif = 'Fin de service',
        ?string $date = null
    ): StockMouvement {
        return DB::transaction(function () use ($produit, $quantite, $userId, $motif, $date) {
            $avant = $produit->stock_actuel;
            $apres = max(0, $avant - $quantite);   // jamais négatif

            $produit->update(['stock_actuel' => $apres]);

            return StockMouvement::create([
                'produit_id'     => $produit->id,
                'user_id'        => $userId,
                'type'           => 'sortie',
                'quantite'       => $quantite,
                'stock_avant'    => $avant,
                'stock_apres'    => $apres,
                'motif'          => $motif,
                'date_mouvement' => $date ?? today(),
            ]);
        });
    }

    /**
     * Remise à zéro / correction d'inventaire.
     */
    public function inventaire(Produit $produit, float $nouvelleQte, int $userId, ?string $motif = null): StockMouvement
    {
        return DB::transaction(function () use ($produit, $nouvelleQte, $userId, $motif) {
            $avant = $produit->stock_actuel;
            $produit->update(['stock_actuel' => $nouvelleQte]);

            return StockMouvement::create([
                'produit_id'     => $produit->id,
                'user_id'        => $userId,
                'type'           => 'inventaire',
                'quantite'       => abs($nouvelleQte - $avant),
                'stock_avant'    => $avant,
                'stock_apres'    => $nouvelleQte,
                'motif'          => $motif ?? 'Correction inventaire',
                'date_mouvement' => today(),
            ]);
        });
    }

    /**
     * Saisie groupée de fin de service (plusieurs produits en une fois).
     * $lignes = [['produit_id' => 1, 'quantite' => 3.5, 'motif' => '...'], ...]
     */
    public function sortieGroupee(array $lignes, int $userId, ?string $date = null): array
    {
        $mouvements = [];
        DB::transaction(function () use ($lignes, $userId, $date, &$mouvements) {
            foreach ($lignes as $ligne) {
                if (empty($ligne['quantite']) || $ligne['quantite'] <= 0) continue;
                $produit = Produit::findOrFail($ligne['produit_id']);
                $mouvements[] = $this->sortie(
                    $produit,
                    (float) $ligne['quantite'],
                    $userId,
                    $ligne['motif'] ?? 'Fin de service',
                    $date
                );
            }
        });
        return $mouvements;
    }
}
