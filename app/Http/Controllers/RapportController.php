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

        $periode = $request->periode ?? 'jour';
        [$debut, $fin] = match($periode) {
            'semaine' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'mois'    => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'annee'    => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            default   => [today()->toDateString(), today()->toDateString()],
        };

        $topProduits = $this->stats->topProduits($debut . ' 00:00:00', $fin . ' 23:59:59');
        $evolution   = $this->stats->evolutionVentes7j();
        $caPeriode   = $this->stats->caPeriode($debut . ' 00:00:00', $fin . ' 23:59:59');

        return view('rapports.index', compact('topProduits', 'evolution', 'caPeriode', 'periode', 'debut', 'fin'));
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
