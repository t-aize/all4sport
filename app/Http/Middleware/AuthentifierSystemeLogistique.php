<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentifie le système d'information du Lot B "Logistique" sur l'API
 * d'échange.
 *
 * - Le jeton partagé est défini par LOGISTIQUE_API_TOKEN (fichier .env, jamais
 *   versionné) et envoyé dans l'en-tête "Authorization: Bearer <jeton>".
 * - La comparaison se fait en temps constant (`hash_equals`) pour ne rien
 *   révéler par la durée de la réponse.
 * - En production, l'API refuse toute requête non chiffrée (HTTPS obligatoire,
 *   besoin "échange des données chiffré" du lot A).
 */
class AuthentifierSystemeLogistique
{
    /**
     * Laisse passer la requête si le jeton est valide.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isProduction() && ! $request->secure()) {
            return response()->json(['message' => 'HTTPS obligatoire.'], Response::HTTP_UPGRADE_REQUIRED);
        }

        $jetonAttendu = (string) config('services.logistique.token');
        $jetonRecu = (string) $request->bearerToken();

        if ($jetonAttendu === '' || $jetonRecu === '' || ! hash_equals($jetonAttendu, $jetonRecu)) {
            return response()->json(['message' => "Jeton d'API invalide ou absent."], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
