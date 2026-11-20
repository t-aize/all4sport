<?php

namespace App\Http\Controllers\Compte;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Enfant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Enfants rattachés au compte du client (nombre et âge des enfants, fichier
 * client du dossier de contexte).
 */
class EnfantController extends Controller
{
    /**
     * Nombre maximal d'enfants déclarés sur un compte.
     */
    private const NOMBRE_MAXIMAL = 10;

    /**
     * Ajoute un enfant à partir de sa date de naissance.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Client $client */
        $client = $request->user('web');

        $donnees = $request->validate([
            'date_naissance' => ['required', 'date', 'before_or_equal:today', 'after:-30 years'],
        ], [
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'date_naissance.before_or_equal' => 'La date de naissance ne peut pas être dans le futur.',
            'date_naissance.after' => "L'enfant doit avoir moins de 30 ans.",
        ]);

        if ($client->enfants()->count() >= self::NOMBRE_MAXIMAL) {
            return back()->withErrors(['date_naissance' => 'Vous ne pouvez pas déclarer plus de '.self::NOMBRE_MAXIMAL.' enfants.']);
        }

        $client->enfants()->create($donnees);

        return back()->with('status', 'Enfant ajouté.');
    }

    /**
     * Retire un enfant du compte. Un client ne peut supprimer que ses propres
     * enfants (404 sinon).
     */
    public function destroy(Request $request, Enfant $enfant): RedirectResponse
    {
        abort_unless($enfant->client_id === $request->user('web')?->getAuthIdentifier(), 404);

        $enfant->delete();

        return back()->with('status', 'Enfant retiré.');
    }
}
