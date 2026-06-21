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
        Schema::create('pertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // cuisinier
            $table->decimal('quantite', 10, 2);
            $table->decimal('cout_unitaire', 10, 0);
            $table->decimal('cout_total', 10, 0);
            $table->enum('motif', [
                'perime',
                'brulee',
                'tombe',
                'mauvaise_cuisson',
                'autre'
            ])->default('autre');
            $table->text('description')->nullable();
            $table->date('date_perte');
            $table->timestamps();
            // Note: crée automatiquement un stock_mouvement type='perte' via observer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertes');
    }
};
