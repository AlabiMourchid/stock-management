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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->decimal('cout_unitaire', 10, 0)->default(0); // coût d'achat pour calcul pertes
            $table->string('unite')->default('pièce');      // pièce, kg, litre, sachet…
            $table->decimal('stock_actuel', 10, 2)->default(0);
            $table->decimal('seuil_critique', 10, 2)->default(5);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
