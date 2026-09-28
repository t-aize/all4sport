<?php

/*
|--------------------------------------------------------------------------
| Routes web
|--------------------------------------------------------------------------
|
| Une route associe une URL à une action. Ces routes sont chargées par
| bootstrap/app.php et passent par le groupe de middlewares « web » : session,
| cookies, protection CSRF, etc.
|
| Astuce : `php artisan route:list` affiche toutes les routes de l'application.
|
*/

use Illuminate\Support\Facades\Route;

// Page d'accueil. `Route::inertia` évite d'écrire un contrôleur quand la page
// n'a aucune logique côté serveur : elle affiche directement le composant React
// resources/js/pages/welcome.tsx. Le nom « home » permet de générer l'URL avec
// route('home') (PHP) plutôt que de l'écrire en dur.
Route::inertia('/', 'welcome')->name('home');

// Routes d'authentification (inscription, connexion, mot de passe oublié).
require __DIR__.'/auth.php';

// Routes du back-office réservées aux collaborateurs (préfixe /backoffice).
require __DIR__.'/backoffice.php';
