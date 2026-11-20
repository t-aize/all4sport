<?php

/*
|--------------------------------------------------------------------------
| Commandes Artisan personnalisées
|--------------------------------------------------------------------------
|
| Commandes déclarées sous forme de closures, disponibles avec
| `php artisan <nom>`.
|
*/

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

// Génère un jeton aléatoire pour l'API d'échange avec le Lot B "Logistique".
// Le jeton est à copier dans LOGISTIQUE_API_TOKEN (.env) et à transmettre au
// Lot B par un canal sûr (jamais par email en clair).
Artisan::command('logistique:jeton', function (): void {
    $jeton = Str::random(64);

    $this->info('Jeton généré pour l\'API Logistique :');
    $this->line($jeton);
    $this->newLine();
    $this->comment("Ajoutez dans .env : LOGISTIQUE_API_TOKEN={$jeton}");
})->purpose("Génère un jeton pour l'API d'échange avec le Lot B Logistique");
