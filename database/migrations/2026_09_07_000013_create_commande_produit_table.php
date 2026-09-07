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
        Schema::create('commande_produit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained()->cascadeOnDelete();
            $table->string('reference_produit', 18);
            $table->foreign('reference_produit')->references('reference_produit')->on('produits')->restrictOnDelete();
            $table->unsignedInteger('quantite_commandee');
            $table->decimal('prix_unitaire_vente', 8, 2);
            $table->timestamps();

            $table->unique(['commande_id', 'reference_produit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_produit');
    }
};
