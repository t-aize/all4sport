<?php

namespace App\Http\Controllers\Compte;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

/**
 * Changement du mot de passe du client connecté. Le mot de passe actuel est
 * exigé ; le nouveau est haché (cast « hashed » du modèle Client).
 */
class MotDePasseController extends Controller
{
    /**
     * Enregistre le nouveau mot de passe.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var Client $client */
        $client = $request->user('web');

        $donnees = $request->validate([
            'mot_de_passe_actuel' => ['required', 'current_password:web'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'mot_de_passe_actuel.required' => 'Le mot de passe actuel est obligatoire.',
            'mot_de_passe_actuel.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $client->update(['password' => $donnees['password']]);

        return back()->with('status', 'Mot de passe modifié.');
    }
}
