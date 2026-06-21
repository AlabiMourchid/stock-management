<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Perte;
use App\Models\Produit;
use App\Services\VenteService;
use Illuminate\Http\Request;

class CuisineController extends Controller
{
    public function __construct(private VenteService $venteService) {}

    public function index()
    {
        $commandes = Commande::with('lignes.produit')
            ->whereIn('statut', ['en_attente', 'en_preparation'])
            ->orderBy('created_at')
            ->get();

        return view('cuisine.index', compact('commandes'));
    }

    public function changerStatut(Request $request, Commande $commande)
    {
        $request->validate(['statut' => 'required|in:en_preparation,pret,livre']);
        $this->venteService->changerStatut($commande, $request->statut);

        // Retour JSON pour les mises à jour AJAX depuis l'écran cuisine
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'statut' => $commande->fresh()->statut]);
        }
        return back();
    }

    public function signalerPerte(Request $request)
    {
        $validated = $request->validate([
            'produit_id'  => 'required|exists:produits,id',
            'quantite'    => 'required|numeric|min:0.1',
            'motif'       => 'required|in:perime,brulee,tombe,mauvaise_cuisson,autre',
            'description' => 'nullable|string|max:500',
        ]);

        $produit = Produit::findOrFail($validated['produit_id']);
        Perte::create([
            ...$validated,
            'user_id'       => auth()->id(),
            'cout_unitaire' => $produit->cout_unitaire,
            'cout_total'    => $produit->cout_unitaire * $validated['quantite'],
            'date_perte'    => today(),
        ]);

        return back()->with('success', 'Perte signalée.');
    }
}
