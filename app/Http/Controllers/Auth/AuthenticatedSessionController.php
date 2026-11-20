<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ServicePanier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gère la session d'un client : affichage du formulaire de connexion,
 * connexion et déconnexion.
 *
 * Routes associées : voir routes/auth.php (login, logout).
 * Page React : resources/js/pages/auth/login.tsx.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     *
     * `Inertia::render` ne renvoie pas du HTML : il indique quel composant React
     * afficher (resources/js/pages/auth/login.tsx) et lui transmet des données
     * (les « props »). Ici, `status` contient un éventuel message flash, par
     * exemple « Votre mot de passe a été réinitialisé ».
     */
    public function create(): Response
    {
        return Inertia::render('auth/login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Authentifie le client à partir des identifiants fournis.
     *
     * La validation des champs, la limitation des tentatives et la vérification
     * du mot de passe sont déléguées à LoginRequest (un « Form Request »), ce qui
     * garde le contrôleur court et lisible.
     */
    public function store(LoginRequest $request, ServicePanier $servicePanier): RedirectResponse
    {
        // Identifiant de session du visiteur, lu AVANT sa régénération : il sert
        // à retrouver le panier rempli avant la connexion pour le fusionner.
        $ancienneSession = $request->session()->getId();

        // Lance une ValidationException (donc renvoie les erreurs au
        // formulaire) si l'email ou le mot de passe est incorrect.
        $request->authenticate();

        // Nouvel identifiant de session après connexion : protège contre la
        // « fixation de session » (un attaquant qui aurait imposé un ancien
        // identifiant à la victime).
        $request->session()->regenerate();

        // Le panier du visiteur rejoint celui du compte client.
        $servicePanier->fusionnerPanierInvite($ancienneSession, (int) Auth::guard('web')->id());

        // `intended` renvoie le client vers la page qu'il voulait consulter
        // avant d'être redirigé vers /login, ou vers l'accueil à défaut.
        return redirect()->intended(route('home'));
    }

    /**
     * Déconnecte le client actuellement authentifié.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // On détruit la session et on change le jeton CSRF pour qu'aucune donnée
        // de l'ancienne session ne puisse être réutilisée.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
