<?php

/*
|--------------------------------------------------------------------------
| Commandes console
|--------------------------------------------------------------------------
|
| Ce fichier permet de déclarer des commandes artisan sous forme de simples
| fonctions (closures). Il est chargé par bootstrap/app.php.
|
| Exemple fourni par Laravel : `php artisan inspire` affiche une citation.
| Pour une commande plus complexe, on crée plutôt une classe avec
| `php artisan make:command`.
|
*/

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
