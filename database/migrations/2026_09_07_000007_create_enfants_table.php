<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `enfants` : les enfants rattachés au compte d'un client (association
 * AVOIR). Seule leur date de naissance est conservée, ce qui permet par exemple
 * de proposer des articles adaptés à leur âge.
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('enfants', function (Blueprint $table) {
            $table->id();
            $table->date('date_naissance');
            // Relation « un client a plusieurs enfants ». `cascadeOnDelete`
            // supprime automatiquement les enfants quand le client est supprimé.
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('enfants');
    }
};
