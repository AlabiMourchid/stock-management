<?php

namespace App\Services;
use App\Models\ClotureCaisse;
use App\Models\Commande;

class CaisseService
{
    /**
     * Ouvre (ou récupère) la session de caisse du jour.
     */
    public function ouvrirSession(int $userId, float $fondOuverture = 0, ?string $date=null): ClotureCaisse
    {
        $date = $date ?? today()->toDateString();
        return ClotureCaisse::firstOrCreate(
            ['date_service'=>$date,'est_cloturee'=>false],
            ['user_id'=>$userId,'fond_caisse_ouverture'=> $fondOuverture]
        );
    }

    /**
     * Clôture la caisse du jour en calculant les totaux réels.
     */
    public function cloturerSession(ClotureCaisse $session, float $fondReel, ?string $observations = null,?string $date=null): ClotureCaisse
    {
        $d = $date ?? $session->date_service->toDateString();
        $commandes = Commande::whereDate('created_at', $d)
            ->whereNotIn('statut', ['annule'])
            ->get();

        $caEspeces     = $commandes->where('mode_paiement', 'especes')->sum('total_ttc');
        $caMobile      = $commandes->where('mode_paiement', 'mobile_money')->sum('total_ttc');
        $caTotal       = $caEspeces + $caMobile ;
        $ecart         = $fondReel - ($session->fond_caisse_ouverture + $caEspeces);

        $session->update([
            'ca_especes'           => $caEspeces,
            'ca_mobile_money'      => $caMobile,
            'ca_total'             => $caTotal,
            'nb_commandes'         => $commandes->count(),
            'fond_caisse_cloture'  => $fondReel,
            'ecart_caisse'         => $ecart,
            'observations'         => $observations,
            'est_cloturee'         => true,
            'clôturee_a'           => now(),
        ]);

        return $session->fresh();
    }
}
