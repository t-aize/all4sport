<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Première étape du « mot de passe oublié » : le client saisit son email et
 * reçoit un lien de réinitialisation.
 *
 * La deuxième étape (choix du nouveau mot de passe) est dans
 * NewPasswordController. Page React : resources/js/pages/auth/forgot-password.tsx.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Affiche le formulaire de demande de réinitialisation du mot de passe.
     */
    public function create(): Response
    {
        return Inertia::render('auth/forgot-password', [
            'status' => session('status'),
        ]);
    }

    /**
     * Envoie un lien de réinitialisation du mot de passe au client.
     */
    public function store(Request $request): RedirectResponse
    {
        // `validate` vérifie les données de la requête. En cas d'erreur, Laravel
        // renvoie automatiquement le visiteur au formulaire avec les messages.
        // Le second tableau remplace les messages anglais par des messages français.
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => "L'adresse email n'est pas valide.",
        ]);

        // Le « broker » de mots de passe de Laravel crée un jeton temporaire,
        // l'enregistre (table password_reset_tokens) et envoie l'email.
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Un lien de réinitialisation de mot de passe vous a été envoyé par email.')
            : back()->withErrors(['email' => $this->message($status)]);
    }

    /**
     * Traduit le statut renvoyé par le broker de mots de passe.
     *
     * `match` est comme un `switch` qui renvoie directement une valeur.
     */
    private function message(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => "Aucun compte n'est associé à cette adresse email.",
            Password::RESET_THROTTLED => 'Veuillez patienter avant de demander un nouveau lien.',
            default => "Impossible d'envoyer le lien de réinitialisation.",
        };
    }
}
