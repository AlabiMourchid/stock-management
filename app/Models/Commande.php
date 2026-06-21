<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $fillable = [
        'user_id', 'numero', 'statut', 'type', 'mode_paiement',
        'total_ttc', 'montant_recu', 'monnaie_rendue', 'notes', 'pret_a',
    ];

    protected $casts = ['pret_a' => 'datetime'];

    public function user()   { return $this->belongsTo(User::class); }
    public function lignes() { return $this->hasMany(LigneCommande::class)->with('menu'); }

    // Génère le numéro unique de commande
    protected static function booted(): void
    {
        static::creating(function (Commande $c) {
            $count = static::whereDate('created_at', today())->count() + 1;
            $c->numero = 'CMD-' . now()->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        });
    }

    public function scopeAujourdhui($q)   { return $q->whereDate('created_at', today()); }
    public function scopeActives($q)      { return $q->whereNotIn('statut', ['annule', 'livre']); }

    public function getStatutLibelleAttribute(): string
    {
        return match($this->statut) {
            'en_attente'      => 'En attente',
            'en_preparation'  => 'En préparation',
            'pret'            => 'Prêt',
            'livre'           => 'Livré',
            'annule'          => 'Annulé',
            default           => $this->statut,
        };
    }

    public function recalculerTotal(): void
    {
        $this->update(['total_ttc' => $this->lignes->sum('sous_total')]);
    }
}
