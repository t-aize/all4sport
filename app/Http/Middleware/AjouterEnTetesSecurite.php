<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ajoute les en-têtes HTTP de sécurité à chaque réponse (besoin "Sécurité du
 * SI" du lot A) :
 * - HSTS (HTTPS uniquement) : le navigateur n'utilisera plus que HTTPS ;
 * - X-Frame-Options : interdit d'afficher le site dans une iframe (clickjacking) ;
 * - X-Content-Type-Options : interdit au navigateur de deviner le type MIME ;
 * - Referrer-Policy : limite les informations envoyées aux autres sites ;
 * - Permissions-Policy : désactive caméra, micro et géolocalisation.
 */
class AjouterEnTetesSecurite
{
    /**
     * Durée de validité de HSTS : un an, en secondes.
     */
    private const DUREE_HSTS = 31536000;

    /**
     * Ajoute les en-têtes à la réponse.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age='.self::DUREE_HSTS.'; includeSubDomains');
        }

        return $response;
    }
}
