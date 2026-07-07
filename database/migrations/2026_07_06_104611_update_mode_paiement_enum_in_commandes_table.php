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
        Schema::table('commandes', function (Blueprint $table) {
            //
            $table->enum('mode_paiement', ['especes', 'mobile_money'])
                ->default('especes')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            //
            $table->enum('mode_paiement', ['especes', 'mobile_money','carte'])
                ->default('especes')
                ->change();
        });
    }
};
