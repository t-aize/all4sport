<?php

/*
|--------------------------------------------------------------------------
| Point de démarrage de l'application
|--------------------------------------------------------------------------
|
| Ce fichier construit l'objet Application de Laravel. C'est ici que l'on
| déclare, en un seul endroit :
|   - les fichiers de routes utilisés par l'application ;
|   - les middlewares (filtres exécutés avant/après chaque requête) ;
|   - la gestion des exceptions (erreurs).
|
| Il est appelé par public/index.php (requêtes web) et par le fichier
| artisan (commandes en ligne de commande).
|
*/

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    // Routage : quel fichier répond à quelles URL.
    ->withRouting(
        // Routes des pages du site (voir routes/web.php).
        web: __DIR__.'/../routes/web.php',
        // Commandes artisan personnalisées (voir routes/console.php).
        commands: __DIR__.'/../routes/console.php',
        // URL de « santé » : renvoie 200 si l'application démarre correctement.
        // Utile pour la supervision et le déploiement (voir Laravel Cloud).
        health: '/up',
    )
    // Middlewares : chaque requête web traverse cette liste avant d'atteindre
    // le contrôleur, et la réponse la retraverse en sens inverse.
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            // Partage des données communes (utilisateur connecté, nom du site)
            // avec toutes les pages React. Voir HandleInertiaRequests.
            HandleInertiaRequests::class,
            // Ajoute des en-têtes HTTP pour que le navigateur précharge les
            // fichiers CSS/JS générés par Vite (pages plus rapides).
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Un visiteur non connecté qui tente d'accéder à une page protégée
        // (middleware « auth ») est renvoyé vers le formulaire de connexion.
        $middleware->redirectGuestsTo('/login');
    })
    // Exceptions : personnalise la manière dont les erreurs sont affichées.
    ->withExceptions(function (Exceptions $exceptions): void {
        // Renvoie les erreurs au format JSON (plutôt qu'une page HTML) pour
        // les URL d'API ou quand le client demande explicitement du JSON.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
