<?php

namespace App\Http\Middleware;

use App\Enums\RoleEmploye;
use App\Models\Employe;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Réserve une route du back-office aux collaborateurs ayant l'un des rôles
 * indiqués.
 *
 * Le middleware est enregistré sous l'alias "role" (voir bootstrap/app.php).
 * On lui passe les rôles autorisés après les deux-points, séparés par des
 * virgules, avec le nom technique de l'enum RoleEmploye :
 *
 *     Route::post('commandes/{commande}/livraison', ...)
 *         ->middleware(['auth:employe', 'role:livreur']);
 *
 *     Route::get('commandes', ...)
 *         ->middleware(['auth:employe', 'role:operateur,vendeur']);
 *
 * Il doit toujours être placé APRÈS `auth:employe` : celui-ci redirige les
 * visiteurs non connectés vers la page de connexion, alors que ce middleware
 * répond "403 Interdit" à un employé connecté dont le rôle ne convient pas.
 */
class EnsureEmployeHasRole
{
    /**
     * Laisse passer la requête si l'employé connecté a l'un des rôles autorisés.
     *
     * Un nom de rôle inconnu dans la déclaration de la route (faute de frappe)
     * déclenche volontairement une erreur (ValueError) : mieux vaut un échec
     * visible pendant le développement qu'une route silencieusement ouverte ou
     * fermée à tout le monde.
     *
     * @param  Closure(Request): Response  $next
     * @param  string  ...$roles  Les valeurs de RoleEmploye autorisées.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $rolesAutorises = array_map(
            fn (string $role): RoleEmploye => RoleEmploye::from($role),
            $roles,
        );

        /** @var Employe|null $employe */
        $employe = $request->user('employe');

        abort_unless(
            $employe !== null && in_array($employe->role, $rolesAutorises, true),
            Response::HTTP_FORBIDDEN,
            "Votre rôle ne vous permet pas d'accéder à cette page.",
        );

        return $next($request);
    }
}
