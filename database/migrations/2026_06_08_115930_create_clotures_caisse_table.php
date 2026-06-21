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
        Schema::create('clotures_caisse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // caissier
            $table->date('date_service');
            $table->decimal('ca_especes', 10, 0)->default(0);
            $table->decimal('ca_mobile_money', 10, 0)->default(0);
            $table->decimal('ca_carte', 10, 0)->default(0);
            $table->decimal('ca_total', 10, 0)->default(0);
            $table->integer('nb_commandes')->default(0);
            $table->decimal('fond_caisse_ouverture', 10, 0)->default(0);
            $table->decimal('fond_caisse_cloture', 10, 0)->nullable();
            $table->decimal('ecart_caisse', 10, 0)->nullable();     // réel - attendu
            $table->text('observations')->nullable();
            $table->boolean('est_cloturee')->default(false);
            $table->timestamp('clôturee_a')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clotures_caisse');
    }
};
