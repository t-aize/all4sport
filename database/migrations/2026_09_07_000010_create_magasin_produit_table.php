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
        Schema::create('magasin_produit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('magasin_id')->constrained()->cascadeOnDelete();
            $table->string('reference_produit', 18);
            $table->foreign('reference_produit')->references('reference_produit')->on('produits')->cascadeOnDelete();
            $table->unsignedInteger('quantite_stock_magasin')->default(0);
            $table->timestamps();

            $table->unique(['magasin_id', 'reference_produit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('magasin_produit');
    }
};
