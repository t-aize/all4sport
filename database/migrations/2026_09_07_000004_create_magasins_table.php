<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `magasins` : les magasins physiques ALL4SPORT. Chaque magasin est
 * approvisionné par un entrepôt (association DESSERVIR).
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('magasins', function (Blueprint $table) {
            $table->id();
            $table->string('ville', 30);
            $table->string('adresse', 60);
            // Clé étrangère vers `entrepots`. `restrictOnDelete` interdit de
            // supprimer un entrepôt tant qu'il dessert encore un magasin.
            $table->foreignId('entrepot_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('magasins');
    }
};
