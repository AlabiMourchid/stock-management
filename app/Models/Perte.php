<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Perte extends Model
{
    protected $fillable = [
        'produit_id', 'user_id', 'quantite', 'cout_unitaire',
        'cout_total', 'motif', 'description', 'date_perte',
    ];

    protected $casts = ['date_perte' => 'date'];

    public function produit() { return $this->belongsTo(Produit::class); }
    public function user()    { return $this->belongsTo(User::class); }

    // Crée automatiquement le mouvement de stock associé
    protected static function booted(): void
    {
        static::created(function (Perte $perte) {
            $produit = $perte->produit;
            StockMouvement::create([
                'produit_id'      => $perte->produit_id,
                'user_id'         => $perte->user_id,
                'type'            => 'perte',
                'quantite'        => $perte->quantite,
                'stock_avant'     => $produit->stock_actuel,
                'stock_apres'     => $produit->stock_actuel - $perte->quantite,
                'cout_total'      => $perte->cout_total,
                'motif'           => $perte->motif . ($perte->description ? ': ' . $perte->description : ''),
                'date_mouvement'  => $perte->date_perte,
            ]);
            $produit->decrement('stock_actuel', $perte->quantite);
        });
    }

    public function getMotifLibelleAttribute(): string
    {
        return match($this->motif) {
            'perime'           => 'Périmé',
            'brulee'           => 'Brûlé',
            'tombe'            => 'Tombé / abîmé',
            'mauvaise_cuisson' => 'Mauvaise cuisson',
            'autre'            => 'Autre',
            default            => $this->motif,
        };
    }
}
