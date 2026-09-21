<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `commandes` : les commandes passées par les clients (association
 * CHOISIR).
 *
 * Selon le mode de livraison, la commande est retirée dans un magasin (colonne
 * `magasin_id` remplie) ou expédiée à domicile depuis un entrepôt (colonnes
 * `entrepot_id` et `adresse_livraison` remplies, 10 € de frais).
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->date('date_commande');
            // Valeur de l'enum App\Enums\ModeLivraison.
            $table->string('mode_livraison', 20);
            $table->string('adresse_livraison', 100)->nullable();
            $table->decimal('frais_livraison', 4, 2)->default(0);
            $table->decimal('montant_total', 10, 2);
            // Le client qui commande : on interdit de supprimer un client qui a
            // déjà passé des commandes (elles servent à l'historique et à la
            // comptabilité).
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            // Les liens ci-dessous sont facultatifs : si l'employé, le magasin ou
            // l'entrepôt est supprimé, la commande est conservée et le lien
            // passe simplement à NULL (`nullOnDelete`).
            $table->foreignId('employe_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('magasin_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('entrepot_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
