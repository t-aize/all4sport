<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `commande_produit` : les lignes de chaque commande, c'est-à-dire les
 * produits commandés avec leur quantité et leur prix. Table pivot d'une
 * relation plusieurs-à-plusieurs entre `commandes` et `produits`.
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('commande_produit', function (Blueprint $table) {
            $table->id();
            // Supprimer une commande supprime ses lignes.
            $table->foreignId('commande_id')->constrained()->cascadeOnDelete();
            // À l'inverse, on interdit de supprimer un produit déjà commandé.
            $table->string('reference_produit', 18);
            $table->foreign('reference_produit')->references('reference_produit')->on('produits')->restrictOnDelete();
            $table->unsignedInteger('quantite_commandee');
            // Prix au moment de la vente : il est copié ici pour que l'historique
            // reste exact même si le prix du produit change ensuite.
            $table->decimal('prix_unitaire_vente', 8, 2);
            $table->timestamps();

            // Un produit n'apparaît qu'une fois par commande (on en augmente la
            // quantité plutôt que d'ajouter une deuxième ligne).
            $table->unique(['commande_id', 'reference_produit']);
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_produit');
    }
};
