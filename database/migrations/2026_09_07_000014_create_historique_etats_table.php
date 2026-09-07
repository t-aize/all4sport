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
        Schema::create('historique_etats', function (Blueprint $table) {
            $table->id();
            $table->string('etat', 20);
            $table->dateTime('date_etat');
            $table->foreignId('commande_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employe_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historique_etats');
    }
};
