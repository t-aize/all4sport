<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `client_rayon` : les sports pratiqués par chaque client (association
 * PRATIQUER). Table pivot d'une relation plusieurs-à-plusieurs entre `clients`
 * et `rayons`.
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('client_rayon', function (Blueprint $table) {
            $table->id();
            // Si le client ou le rayon est supprimé, le lien disparaît avec lui.
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rayon_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Un client ne peut pas déclarer deux fois le même sport.
            $table->unique(['client_id', 'rayon_id']);
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_rayon');
    }
};
