<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $fillable = ['nom', 'emoji', 'ordre'];

    public function produits()
    {
        return $this->hasMany(Menu::class);
    }
}
