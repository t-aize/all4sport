<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Middleware d'Inertia, exécuté à chaque requête web (déclaré dans
 * bootstrap/app.php).
 *
 * Inertia relie le back-end Laravel au front-end React : au lieu de renvoyer du
 * HTML, les contrôleurs renvoient le nom d'un composant React et ses données
 * (« props »). Ce middleware définit la vue racine et les données partagées
 * avec TOUTES les pages.
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * Le modèle Blade chargé lors de la première visite d'une page
     * (resources/views/app.blade.php). Les visites suivantes se font en
     * JavaScript, sans recharger la page.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Version des fichiers front-end. Quand elle change (nouveau déploiement),
     * Inertia recharge entièrement la page pour éviter d'utiliser d'anciens
     * fichiers JavaScript.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Les données partagées par défaut avec toutes les pages React.
     *
     * Elles sont lisibles côté React avec `usePage().props` (voir
     * resources/js/pages/welcome.tsx). Comme `Client` et `Employe` masquent leur
     * mot de passe (attribut #[Hidden]), celui-ci n'est jamais envoyé au
     * navigateur.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            // Nom du site, défini par APP_NAME dans le fichier .env.
            'name' => config('app.name'),
            // Message de confirmation d'une action (« Produit créé. »…), envoyé
            // une seule fois par `->with('status', ...)` après une redirection.
            'status' => fn () => $request->session()->get('status'),
            'auth' => [
                // Le client connecté, ou null pour un visiteur.
                'user' => $request->user('web'),
                // Le collaborateur connecté au back-office, ou null. Les deux
                // espaces sont indépendants : un client n'est jamais un employé.
                'employe' => $request->user('employe'),
            ],
        ];
    }
}
