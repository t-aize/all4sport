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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 30);
            $table->string('prenom', 30);
            $table->string('email', 80)->unique();
            $table->string('telephone', 15)->nullable();
            $table->string('adresse', 50);
            $table->string('code_postal', 5);
            $table->string('ville', 30);
            $table->date('date_naissance')->nullable();
            $table->date('date_dernier_achat')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
