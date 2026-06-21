<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMouvement extends Model
{
    protected $fillable = [
        'produit_id', 'user_id', 'type',
        'quantite', 'stock_avant', 'stock_apres',
        'cout_total', 'motif', 'date_mouvement',
    ];

    protected $casts = ['date_mouvement' => 'date'];

    public function produit()      { return $this->belongsTo(Produit::class); }
    public function user()         { return $this->belongsTo(User::class); }

    public function getTypeLibelleAttribute(): string
    {
        return match($this->type) {
            'entree'     => 'Approvisionnement',
            'sortie'     => 'Fin de service',
            'inventaire' => 'Inventaire',
            'perte'      => 'Perte / Déchet',
            default      => $this->type,
        };
    }
}
