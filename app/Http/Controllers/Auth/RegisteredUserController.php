<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Affiche le formulaire d'inscription.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Enregistre un nouveau client puis le connecte.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:30'],
            'prenom' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:80', Rule::unique(Client::class)],
            'telephone' => ['nullable', 'string', 'max:15'],
            'adresse' => ['required', 'string', 'max:50'],
            'code_postal' => ['required', 'string', 'max:5'],
            'ville' => ['required', 'string', 'max:30'],
            'date_naissance' => ['nullable', 'date'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => "L'adresse email n'est pas valide.",
            'email.unique' => 'Un compte existe déjà avec cette adresse email.',
            'telephone.max' => 'Le numéro de téléphone ne doit pas dépasser :max caractères.',
            'adresse.required' => "L'adresse est obligatoire.",
            'code_postal.required' => 'Le code postal est obligatoire.',
            'ville.required' => 'La ville est obligatoire.',
            'date_naissance.date' => "La date de naissance n'est pas valide.",
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $client = Client::create($validated);

        event(new Registered($client));

        Auth::login($client);

        return redirect()->route('home');
    }
}
