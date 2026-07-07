<?php
// routes/web.php — VERSION FINALE COMPLÈTE

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{DashboardController,
    DepenseController,
    StockController,
    VenteController,
    CaisseController,
    CuisineController,
    RapportController,
    AdminController,
    AuthController};

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Stock (matières premières)
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/',              [StockController::class, 'index'])->name('index');
        Route::post('/entree',       [StockController::class, 'entree'])->name('entree');
        Route::post('/inventaire',   [StockController::class, 'inventaire'])->name('inventaire');
        Route::get('/fin-service',   [StockController::class, 'saisieFinService'])->name('fin-service');
        Route::post('/fin-service',  [StockController::class, 'enregistrerFinService'])->name('fin-service.store');
        Route::get('/mouvements',    [StockController::class, 'mouvement'])->name('mouvement');
    });

    // Ventes / POS
    Route::prefix('ventes')->name('ventes.')->group(function () {
        Route::get('/',                        [VenteController::class, 'index'])->name('index');
        Route::get('/pos',                     [VenteController::class, 'pos'])->name('pos');
        Route::post('/pos',                    [VenteController::class, 'store'])->name('store');
        Route::get('/recu/{commande}',         [VenteController::class, 'recu'])->name('recu');
        Route::patch('/{commande}/statut',     [VenteController::class, 'changerStatut'])->name('statut');
    });

    // Caisse
    Route::prefix('caisse')->name('caisse.')->group(function () {
        Route::get('/cloture',  [CaisseController::class, 'cloture'])->name('cloture');
        Route::post('/cloture', [CaisseController::class, 'effectuerCloture'])->name('cloture.store');
    });

    // Cuisine
    Route::prefix('cuisine')->name('cuisine.')->group(function () {
        Route::get('/',                    [CuisineController::class, 'index'])->name('index');
        Route::patch('/{commande}/statut', [CuisineController::class, 'changerStatut'])->name('statut');
        Route::post('/perte',              [CuisineController::class, 'signalerPerte'])->name('perte');
    });

    // Rapports
    Route::prefix('rapports')->name('rapports.')->middleware('can:view-reports')->group(function () {
        Route::get('/',       [RapportController::class, 'index'])->name('index');
        Route::get('/pertes', [RapportController::class, 'pertes'])->name('pertes');
    });

    // Admin
    Route::prefix('admin')->name('admin.')->middleware('can:admin')->group(function () {
        // Menu (articles finis)
        Route::get('/menu',                    [AdminController::class, 'menu'])->name('menu');
        Route::post('/menu',                   [AdminController::class, 'menuStore'])->name('menu.store');
        Route::put('/menu/{article}',          [AdminController::class, 'menuUpdate'])->name('menu.update');
        Route::delete('/menu/{article}',       [AdminController::class, 'menuDestroy'])->name('menu.destroy');

        // Stock MP
        Route::get('/stock',                   [AdminController::class, 'stock'])->name('stock');
        Route::post('/stock',                  [AdminController::class, 'stockStore'])->name('stock.store');
        Route::put('/stock/{produit}',         [AdminController::class, 'stockUpdate'])->name('stock.update');
        Route::delete('/stock/{produit}',      [AdminController::class, 'stockDestroy'])->name('stock.destroy');

        // Utilisateurs
        Route::get('/utilisateurs',            [AdminController::class, 'utilisateurs'])->name('utilisateurs');
        Route::post('/utilisateurs',           [AdminController::class, 'utilisateursStore'])->name('utilisateurs.store');
        Route::put('/utilisateurs/{user}',     [AdminController::class, 'utilisateursUpdate'])->name('utilisateurs.update');
        Route::delete('/utilisateurs/{user}',  [AdminController::class, 'utilisateursDestroy'])->name('utilisateurs.destroy');

        // Fournisseurs
        Route::get('/fournisseurs',                 [AdminController::class, 'fournisseurs'])->name('fournisseurs');
        Route::post('/fournisseurs',                [AdminController::class, 'fournisseursStore'])->name('fournisseurs.store');
        Route::put('/fournisseurs/{fournisseur}',   [AdminController::class, 'fournisseursUpdate'])->name('fournisseurs.update');
        Route::delete('/fournisseurs/{fournisseur}',[AdminController::class, 'fournisseursDestroy'])->name('fournisseurs.destroy');


        Route::get('/expenses', [DepenseController::class, 'index'])->name('expenses');

        Route::post('/expenses', [DepenseController::class, 'store'])->name('expenses.store');
        Route::put('/expenses/{depense}',  [DepenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{depense}',  [DepenseController::class, 'destroy'])->name('expenses.destroy');
    });
});
