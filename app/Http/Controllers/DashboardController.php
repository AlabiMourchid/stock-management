<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Produit;
use App\Services\StatistiqueService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private StatistiqueService $stats) {}

    public function index()
    {
        $kpis      = $this->stats->kpisDashboard();
        $evolution = $this->stats->evolutionVentes7j();
        $alertes   = Produit::stockCritique()->limit(5)->get();
        $dernieres = Commande::with('user')->latest()->limit(8)->get();

        return view('dashboard.index', compact('kpis', 'evolution', 'alertes', 'dernieres'));
    }
}
