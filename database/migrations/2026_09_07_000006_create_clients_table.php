<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `clients` : les clients du site marchand et des magasins.
 *
 * Elle sert aussi de table d'authentification : l'email et le mot de passe
 * permettent au client de se connecter (voir config/auth.php et le modèle
 * App\Models\Client).
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            // Code client unique, généré automatiquement par le modèle.
            $table->char('code_cli', 11)->unique();
            $table->string('nom', 30);
            $table->string('prenom', 30);
            // Identifiant de connexion, unique pour toute la table.
            $table->string('email', 80)->unique();
            $table->string('telephone', 15)->nullable();
            $table->string('adresse', 50);
            $table->string('code_postal', 5);
            $table->string('ville', 30);
            $table->date('date_naissance')->nullable();
            // Mise à jour par l'application à chaque nouvelle commande.
            $table->date('date_dernier_achat')->nullable();
            // Hash du mot de passe (le cast "hashed" du modèle le calcule).
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
