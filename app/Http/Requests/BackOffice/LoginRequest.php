<?php

namespace App\Http\Requests\BackOffice;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Requête de connexion d'un collaborateur au back-office.
 *
 * Elle joue le même rôle que App\Http\Requests\Auth\LoginRequest pour les
 * clients, avec deux différences : l'identifiant est l'"identifiant de
 * connexion" de l'employé (et non un email) et l'authentification passe par le
 * guard "employe", qui lit la table `employes`.
 *
 * Utilisée par BackOffice\AuthenticatedSessionController::store.
 */
class LoginRequest extends FormRequest
{
    /**
     * Nombre d'échecs tolérés avant de bloquer temporairement les tentatives.
     */
    private const MAX_TENTATIVES = 5;

    /**
     * Tout visiteur peut tenter de se connecter.
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
            'identifiant_connexion' => ['required', 'string'],
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
            'identifiant_connexion.required' => "L'identifiant de connexion est obligatoire.",
            'password.required' => 'Le mot de passe est obligatoire.',
        ];
    }

    /**
     * Tente d'authentifier le collaborateur à partir des identifiants fournis.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::guard('employe')->attempt($this->only('identifiant_connexion', 'password'))) {
            RateLimiter::hit($this->throttleKey());

            // Message volontairement générique : on ne révèle pas si c'est
            // l'identifiant ou le mot de passe qui est faux.
            throw ValidationException::withMessages([
                'identifiant_connexion' => 'Ces identifiants ne correspondent à aucun compte.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Vérifie que la tentative de connexion n'est pas limitée par le rate limiter.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_TENTATIVES)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'identifiant_connexion' => "Trop de tentatives de connexion. Veuillez réessayer dans {$seconds} secondes.",
        ]);
    }

    /**
     * La clé du rate limiter : identifiant (en minuscules) + adresse IP.
     *
     * Le préfixe "backoffice|" sépare ces compteurs de ceux des clients, pour
     * qu'un identifiant identique côté client et côté employé ne partage pas
     * le même quota d'essais.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            'backoffice|'.Str::lower($this->string('identifiant_connexion')).'|'.$this->ip()
        );
    }
}
