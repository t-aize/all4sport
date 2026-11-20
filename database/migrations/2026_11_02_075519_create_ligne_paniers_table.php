<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `lignes_panier` : les produits placés dans un panier, avec leur quantité
 * et le prix figé au moment de l'ajout. Le produit est référencé par sa
 * `reference_produit` (clé primaire textuelle de la table `produits`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_panier', function (Blueprint $table) {
            $table->id();
            // Supprimer un panier supprime ses lignes.
            $table->foreignId('panier_id')->constrained('paniers')->cascadeOnDelete();
            // Supprimer un produit le retire aussi des paniers en cours.
            $table->string('reference_produit', 18);
            $table->foreign('reference_produit')->references('reference_produit')->on('produits')->cascadeOnDelete();
            $table->unsignedInteger('quantite')->default(1);
            $table->decimal('prix_unitaire', 8, 2); // prix figé au moment de l'ajout
            $table->timestamps();

            // Un produit n'apparaît qu'une fois par panier (on augmente la quantité).
            $table->unique(['panier_id', 'reference_produit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_panier');
    }
};
