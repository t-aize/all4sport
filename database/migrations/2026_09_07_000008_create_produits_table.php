<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `produits` : le catalogue d'articles vendus par ALL4SPORT.
 *
 * Particularité : la clé primaire n'est pas un identifiant numérique mais la
 * référence métier du produit (18 caractères : 3 lettres du fournisseur,
 * 3 lettres du rayon, puis un numéro sur 12 chiffres). Les tables `photos`,
 * `magasin_produit` et `commande_produit` s'y rattachent par cette référence.
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            // Clé primaire textuelle (et non auto-incrémentée).
            $table->string('reference_produit', 18)->primary();
            $table->string('description_produit', 500);
            // Prix d'achat HT et prix de vente : decimal(8, 2) = jusqu'à 8
            // chiffres dont 2 après la virgule (les décimaux évitent les erreurs
            // d'arrondi des nombres flottants pour les montants d'argent).
            $table->decimal('cout_unitaire_ht', 8, 2);
            $table->decimal('prix_vente', 8, 2);
            // Stock disponible pour la vente en ligne (celui des magasins est
            // dans la table `magasin_produit`). `unsigned` interdit les négatifs.
            $table->unsignedInteger('quantite_stock_internet')->default(0);
            // Emplacement dans l'entrepôt (ex : M2D3.5).
            $table->string('lieu_stockage', 15)->nullable();
            // `restrictOnDelete` : impossible de supprimer un fournisseur ou un
            // rayon tant que des produits y sont rattachés.
            $table->foreignId('fournisseur_id')->constrained()->restrictOnDelete();
            $table->foreignId('rayon_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
