<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `employes` : les collaborateurs d'ALL4SPORT qui se connectent au
 * back-office (opérateur, vendeur, livreur, service client).
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 30);
            $table->string('prenom', 30);
            // Identifiant saisi pour se connecter ; `unique` interdit les doublons.
            $table->string('identifiant_connexion', 20)->unique();
            // Contient le hash du mot de passe (jamais le mot de passe en clair),
            // d'où une longueur libre.
            $table->string('password');
            // Valeur de l'enum App\Enums\RoleEmploye (ex : "livreur").
            $table->string('role', 20);
            // Jeton de la case "Se souvenir de moi".
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};
