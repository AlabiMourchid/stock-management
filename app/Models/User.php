<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'actif'];
    protected $hidden   = ['password', 'remember_token'];

    // Relations
    public function commandes()    { return $this->hasMany(Commande::class); }
    public function mouvements()   { return $this->hasMany(StockMouvement::class); }
    public function clotures()     { return $this->hasMany(ClotureCaisse::class); }
    public function pertes()       { return $this->hasMany(Perte::class); }

    // Helpers
    public function isAdmin()      { return $this->role === 'admin'; }
    public function isCaissier()   { return $this->role === 'caissier'; }
    public function isCuisinier()  { return $this->role === 'cuisinier'; }
}

