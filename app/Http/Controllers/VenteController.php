<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\ClotureCaisse;
use App\Models\Commande;
use App\Models\Menu;
use App\Services\VenteService;
use Illuminate\Http\Request;

class VenteController extends Controller
{
    public function __construct(private VenteService $venteService) {}

    public function pos()
    {
        $produits   = Menu::with('categorie')->disponible()->orderBy('categorie_id')->get();
        $categories = Categorie::orderBy('ordre')->get();
        return view('ventes.pos', compact('produits', 'categories'));
    }


    public function index(Request $request)
    {
        $query = Commande::with(['user', 'lignes.menu'])->latest();

        // Plage de dates (défaut = aujourd'hui)
        $dateDebut = $request->date_debut ?? today()->toDateString();
        $dateFin   = $request->date_fin   ?? today()->toDateString();

        // S'assurer que date_fin >= date_debut
        if ($dateFin < $dateDebut) {
            $dateFin = $dateDebut;
        }

        $query->whereDate('created_at', '>=', $dateDebut)
              ->whereDate('created_at', '<=', $dateFin);

        // Filtre statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtre mode de paiement
        if ($request->filled('mode_paiement')) {
            $query->where('mode_paiement', $request->mode_paiement);
        }

        // Filtre type (sur_place / emporter)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $commandes = $query->paginate(20)->withQueryString();

        return view('ventes.index', compact('commandes', 'dateDebut', 'dateFin'));
    }

    public function cloture()
    {
        $session = ClotureCaisse::whereDate('date_service', today())->first();

        // Charger toutes les commandes du jour avec lignes et produits
        $commandes = Commande::with(['lignes.menu', 'user'])
            ->whereDate('created_at', today())
            ->whereNotIn('statut', ['annule'])
            ->get();

        return view('ventes.cloture', compact('session', 'commandes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lignes'            => 'required|array|min:1',
            'lignes.*.menu_id'  => 'required|exists:menus,id',
            'lignes.*.quantite' => 'required|integer|min:1',
            'mode_paiement'     => 'required|in:especes,mobile_money',
            'type'              => 'required|in:sur_place,emporter',
            'montant_recu'      => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string|max:500',
            'supplement'        => 'nullable|numeric|min:0',
        ]);

        $commande = $this->venteService->creerCommande(
            $validated['lignes'],
            auth()->id(),
            $validated['mode_paiement'],
            $validated['type'],
            (float) ($validated['montant_recu'] ?? 0),
            $validated['notes'] ?? null,
            (float) ($validated['supplement'] ?? 0)
        );

        return response()->json([
            'success'  => true,
            'commande' => $commande,
            'recu_url' => route('ventes.recu', $commande),
        ]);
    }

    public function recu(Commande $commande)
    {
        $commande->load('lignes.menu', 'user');
        return view('ventes.recu', compact('commande'));
    }

    public function changerStatut(Request $request, Commande $commande)
    {
        $request->validate(['statut' => 'required|in:en_attente,en_preparation,pret,livre,annule']);
        $this->venteService->changerStatut($commande, $request->statut);
        return back()->with('success', 'Statut mis à jour.');
    }
}
