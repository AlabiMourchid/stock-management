<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{Menu, User, Categorie, Produit};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ne re-semer que si la base est vide (idempotent pour les redéploiements)
        if (User::count() > 0) {
            return;
        }

        // ---------- Utilisateurs ----------
        User::create(['name' => 'Admin',     'email' => 'admin@amira.com',     'password' => Hash::make('password'), 'role' => 'admin']);
        User::create(['name' => 'Caissier Test',   'email' => 'caissier@amira.com',  'password' => Hash::make('password'), 'role' => 'caissier']);

        // ---------- Catégories (inspirées du menu Amira) ----------
        $cats = [
            ['nom' => 'Poulets', 'ordre' => 2],
            ['nom' => 'Entrées', 'ordre' => 1],
            ['nom' => 'Accompagnements', 'ordre' => 3],
            ['nom' => 'Pépites', 'ordre' => 4],
            ['nom' => 'Snacks', 'ordre' => 5],
            ['nom' => 'Desserts', 'ordre' => 6],
            ['nom' => 'Boissons', 'ordre' => 7],
            ['nom' => 'Boxes & Formules', 'ordre' => 8],
        ];
        foreach ($cats as $c) { Categorie::create($c); }

        // ---------- Produits (exemples calibrés menu Amira) ----------
        $menu = [
            // Poulets rôtis
            ['categorie_id' => 1, 'nom' => 'Poulet entier rôti', 'prix_vente' => 4500, 'ordre' => 1],
            ['categorie_id' => 1, 'nom' => 'Demi-poulet rôti',    'prix_vente' => 2500, 'ordre' => 2],
            ['categorie_id' => 1, 'nom' => 'Quart poulet rôti',    'prix_vente' => 1500, 'ordre' => 3],
            ['categorie_id' => 1, 'nom' => 'Ailes de poulet (x6)', 'prix_vente' => 2000, 'ordre' => 4],
            ['categorie_id' => 1, 'nom' => 'Cuisses de poulet (x2)', 'prix_vente' => 1800, 'ordre' => 5],
            // Pépites & Snacks
            ['categorie_id' => 2, 'nom' => 'Pépites de poulet',    'prix_vente' => 1500, 'ordre' => 1],
            ['categorie_id' => 2, 'nom' => 'Burger poulet',         'prix_vente' => 2000, 'ordre' => 2],
            ['categorie_id' => 2, 'nom' => 'Wrap poulet',          'prix_vente' => 1800, 'ordre' => 3],
            ['categorie_id' => 2, 'nom' => 'Hot-dog poulet',      'prix_vente' => 1200, 'ordre' => 4],
            // Accompagnements
            ['categorie_id' => 3, 'nom' => 'Frites',               'prix_vente' => 800,  'ordre' => 1],
            ['categorie_id' => 3, 'nom' => 'Riz jollof',            'prix_vente' => 600,  'ordre' => 2],
            ['categorie_id' => 3, 'nom' => 'Salade coleslaw',       'prix_vente' => 500,  'ordre' => 3],
            ['categorie_id' => 3, 'nom' => 'Sauce piment',          'prix_vente' => 200,  'ordre' => 4],
            ['categorie_id' => 3, 'nom' => 'Sauce mayo-ail',        'prix_vente' => 200,  'ordre' => 5],
            // Boxes
            ['categorie_id' => 4, 'nom' => 'Box Family (4 pers.)', 'prix_vente' => 12000,'ordre' => 1],
            ['categorie_id' => 4, 'nom' => 'Box Duo',              'prix_vente' => 6000, 'ordre' => 2],
            ['categorie_id' => 4, 'nom' => 'Box Solo',             'prix_vente' => 3500, 'ordre' => 3],
            // Desserts
            ['categorie_id' => 5, 'nom' => 'Beignets (x3)',         'prix_vente' => 600,  'ordre' => 1],
            ['categorie_id' => 5, 'nom' => 'Banana bread',         'prix_vente' => 800,  'ordre' => 2],
            // Boissons
            ['categorie_id' => 6, 'nom' => 'Bissap / Gingembre',   'prix_vente' => 500,  'ordre' => 1],
            ['categorie_id' => 6, 'nom' => 'Eau minérale 50cl',     'prix_vente' => 300,  'ordre' => 2],
            ['categorie_id' => 6, 'nom' => 'Jus naturel',           'prix_vente' => 700,  'ordre' => 3],
        ];


        foreach ($menu as $m) {
            Menu::create(array_merge(['disponible' => true, 'actif' => true], $m));
        }

        // ================================================================
        //  PRODUITS STOCK = Matières premières achetées
        //  Ce sont les éléments physiques gérés en inventaire
        // ================================================================
        $stock = [
            // Viandes / Volailles
            ['nom' => 'Poulet entier cru',          'unite' => 'pièce', 'stock_actuel' => 15,  'seuil_critique' => 4,  'cout_unitaire' => 2800],
            ['nom' => 'Ailes de poulet cru (kg)',   'unite' => 'kg',    'stock_actuel' => 8,   'seuil_critique' => 2,  'cout_unitaire' => 1500],
            ['nom' => 'Cuisses de poulet cru (kg)', 'unite' => 'kg',    'stock_actuel' => 6,   'seuil_critique' => 2,  'cout_unitaire' => 1600],
            // Féculents
            ['nom' => 'Pommes de terre (kg)',       'unite' => 'kg',    'stock_actuel' => 25,  'seuil_critique' => 5,  'cout_unitaire' => 400],
            ['nom' => 'Riz (kg)',                    'unite' => 'kg',    'stock_actuel' => 15,  'seuil_critique' => 3,  'cout_unitaire' => 600],
            ['nom' => 'Pain burger (pièce)',         'unite' => 'pièce', 'stock_actuel' => 30,  'seuil_critique' => 8,  'cout_unitaire' => 200],
            ['nom' => 'Tortilla wrap (pièce)',       'unite' => 'pièce', 'stock_actuel' => 20,  'seuil_critique' => 5,  'cout_unitaire' => 250],
            // Condiments & Sauces
            ['nom' => 'Huile de friture (litre)',    'unite' => 'litre', 'stock_actuel' => 10,  'seuil_critique' => 2,  'cout_unitaire' => 900],
            ['nom' => 'Sauce piment (kg)',            'unite' => 'kg',    'stock_actuel' => 3,   'seuil_critique' => 1,  'cout_unitaire' => 1200],
            ['nom' => 'Mayonnaise (kg)',            'unite' => 'kg',    'stock_actuel' => 2,   'seuil_critique' => 1,  'cout_unitaire' => 2000],
            ['nom' => 'Épices marinade (kg)',          'unite' => 'kg',    'stock_actuel' => 4,   'seuil_critique' => 1,  'cout_unitaire' => 3000],
            ['nom' => 'Ail (kg)',                    'unite' => 'kg',    'stock_actuel' => 2,   'seuil_critique' => 0.5,'cout_unitaire' => 2500],
            // Boissons / Ingrédients boissons
            ['nom' => 'Fleurs de bissap (kg)',       'unite' => 'kg',    'stock_actuel' => 3,   'seuil_critique' => 1,  'cout_unitaire' => 2000],
            ['nom' => 'Gingembre frais (kg)',       'unite' => 'kg',    'stock_actuel' => 2,   'seuil_critique' => 0.5,'cout_unitaire' => 1500],
            ['nom' => 'Eau minérale (carton 12)',   'unite' => 'carton','stock_actuel' => 5,   'seuil_critique' => 1,  'cout_unitaire' => 2500],
            ['nom' => 'Jus de fruit (litre)',        'unite' => 'litre', 'stock_actuel' => 8,   'seuil_critique' => 2,  'cout_unitaire' => 800],
            // Emballages
            ['nom' => 'Boîtes carton poulet',      'unite' => 'pièce', 'stock_actuel' => 120, 'seuil_critique' => 20, 'cout_unitaire' => 80],
            ['nom' => 'Sachets à emporter',        'unite' => 'pièce', 'stock_actuel' => 200, 'seuil_critique' => 30, 'cout_unitaire' => 40],
            ['nom' => 'Serviettes papier (paquet)', 'unite' => 'paquet','stock_actuel' => 10,  'seuil_critique' => 2,  'cout_unitaire' => 500],
            ['nom' => 'Gobelets (paquet 50)',      'unite' => 'paquet','stock_actuel' => 8,   'seuil_critique' => 2,  'cout_unitaire' => 800],
        ];

        foreach ($stock as $s) {
            Produit::create(array_merge(['actif' => true], $s));
        }
    }
}
