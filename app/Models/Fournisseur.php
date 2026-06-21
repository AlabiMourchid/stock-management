<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    protected $fillable = ['nom', 'telephone', 'email', 'adresse', 'actif'];

    public function mouvements() { return $this->hasMany(StockMouvement::class); }
}
