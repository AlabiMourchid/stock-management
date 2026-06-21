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
        Schema::create('stock_mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();    // qui a saisi
            $table->enum('type', ['entree', 'sortie', 'inventaire', 'perte']);
            // entree    = approvisionnement
            // sortie    = quantité enlevée en fin de service
            // inventaire= correction manuelle (remise à zéro)
            // perte     = déchet/jeté signalé par cuisinier
            $table->decimal('quantite', 10, 2);
            $table->decimal('stock_avant', 10, 2);   // snapshot avant mouvement
            $table->decimal('stock_apres', 10, 2);   // snapshot après mouvement
            $table->decimal('cout_total', 10, 0)->default(0); // pour rapport pertes
            $table->text('motif')->nullable();
            $table->date('date_mouvement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
