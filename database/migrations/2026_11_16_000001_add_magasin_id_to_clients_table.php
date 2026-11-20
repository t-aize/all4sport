<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute le magasin par défaut du client (dossier de contexte, "Workflow de
 * gestion" : préciser le compte client avec choix d'un magasin par défaut).
 * Il est proposé en premier pour le retrait en magasin et détermine l'entrepôt
 * d'expédition des livraisons à domicile.
 */
return new class extends Migration
{
    /**
     * Applique la migration.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Facultatif ; si le magasin ferme, le client garde son compte.
            $table->foreignId('magasin_id')->nullable()->after('ville')->constrained()->nullOnDelete();
        });
    }

    /**
     * Annule la migration.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('magasin_id');
        });
    }
};
