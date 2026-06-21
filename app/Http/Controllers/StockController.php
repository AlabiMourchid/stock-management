<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\StockMouvement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StockController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(Request $request)
    {
        $query = Produit::with('categorie')->actif();

        if ($request->filtre === 'critique') {
            $query->stockCritique();
        }

        if ($request->search) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        $produits    = $query->orderBy('nom')->get();
        $categories  = Categorie::orderBy('ordre')->get();
        $fournisseurs = Fournisseur::where('actif', 1)->orderBy('nom')->get();

        return view('stock.index', compact('produits', 'categories', 'fournisseurs'));
    }

    public function saisieFinService()
    {
        Gate::authorize('admin');
        $produits = Produit::actif()->orderBy('nom')->get();
        return view('stock.fin-service', compact('produits'));
    }

    public function enregistrerFinService(Request $request)
    {
        Gate::authorize('admin');
        $validated = $request->validate([
            'lignes'                => 'required|array',
            'lignes.*.produit_id'   => 'required|exists:produits,id',
            'lignes.*.quantite'     => 'required|numeric|min:0',
            'lignes.*.motif'        => 'nullable|string|max:200',
        ]);

        $this->stockService->sortieGroupee($validated['lignes'], auth()->id());

        return redirect()->route('stock.index')->with('success', 'Sorties de fin de service enregistrées.');
    }

    public function entree(Request $request)
    {
        $validated = $request->validate([
            'produit_id'      => 'required|exists:produits,id',
            'quantite'        => 'required|numeric|min:0.01',
            'fournisseur_id'  => 'nullable|exists:fournisseurs,id',
            'motif'           => 'nullable|string|max:200',
            'date_mouvement'  => 'nullable|date',
        ]);

        $produit = Produit::findOrFail($validated['produit_id']);
        $this->stockService->entree(
            $produit,
            $validated['quantite'],
            auth()->id(),
            $validated['fournisseur_id'] ?? null,
            $validated['motif'] ?? null,
            $validated['date_mouvement'] ?? null
        );

        return back()->with('success', "Entrée de {$validated['quantite']} {$produit->unite}(s) enregistrée pour « {$produit->nom} ».");
    }

    public function mouvement()
    {
        $mouvements = StockMouvement::with(['produit', 'user'])
            ->latest()
            ->paginate(25);

        return view('stock.mouvement', compact('mouvements'));
    }

    public function inventaire(Request $request)
    {
        Gate::authorize('manage-stock');
        $validated = $request->validate([
            'produit_id'  => 'required|exists:produits,id',
            'nouvelle_qte'=> 'required|numeric|min:0',
            'motif'       => 'nullable|string|max:200',
        ]);
        $produit = Produit::findOrFail($validated['produit_id']);
        $this->stockService->inventaire($produit, $validated['nouvelle_qte'], auth()->id(), $validated['motif'] ?? null);
        return back()->with('success', "Stock de « {$produit->nom} » corrigé à {$validated['nouvelle_qte']} {$produit->unite}.");
    }
}
