<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';

    protected $fillable = [
        'categorie_id', 'nom', 'prix_vente',
        'description', 'disponible', 'actif', 'ordre',
    ];

    protected $casts = [
        'disponible' => 'boolean',
        'actif' => 'boolean',
    ];

    // ---- Relations ----
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function lignesCommande()
    {
        return $this->hasMany(LigneCommande::class, 'menu_id');
    }

    // ---- Scopes ----
    public function scopeActif($q)
    {
        return $q->where('actif', true);
    }

    public function scopeDisponible($q)
    {
        return $q->where('disponible', true)->where('actif', true);
    }
}
