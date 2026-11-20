<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `historique_etats` : le journal des changements d'état de chaque
 * commande (transmise, validée, en préparation, expédiée, livrée ou retirée).
 *
 * Chaque ligne indique quel état a été atteint, quand, et par quel employé
 * (association ENREGISTRER). L'état actuel d'une commande est donc la ligne la
 * plus récente de son historique.
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('historique_etats', function (Blueprint $table) {
            $table->id();
            // Valeur de l'enum App\Enums\EtatCommande.
            $table->string('etat', 20);
            $table->dateTime('date_etat');
            // L'historique n'a pas de sens sans sa commande : il est supprimé avec elle.
            $table->foreignId('commande_id')->constrained()->cascadeOnDelete();
            // Facultatif : l'état "transmise" est enregistré automatiquement,
            // sans employé. Si l'employé est supprimé, on garde la trace.
            $table->foreignId('employe_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('historique_etats');
    }
};
