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
        Schema::create('produits', function (Blueprint $table) {
            $table->string('reference_produit', 18)->primary();
            $table->string('description_produit', 500);
            $table->decimal('cout_unitaire_ht', 8, 2);
            $table->decimal('prix_vente', 8, 2);
            $table->unsignedInteger('quantite_stock_internet')->default(0);
            $table->string('lieu_stockage', 15)->nullable();
            $table->foreignId('fournisseur_id')->constrained()->restrictOnDelete();
            $table->foreignId('rayon_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
