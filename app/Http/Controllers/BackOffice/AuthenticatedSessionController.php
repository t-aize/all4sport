<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackOffice\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gère la session d'un collaborateur : formulaire de connexion, connexion et
 * déconnexion du back-office.
 *
 * Routes associées : voir routes/backoffice.php.
 * Page React : resources/js/pages/backoffice/login.tsx.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Affiche le formulaire de connexion du back-office.
     */
    public function create(): Response
    {
        return Inertia::render('backoffice/login');
    }

    /**
     * Authentifie le collaborateur à partir des identifiants fournis.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Lance une ValidationException si l'identifiant ou le mot de passe est
        // incorrect, ou si trop de tentatives ont été faites.
        $request->authenticate();

        // Nouvel identifiant de session après connexion (protection contre la
        // "fixation de session").
        $request->session()->regenerate();

        return redirect()->intended(route('backoffice.dashboard'));
    }

    /**
     * Déconnecte le collaborateur.
     *
     * La session entière est détruite : si la même personne était aussi
     * connectée comme client dans ce navigateur, elle est déconnectée des deux
     * espaces, ce qui évite de laisser une session ouverte par inadvertance.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('employe')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('backoffice.login');
    }
}
