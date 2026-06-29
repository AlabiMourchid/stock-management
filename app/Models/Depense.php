<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    protected $fillable = [
        'user_id', 'libelle', 'categorie',
        'montant', 'date_depense', 'note',
    ];

    protected $casts = ['date_depense' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeAujourdhui($q)
    {
        return $q->whereDate('date_depense', today());
    }

    public function scopeSemaine($q)
    {
        return $q->whereBetween('date_depense', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeMois($q)
    {
        return $q->whereBetween('date_depense', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopePeriode($q, $d, $f)
    {
        return $q->whereBetween('date_depense', [$d, $f]);
    }
}
