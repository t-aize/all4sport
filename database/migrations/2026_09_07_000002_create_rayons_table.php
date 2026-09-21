<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `rayons` : les rayons (sports) dans lesquels les produits sont classés
 * (football, tennis, running…). Un rayon correspond aussi à un sport pratiqué
 * par un client (voir la table `client_rayon`).
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('rayons', function (Blueprint $table) {
            $table->id();
            $table->string('nom_rayon', 30);
            $table->timestamps();
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('rayons');
    }
};
