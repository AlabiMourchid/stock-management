<?php

namespace App\Services;
use App\Models\Commande;
use App\Models\LigneCommande;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class VenteService
{
    /**
     * Crée une commande complète depuis le POS.
     * $lignes = [['produit_id' => 1, 'quantite' => 2], ...]
     */
    public function creerCommande(
        array  $lignes,
        int    $userId,
        string $modePaiement = 'especes',
        string $type = 'sur_place',
        float  $montantRecu = 0,
        ?string $notes = null,
        float  $supplement = 0
    ): Commande {
        return DB::transaction(function () use ($lignes, $userId, $modePaiement, $type, $montantRecu, $notes, $supplement) {
            $commande = Commande::create([
                'user_id'        => $userId,
                'statut'         => 'en_preparation',
                'type'           => $type,
                'mode_paiement'  => $modePaiement,
                'total_ttc'      => 0,
                'supplement'     => $supplement,
                'montant_recu'   => $montantRecu ?: null,
                'notes'          => $notes,
            ]);

            $total = 0;
            foreach ($lignes as $ligne) {
                $produit = Menu::findOrFail($ligne['menu_id']);
                $qte     = (int) $ligne['quantite'];
                $prix    = $produit->prix_vente;
                $ss      = $prix * $qte;
                $total  += $ss;

                LigneCommande::create([
                    'commande_id'   => $commande->id,
                    'menu_id'    => $produit->id,
                    'quantite'      => $qte,
                    'prix_unitaire' => $prix,
                    'sous_total'    => $ss,
                ]);
            }

            $totalFinal = $total + $supplement;
            $monnaie    = $montantRecu > 0 ? max(0, $montantRecu - $totalFinal) : null;

            $commande->update([
                'total_ttc'      => $totalFinal,
                'monnaie_rendue' => $monnaie,
            ]);

            return $commande->load('lignes.menu');
        });
    }

    /**
     * Met à jour le statut d'une commande.
     */
    public function changerStatut(Commande $commande, string $statut): Commande
    {
        $data = ['statut' => $statut];
        if ($statut === 'pret') {
            $data['pret_a'] = now();
        }
        $commande->update($data);
        return $commande->fresh();
    }
}
