<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\StatistiqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RapportController extends Controller
{
    public function __construct(private StatistiqueService $stats) {}

    public function index(Request $request)
    {
        Gate::authorize('view-reports');

        $debut = $request->debut ?? today()->toDateString();
        $fin   = $request->fin   ?? today()->toDateString();

        if ($fin < $debut) {
            $fin = $debut;
        }

        $topProduits = $this->stats->topProduits($debut . ' 00:00:00', $fin . ' 23:59:59');
        $evolution   = $this->stats->evolutionVentes7j();
        $analyse     = $this->stats->analyseFinanciere($debut, $fin);
        $caPeriode         = $analyse['ca_periode'];
        $totalFixe         = $analyse['total_fixe'];
        $totalVariable     = $analyse['total_variable'];
        $totalDepenses     = $analyse['total_depenses'];
        $margeNette        = $analyse['marge_nette'];
        $tauxMargeNette    = $analyse['taux_marge_nette'];
        $seuilRentabilite  = $analyse['seuil_rentabilite'];
        $seuilAtteint      = $analyse['seuil_atteint'];

        return view('rapports.index', compact(
            'topProduits', 'evolution', 'caPeriode',
            'totalFixe', 'totalVariable', 'totalDepenses', 'margeNette', 'tauxMargeNette',
            'seuilRentabilite', 'seuilAtteint',
            'debut', 'fin'
        ));
    }
    public function pertes(Request $request)
    {
        Gate::authorize('view-reports');
        $debut = $request->debut ?? now()->startOfMonth()->toDateString();
        $fin   = $request->fin   ?? today()->toDateString();

        $rapport = $this->stats->rapportPertes($debut, $fin);

        return view('rapports.pertes', compact('rapport', 'debut', 'fin'));
    }
}
