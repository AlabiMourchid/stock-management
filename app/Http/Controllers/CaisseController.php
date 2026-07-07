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
    public function __construct(private CaisseService $caisseService)
    {
    }

    public function cloture(Request $request)
    {
        $dateService = $request->date_service ?? today()->toDateString();
        $session = ClotureCaisse::whereDate('date_service', $dateService)->first();
        $estCloture = $session?->est_cloturee ?? false;
        $commandes = Commande::with(['lignes.menu', 'user'])->whereNotIn('statut', ['annule'])->whereDate('created_at', $dateService)->get();
        return view('ventes.cloture', compact('session', 'commandes', 'estCloture', 'dateService'));
    }

    public function effectuerCloture(Request $request)
    {
        $request->validate([
            'fond_reel' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
            'date_service' => 'required|date',
        ]);

        $session = $this->caisseService->ouvrirSession(
            auth()->id(), (float)($request->fond_ouverture ?? 0), $request->date_service
        );
        $session = $this->caisseService->cloturerSession(
            $session, $request->fond_reel, $request->observations, $request->date_service
        );
        return redirect()->route('dashboard')
            ->with('success', 'Caisse clôturée — CA : ' . number_format($session->ca_total, 0, ',', ' ') . ' FCFA');
    }
}
