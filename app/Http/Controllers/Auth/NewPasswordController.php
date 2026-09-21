<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Seconde étape du « mot de passe oublié » : le client arrive depuis le lien
 * reçu par email et choisit un nouveau mot de passe.
 *
 * Page React : resources/js/pages/auth/reset-password.tsx.
 */
class NewPasswordController extends Controller
{
    /**
     * Affiche le formulaire de réinitialisation du mot de passe.
     *
     * Le jeton (dans l'URL) et l'email (dans la query string) sont transmis à
     * la page pour être renvoyés tels quels lors de l'envoi du formulaire.
     */
    public function create(Request $request, string $token): Response
    {
        return Inertia::render('auth/reset-password', [
            'email' => $request->string('email'),
            'token' => $token,
        ]);
    }

    /**
     * Réinitialise le mot de passe du client.
     */
    public function store(Request $request): RedirectResponse
    {
        // `confirmed` exige un champ `password_confirmation` identique.
        // `PasswordRule::defaults()` applique la règle de robustesse définie
        // dans AppServiceProvider (plus stricte en production).
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ], [
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => "L'adresse email n'est pas valide.",
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        // Le broker vérifie que le jeton correspond bien à l'email, puis appelle
        // la fonction ci-dessous si tout est valide.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Client $client) use ($request): void {
                // Le mot de passe est haché automatiquement par le cast
                // « hashed » du modèle Client. Le « remember token » est
                // régénéré pour déconnecter les autres appareils.
                $client->forceFill([
                    'password' => $request->password,
                ])->setRememberToken(Str::random(60));

                $client->save();

                event(new PasswordReset($client));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Votre mot de passe a été réinitialisé.')
            : back()->withErrors(['email' => $this->message($status)]);
    }

    /**
     * Traduit le statut renvoyé par le broker de mots de passe.
     */
    private function message(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => "Aucun compte n'est associé à cette adresse email.",
            Password::INVALID_TOKEN => "Ce lien de réinitialisation n'est plus valide.",
            default => 'Impossible de réinitialiser le mot de passe.',
        };
    }
}
