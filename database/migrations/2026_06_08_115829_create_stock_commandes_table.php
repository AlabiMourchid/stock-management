<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // caissier
            $table->string('numero')->unique();         // ex: CMD-20240115-0042
            $table->enum('statut', [
                'en_attente',   // saisie, en attente cuisine
                'en_preparation',
                'pret',         // prêt à servir
                'livre',        // servi / emporté
                'annule'
            ])->default('en_attente');
            $table->enum('type', ['sur_place', 'emporter'])->default('sur_place');
            $table->enum('mode_paiement', ['especes', 'mobile_money', 'carte'])->default('especes');
            $table->decimal('total_ttc', 10, 0)->default(0);
            $table->decimal('montant_recu', 10, 0)->nullable();     // espèces reçues
            $table->decimal('monnaie_rendue', 10, 0)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('pret_a')->nullable();    // heure où marqué "prêt"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_commandes');
    }
};
