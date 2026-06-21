<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClotureCaisse;
use App\Models\Commande;
use App\Models\Perte;
use App\Models\Produit;
use App\Services\CaisseService;
use Illuminate\Http\Request;

class CaisseController extends Controller
{
    public function __construct(private CaisseService $caisseService) {}

    public function cloture()
    {
        $session   = ClotureCaisse::whereDate('date_service', today())->first();
        $commandes = Commande::whereNotIn('statut', ['annule'])->whereDate('created_at', today())->get();
        return view('ventes.cloture', compact('session', 'commandes'));
    }

    public function effectuerCloture(Request $request)
    {
        $request->validate([
            'fond_reel'    => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
        ]);

        $session = $this->caisseService->ouvrirSession(auth()->id());
        $session = $this->caisseService->cloturerSession(
            $session,
            $request->fond_reel,
            $request->observations
        );

        return redirect()->route('dashboard')->with('success', 'Caisse clôturée. CA du jour : ' . number_format($session->ca_total, 0, ',', ' ') . ' FCFA');
    }
}
