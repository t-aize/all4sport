<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `magasin_produit` : le stock de chaque produit dans chaque magasin
 * (association STOCKER).
 *
 * C'est une « table pivot » : elle relie deux tables dans une relation
 * plusieurs-à-plusieurs (un magasin stocke plusieurs produits, un produit est
 * stocké dans plusieurs magasins) et porte une information propre au couple :
 * la quantité en stock.
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('magasin_produit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('magasin_id')->constrained()->cascadeOnDelete();
            $table->string('reference_produit', 18);
            $table->foreign('reference_produit')->references('reference_produit')->on('produits')->cascadeOnDelete();
            $table->unsignedInteger('quantite_stock_magasin')->default(0);
            $table->timestamps();

            // Un même produit ne peut apparaître qu'une fois par magasin : pour
            // changer le stock, on modifie la ligne existante.
            $table->unique(['magasin_id', 'reference_produit']);
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('magasin_produit');
    }
};
