<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $fillable = [
        'nom', 'cout_unitaire',
        'unite', 'stock_actuel', 'seuil_critique', 'actif',
    ];

    protected $casts = ['actif' => 'boolean'];

    // Relations
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function mouvements()
    {
        return $this->hasMany(StockMouvement::class);
    }

    public function lignesCommande()
    {
        return $this->hasMany(LigneCommande::class);
    }

    public function pertes()
    {
        return $this->hasMany(Perte::class);
    }

    // Scopes
    public function scopeActif($q)
    {
        return $q->where('actif', true);
    }

    public function scopeVisiblePos($q)
    {
        return $q->where('visible_pos', true)->where('actif', true);
    }

    public function scopeStockCritique($q)
    {
        return $q->whereColumn('stock_actuel', '<=', 'seuil_critique');
    }

    // Accessors
    public function getStatutStockAttribute(): string
    {
        if ($this->stock_actuel <= $this->seuil_critique) return 'critique';
        if ($this->stock_actuel <= $this->seuil_critique * 2) return 'moyen';
        return 'ok';
    }

    public function getPourcentageStockAttribute(): float
    {
        $max = $this->seuil_critique * 4; // plein = 4× le seuil
        return min(100, round(($this->stock_actuel / max(1, $max)) * 100));
    }
}
