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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->date('date_commande');
            $table->string('mode_livraison', 20);
            $table->string('adresse_livraison', 100)->nullable();
            $table->decimal('frais_livraison', 4, 2)->default(0);
            $table->decimal('montant_total', 10, 2);
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('employe_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('magasin_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('entrepot_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
