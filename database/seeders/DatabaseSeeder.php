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
        //User::create(['name' => 'Caissier Test',   'email' => 'caissier@amira.com',  'password' => Hash::make('password'), 'role' => 'caissier']);

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
            // Entrées
            ['categorie_id' => 1, 'nom' => 'Samoussa 5 pièces + sauce soja', 'prix_vente' => 2500, 'ordre' => 1],
            ['categorie_id' => 1, 'nom' => 'Salade by Amira',    'prix_vente' => 3000, 'ordre' => 2],
            ['categorie_id' => 1, 'nom' => 'Douceurs de alloco',    'prix_vente' => 2000, 'ordre' => 3],

            // Entrées
            ['categorie_id' => 2, 'nom' => 'Pilons braisé', 'prix_vente' => 3500, 'ordre' => 1],
            ['categorie_id' => 2, 'nom' => 'Pilons braisé XL',    'prix_vente' => 4500, 'ordre' => 2],
            ['categorie_id' => 2, 'nom' => 'Butter chicken',    'prix_vente' => 4000, 'ordre' => 3],
            ['categorie_id' => 2, 'nom' => 'Butter chicken XL',    'prix_vente' => 6000, 'ordre' => 4],
            ['categorie_id' => 2, 'nom' => 'Poulet Mayo',    'prix_vente' => 3500, 'ordre' => 5],
            ['categorie_id' => 2, 'nom' => 'Poulet Mayo XL',    'prix_vente' => 5000, 'ordre' => 6],
            ['categorie_id' => 2, 'nom' => 'Poulet Mayo XXL',    'prix_vente' => 7000, 'ordre' => 7],
            ['categorie_id' => 2, 'nom' => 'Poulets frits',    'prix_vente' => 3000, 'ordre' => 8],
            ['categorie_id' => 2, 'nom' => 'Poulets frits XL',    'prix_vente' => 4000, 'ordre' => 9],
            ['categorie_id' => 2, 'nom' => 'Poulet Choukouya',    'prix_vente' => 3000, 'ordre' => 10],
            ['categorie_id' => 2, 'nom' => 'Poulet Choukouya XL',    'prix_vente' => 4500, 'ordre' => 11],
            ['categorie_id' => 2, 'nom' => 'Poulet Choukouya XXL',    'prix_vente' => 6000, 'ordre' => 12],
            ['categorie_id' => 2, 'nom' => 'Poulets caramélisé',    'prix_vente' => 4000, 'ordre' => 13],
            ['categorie_id' => 2, 'nom' => 'Poulets caramélisé XL',    'prix_vente' => 6000, 'ordre' => 14],

            // Pépites
            ['categorie_id' => 4, 'nom' => 'Les nouilles level 1',    'prix_vente' => 2500, 'ordre' => 1],
            ['categorie_id' => 4, 'nom' => 'Les nouilles level 2',         'prix_vente' => 3500, 'ordre' => 2],
            ['categorie_id' => 4, 'nom' => 'Les nouilles level 3',          'prix_vente' => 5000, 'ordre' => 3],
            ['categorie_id' => 4, 'nom' => 'Piron rouge',      'prix_vente' => 3000, 'ordre' => 4],
            ['categorie_id' => 4, 'nom' => 'Piron rouge XL',      'prix_vente' => 5000, 'ordre' => 5],
            ['categorie_id' => 4, 'nom' => 'Macaroni au fromage',      'prix_vente' => 5000, 'ordre' => 6],
            ['categorie_id' => 4, 'nom' => "Pizza d'alloco",      'prix_vente' => 10000, 'ordre' => 7],

            // SNACKS
            ['categorie_id' => 5, 'nom' => "Hamburger basique au poulet",      'prix_vente' => 2000, 'ordre' => 1],
            ['categorie_id' => 5, 'nom' => "Plat de hamburger",      'prix_vente' => 3500, 'ordre' => 2],
            ['categorie_id' => 5, 'nom' => "2 medi burger",      'prix_vente' => 2000, 'ordre' => 3],
            ['categorie_id' => 5, 'nom' => "5 medi burger",      'prix_vente' => 4000, 'ordre' => 4],
            ['categorie_id' => 5, 'nom' => "Sandwich basique au poulet",      'prix_vente' => 1500, 'ordre' => 5],
            ['categorie_id' => 5, 'nom' => "Sandwich medium au poulet",      'prix_vente' => 2000, 'ordre' => 6],
            ['categorie_id' => 5, 'nom' => "Sandwich chargé",      'prix_vente' => 3000, 'ordre' => 7],
            ['categorie_id' => 5, 'nom' => "Sandwich chargé XL",      'prix_vente' => 4000, 'ordre' => 8],
            ['categorie_id' => 5, 'nom' => "Sandwich Auriol",      'prix_vente' => 3000, 'ordre' => 9],
            ['categorie_id' => 5, 'nom' => "Sandwich Auriol XL",      'prix_vente' => 4000, 'ordre' => 10],
            ['categorie_id' => 5, 'nom' => "Shawarma poulet",      'prix_vente' => 2000, 'ordre' => 11],
            ['categorie_id' => 5, 'nom' => "Shawarma poulet XL",      'prix_vente' => 3000, 'ordre' => 12],
            ['categorie_id' => 5, 'nom' => "Tacos au poulet",      'prix_vente' => 3000, 'ordre' => 13],
            ['categorie_id' => 5, 'nom' => "Tacos au poulet",      'prix_vente' => 4000, 'ordre' => 14],
            // Accompagnements
            ['categorie_id' => 3, 'nom' => 'Frites',               'prix_vente' => 1000,  'ordre' => 1],
            ['categorie_id' => 3, 'nom' => 'Riz blanc ou gras',            'prix_vente' => 1000,  'ordre' => 2],
            ['categorie_id' => 3, 'nom' => 'Alloco',       'prix_vente' => 1000,  'ordre' => 3],
            ['categorie_id' => 3, 'nom' => 'Banane sautée',          'prix_vente' => 1000,  'ordre' => 4],
            ['categorie_id' => 3, 'nom' => 'Attieke',        'prix_vente' => 1000,  'ordre' => 5],
            ['categorie_id' => 3, 'nom' => 'Couscous au beurre',        'prix_vente' => 1000,  'ordre' => 6],
            ['categorie_id' => 3, 'nom' => 'Pommes sautées',        'prix_vente' => 1000,  'ordre' => 7],
            // Boxes
            ['categorie_id' => 8, 'nom' => 'Box Solo', 'prix_vente' => 3600,'ordre' => 1],
            ['categorie_id' => 8, 'nom' => 'Box Proteinée',              'prix_vente' => 7500, 'ordre' => 2],
            ['categorie_id' => 8, 'nom' => 'Big box',             'prix_vente' => 5000, 'ordre' => 3],
            ['categorie_id' => 8, 'nom' => 'Box Dosée', 'prix_vente' => 8000,'ordre' => 4],
            ['categorie_id' => 8, 'nom' => 'Formule 1',              'prix_vente' => 9500, 'ordre' => 5],
            ['categorie_id' => 8, 'nom' => 'Formule 2',             'prix_vente' => 11000, 'ordre' => 6],
            // Desserts
            ['categorie_id' => 6, 'nom' => 'Crêpes natures',         'prix_vente' => 1500,  'ordre' => 1],
            ['categorie_id' => 6, 'nom' => 'Crêpes au chocolat',         'prix_vente' => 2000,  'ordre' => 2],
            ['categorie_id' => 6, 'nom' => 'Crêpes au chocolat + glace',         'prix_vente' => 2500,  'ordre' => 3],
            ['categorie_id' => 6, 'nom' => 'Crêpes nature + glace + banane',         'prix_vente' => 2500,  'ordre' => 4],
            ['categorie_id' => 6, 'nom' => 'Mousse au chocolat',         'prix_vente' => 1000,  'ordre' => 5],
            ['categorie_id' => 6, 'nom' => 'Bowl de Yaourt au Granola',         'prix_vente' => 2500,  'ordre' => 6],
            ['categorie_id' => 6, 'nom' => 'Pain perdu + mousse au chocolat',         'prix_vente' => 2000,  'ordre' => 7],
            // Boissons
            ['categorie_id' => 7, 'nom' => 'Carafe de bissap',   'prix_vente' => 1500,  'ordre' => 1],
            ['categorie_id' => 7, 'nom' => 'Eau',     'prix_vente' => 1000,  'ordre' => 2],
            ['categorie_id' => 7, 'nom' => 'Verre de bissap',           'prix_vente' => 500,  'ordre' => 3],
            ['categorie_id' => 7, 'nom' => 'Bierre',   'prix_vente' => 1000,  'ordre' => 4],
            ['categorie_id' => 7, 'nom' => 'Boissons gazeuses',     'prix_vente' => 1000,  'ordre' => 5],
            ['categorie_id' => 7, 'nom' => 'Jus naturel',           'prix_vente' => 600,  'ordre' => 6],
            ['categorie_id' => 7, 'nom' => 'Boissons chaude',   'prix_vente' => 1500,  'ordre' => 7],
            ['categorie_id' => 7, 'nom' => 'Milkshake chocolat',     'prix_vente' => 3000,  'ordre' => 8],
            ['categorie_id' => 7, 'nom' => 'Iced matcha latte',           'prix_vente' => 3000,  'ordre' => 9],
            ['categorie_id' => 7, 'nom' => 'Iced latte / Iced capuccino',   'prix_vente' => 2000,  'ordre' => 10],
            ['categorie_id' => 7, 'nom' => 'Milkshake chocolat alcoolisée',     'prix_vente' => 3000,  'ordre' => 11],
            ['categorie_id' => 7, 'nom' => 'Protein shake',           'prix_vente' => 2000,  'ordre' => 12],
            ['categorie_id' => 7, 'nom' => 'Blue Lagoon',   'prix_vente' => 2000,  'ordre' => 13],
            ['categorie_id' => 7, 'nom' => 'Mojito',     'prix_vente' => 2000,  'ordre' => 14],
            ['categorie_id' => 7, 'nom' => 'Sunset',           'prix_vente' => 2000,  'ordre' => 15],
            ['categorie_id' => 7, 'nom' => 'Restsobre',   'prix_vente' => 1500,  'ordre' => 16],
            ['categorie_id' => 7, 'nom' => 'Apple vodka',     'prix_vente' => 2000,  'ordre' => 17],
        ];


        foreach ($menu as $m) {
            Menu::create(array_merge(['disponible' => true, 'actif' => true], $m));
        }

        // ================================================================
        //  PRODUITS STOCK = Matières premières achetées
        //  Ce sont les éléments physiques gérés en inventaire
        // ================================================================
        /*$stock = [
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
        }*/
    }
}
