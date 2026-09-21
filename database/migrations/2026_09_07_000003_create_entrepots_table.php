<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `entrepots` : les entrepôts logistiques qui approvisionnent les
 * magasins et expédient les commandes livrées à domicile.
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('entrepots', function (Blueprint $table) {
            $table->id();
            $table->string('ville', 30);
            // Zone géographique desservie pour les expéditions (facultative).
            $table->string('zone_expedition', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrepots');
    }
};
