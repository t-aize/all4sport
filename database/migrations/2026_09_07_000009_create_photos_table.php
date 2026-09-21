<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `photos` : les photos illustrant un produit (association ILLUSTRER).
 * Un produit peut en avoir plusieurs.
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->string('url_photo', 255);
            // La clé étrangère cible la référence du produit (et non un `id`),
            // il faut donc la déclarer à la main plutôt qu'avec `foreignId`.
            // `cascadeOnDelete` supprime les photos avec leur produit.
            $table->string('reference_produit', 18);
            $table->foreign('reference_produit')->references('reference_produit')->on('produits')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
