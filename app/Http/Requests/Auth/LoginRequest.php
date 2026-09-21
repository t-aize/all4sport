<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Requête de connexion d'un client.
 *
 * Un « Form Request » regroupe au même endroit la validation d'une requête
 * (`rules`) et la logique associée. Laravel l'exécute automatiquement AVANT
 * d'appeler la méthode du contrôleur qui le reçoit en paramètre : si les
 * règles échouent, le contrôleur n'est jamais atteint.
 *
 * Utilisé par AuthenticatedSessionController::store.
 */
class LoginRequest extends FormRequest
{
    /**
     * Indique si l'utilisateur a le droit d'envoyer cette requête.
     *
     * Toujours vrai ici : n'importe quel visiteur peut tenter de se connecter.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Les règles de validation appliquées aux champs du formulaire.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Les messages d'erreur personnalisés, en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => "L'adresse email n'est pas valide.",
            'password.required' => 'Le mot de passe est obligatoire.',
        ];
    }

    /**
     * Tente d'authentifier le client à partir des identifiants fournis.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // `Auth::attempt` cherche le client par email et compare le mot de
        // passe saisi au hash enregistré. Le second argument active la case
        // « Se souvenir de moi ».
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // Chaque échec est compté pour limiter les attaques par force brute.
            RateLimiter::hit($this->throttleKey());

            // Message volontairement générique : on ne révèle pas si c'est
            // l'email ou le mot de passe qui est faux.
            throw ValidationException::withMessages([
                'email' => 'Ces identifiants ne correspondent à aucun compte.',
            ]);
        }

        // Connexion réussie : on remet le compteur d'échecs à zéro.
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Vérifie que la tentative de connexion n'est pas limitée par le rate limiter.
     *
     * Au-delà de 5 échecs pour un même couple email + adresse IP, les
     * tentatives suivantes sont refusées jusqu'à l'expiration du délai.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Trop de tentatives de connexion. Veuillez réessayer dans {$seconds} secondes.",
        ]);
    }

    /**
     * La clé du rate limiter à utiliser pour cette requête.
     *
     * Elle combine l'email (en minuscules) et l'adresse IP : un attaquant qui
     * essaie plusieurs mots de passe sur un compte est bloqué, sans empêcher
     * les autres clients de se connecter.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
