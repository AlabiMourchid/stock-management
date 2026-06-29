<?php
// app/Http/Controllers/DepenseController.php

namespace App\Http\Controllers;

use App\Models\Depense;
use Illuminate\Http\Request;

class DepenseController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->periode ?? 'jour';
        $debut   = $request->debut  ?? today()->toDateString();
        $fin     = $request->fin    ?? today()->toDateString();

        // Calcul automatique de la période si sélecteur rapide utilisé
        if ($request->periode && !$request->debut) {
            [$debut, $fin] = match($periode) {
                'semaine' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
                'mois'    => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                default   => [today()->toDateString(), today()->toDateString()],
            };
        }

        $query = Depense::with('user')
            ->whereBetween('date_depense', [$debut, $fin])
            ->latest('date_depense');

        // Filtre catégorie
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        $depenses   = $query->get();
        $total      = $depenses->sum('montant');
        $parJour    = $depenses->groupBy(fn($d) => $d->date_depense->format('Y-m-d'))
            ->map->sum('montant');
        $categories = Depense::select('categorie')
            ->whereNotNull('categorie')
            ->distinct()->pluck('categorie');

        // KPIs
        $totalJour    = Depense::aujourdhui()->sum('montant');
        $totalSemaine = Depense::semaine()->sum('montant');
        $totalMois    = Depense::mois()->sum('montant');

        return view('admin.depenses', compact(
            'depenses', 'total', 'parJour', 'categories',
            'totalJour', 'totalSemaine', 'totalMois',
            'debut', 'fin', 'periode'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'libelle'       => 'required|string|max:200',
            'categorie'     => 'nullable|string|max:100',
            'montant'       => 'required|numeric|min:1',
            'date_depense'  => 'required|date',
            'note'          => 'nullable|string|max:500',
        ]);

        $data['user_id'] = auth()->id();

        Depense::create($data);

        return back()->with('success', "Dépense « {$data['libelle']} » enregistrée.");
    }

    public function update(Request $request, Depense $depense)
    {
        $data = $request->validate([
            'libelle'      => 'required|string|max:200',
            'categorie'    => 'nullable|string|max:100',
            'montant'      => 'required|numeric|min:1',
            'date_depense' => 'required|date',
            'note'         => 'nullable|string|max:500',
        ]);

        $depense->update($data);

        return back()->with('success', "Dépense mise à jour.");
    }

    public function destroy(Depense $depense)
    {
        $depense->delete();
        return back()->with('success', "Dépense supprimée.");
    }
}
