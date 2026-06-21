<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LigneCommande extends Model
{
    protected $fillable = ['commande_id', 'menu_id', 'quantite', 'prix_unitaire', 'sous_total'];

    public function commande() { return $this->belongsTo(Commande::class); }
    public function menu()  { return $this->belongsTo(Menu::class, 'menu_id'); }
}
