<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ClotureCaisse extends Model
{

    protected $table = 'clotures_caisse';
    protected $fillable = [
        'user_id', 'date_service',
        'ca_especes', 'ca_mobile_money', 'ca_carte', 'ca_total',
        'nb_commandes', 'fond_caisse_ouverture', 'fond_caisse_cloture',
        'ecart_caisse', 'observations', 'est_cloturee', 'clôturee_a',
    ];

    protected $casts = [
        'date_service'  => 'date',
        'est_cloturee'  => 'boolean',
        'clôturee_a'    => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
