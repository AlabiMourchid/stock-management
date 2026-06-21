<?php

namespace App\Http\Controllers;

use App\Models\{Menu, Produit, Categorie, Fournisseur, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Gate, Hash};
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    // MENU
    public function menu() {
        $articles   = Menu::with('categorie')->orderBy('categorie_id')->orderBy('ordre')->get();
        $categories = Categorie::orderBy('ordre')->get();
        return view('admin.menu', compact('articles', 'categories'));
    }
    public function menuStore(Request $request) {
        $data = $request->validate(['categorie_id'=>'required|exists:categories,id','nom'=>'required|string|max:120','emoji'=>'nullable|string|max:10','prix_vente'=>'required|numeric|min:0','description'=>'nullable|string|max:500']);
        $data['disponible'] = $request->boolean('disponible', true);
        $data['actif'] = true;
        Menu::create($data);
        return redirect()->route('admin.menu')->with('success', "Article « {$data['nom']} » créé.");
    }
    public function menuUpdate(Request $request, Menu $article) {
        $data = $request->validate(['categorie_id'=>'required|exists:categories,id','nom'=>'required|string|max:120','emoji'=>'nullable|string|max:10','prix_vente'=>'required|numeric|min:0','description'=>'nullable|string|max:500']);
        $data['disponible'] = $request->boolean('disponible');
        $data['actif']      = $request->boolean('actif');
        $article->update($data);
        return redirect()->route('admin.menu')->with('success', "Article « {$article->nom} » mis à jour.");
    }
    public function menuDestroy(Menu $article) {
        $article->update(['actif'=>false,'disponible'=>false]);
        return redirect()->route('admin.menu')->with('success', "Article désactivé.");
    }

    // STOCK MP
    public function stock() {
        $produits     = Produit::where('actif',true)->orderBy('nom')->get();
        $fournisseurs = Fournisseur::where('actif',true)->orderBy('nom')->get();
        return view('admin.stock', compact('produits','fournisseurs'));
    }
    public function stockStore(Request $request) {
        $data = $request->validate(['nom'=>'required|string|max:120','emoji'=>'nullable|string|max:10','unite'=>'required|string|max:30','stock_actuel'=>'required|numeric|min:0','seuil_critique'=>'required|numeric|min:0','cout_unitaire'=>'required|numeric|min:0','fournisseur_id'=>'nullable|exists:fournisseurs,id']);
        $data['actif'] = true;
        Produit::create($data);
        return redirect()->route('admin.stock')->with('success', "« {$data['nom']} » créé.");
    }
    public function stockUpdate(Request $request, Produit $produit) {
        $data = $request->validate(['nom'=>'required|string|max:120','emoji'=>'nullable|string|max:10','unite'=>'required|string|max:30','seuil_critique'=>'required|numeric|min:0','cout_unitaire'=>'required|numeric|min:0','fournisseur_id'=>'nullable|exists:fournisseurs,id']);
        $data['actif'] = $request->boolean('actif');
        $produit->update($data);
        return redirect()->route('admin.stock')->with('success', "« {$produit->nom} » mis à jour.");
    }
    public function stockDestroy(Produit $produit) {
        $produit->update(['actif'=>false]);
        return redirect()->route('admin.stock')->with('success', "Désactivé.");
    }

    // UTILISATEURS
    public function utilisateurs() {
        $utilisateurs = User::orderBy('role')->orderBy('name')->get();
        return view('admin.utilisateurs', compact('utilisateurs'));
    }
    public function utilisateursStore(Request $request) {
        $data = $request->validate(['name'=>'required|string|max:100','email'=>'required|email|unique:users,email','password'=>['required',Password::min(8)],'role'=>'required|in:admin,caissier,cuisinier']);
        User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'role'=>$data['role'],'actif'=>true]);
        return redirect()->route('admin.utilisateurs')->with('success', "Utilisateur « {$data['name']} » créé.");
    }
    public function utilisateursUpdate(Request $request, User $user) {
        $data = $request->validate(['name'=>'required|string|max:100','email'=>'required|email|unique:users,email,'.$user->id,'role'=>'required|in:admin,caissier,cuisinier','password'=>['nullable',Password::min(8)]]);
        $update = ['name'=>$data['name'],'email'=>$data['email'],'role'=>$data['role'],'actif'=>$request->boolean('actif')];
        if (!empty($data['password'])) $update['password'] = Hash::make($data['password']);
        $user->update($update);
        return redirect()->route('admin.utilisateurs')->with('success', "Mis à jour.");
    }
    public function utilisateursDestroy(User $user) {
        if ($user->id === auth()->id()) return back()->with('error', 'Impossible de désactiver votre propre compte.');
        $user->update(['actif'=>false]);
        return redirect()->route('admin.utilisateurs')->with('success', "Désactivé.");
    }

    // FOURNISSEURS
    public function fournisseurs() {
        $fournisseurs = Fournisseur::orderBy('nom')->get();
        return view('admin.fournisseurs', compact('fournisseurs'));
    }
    public function fournisseursStore(Request $request) {
        $data = $request->validate(['nom'=>'required|string|max:120','telephone'=>'nullable|string|max:30','email'=>'nullable|email|max:120','adresse'=>'nullable|string|max:300']);
        Fournisseur::create(array_merge($data,['actif'=>true]));
        return redirect()->route('admin.fournisseurs')->with('success', "Fournisseur créé.");
    }
    public function fournisseursUpdate(Request $request, Fournisseur $fournisseur) {
        $data = $request->validate(['nom'=>'required|string|max:120','telephone'=>'nullable|string|max:30','email'=>'nullable|email|max:120','adresse'=>'nullable|string|max:300']);
        $data['actif'] = $request->boolean('actif');
        $fournisseur->update($data);
        return redirect()->route('admin.fournisseurs')->with('success', "Mis à jour.");
    }
    public function fournisseursDestroy(Fournisseur $fournisseur) {
        $fournisseur->update(['actif'=>false]);
        return redirect()->route('admin.fournisseurs')->with('success', "Désactivé.");
    }
}
