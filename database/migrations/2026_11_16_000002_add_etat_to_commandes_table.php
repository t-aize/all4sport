<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute l'état courant de la commande. Il recopie le dernier état de
 * l'historique (table `historique_etats`) pour filtrer et compter rapidement
 * les commandes par état dans le back-office, sans parcourir l'historique.
 */
return new class extends Migration
{
    /**
     * Applique la migration.
     */
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            // Valeur de l'enum App\Enums\EtatCommande.
            $table->string('etat', 20)->default('transmise')->after('montant_total')->index();
        });
    }

    /**
     * Annule la migration.
     */
    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropIndex(['etat']);
            $table->dropColumn('etat');
        });
    }
};
