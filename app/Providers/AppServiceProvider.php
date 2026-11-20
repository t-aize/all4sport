<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

/**
 * Fournisseur de services principal de l'application.
 *
 * Un « service provider » est un point de démarrage : il enregistre des services
 * dans le conteneur d'injection de dépendances (`register`) puis configure le
 * comportement global de l'application au démarrage (`boot`).
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services de l'application dans le conteneur.
     *
     * Rien à enregistrer pour le moment.
     */
    public function register(): void
    {
        //
    }

    /**
     * Exécuté au démarrage de l'application, après l'enregistrement de tous
     * les services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();
    }

    /**
     * Définit les comportements par défaut adaptés à un site en production.
     */
    protected function configureDefaults(): void
    {
        // Toutes les dates manipulées par Laravel (Carbon) deviennent immuables :
        // `$date->addDay()` renvoie une NOUVELLE date au lieu de modifier
        // l'originale, ce qui évite des bugs difficiles à repérer.
        Date::use(CarbonImmutable::class);

        // En production, interdit les commandes dangereuses comme
        // `migrate:fresh` ou `db:wipe` qui effaceraient toute la base.
        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        // En production, toutes les URL générées utilisent HTTPS : les échanges
        // (formulaires, cookies de session) sont chiffrés après authentification.
        URL::forceHttps(app()->isProduction());

        // Règle de robustesse des mots de passe, utilisée partout via
        // `Password::defaults()`. En production : 12 caractères minimum avec
        // majuscules, minuscules, chiffres, symboles, et absent des fuites de
        // données connues. En développement : aucune contrainte pour faciliter
        // les tests.
        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Limite le nombre de requêtes sur l'API d'échange avec le Lot B :
     * 60 par minute et par adresse IP, pour se protéger des abus.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(60)->by((string) $request->ip()));
    }
}
