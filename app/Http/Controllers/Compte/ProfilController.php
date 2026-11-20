<?php

namespace App\Http\Controllers\Compte;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compte\ProfilRequest;
use App\Models\Client;
use App\Models\Enfant;
use App\Models\Magasin;
use App\Models\Rayon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Espace "Mon compte" du client : informations personnelles, adresse,
 * magasin par défaut, sports pratiqués et enfants (fichier client du dossier
 * de contexte).
 *
 * Page React : resources/js/pages/compte/profil.tsx.
 */
class ProfilController extends Controller
{
    /**
     * Affiche le profil du client connecté.
     */
    public function edit(Request $request): Response
    {
        /** @var Client $client */
        $client = $request->user('web');
        $client->load(['rayons', 'enfants', 'magasin']);

        return Inertia::render('compte/profil', [
            'client' => [
                'codeCli' => $client->code_cli,
                'nom' => $client->nom,
                'prenom' => $client->prenom,
                'email' => $client->email,
                'telephone' => $client->telephone,
                'adresse' => $client->adresse,
                'codePostal' => $client->code_postal,
                'ville' => $client->ville,
                'dateNaissance' => $client->date_naissance?->toDateString(),
                'dateDernierAchat' => $client->date_dernier_achat?->toDateString(),
                'magasinId' => $client->magasin_id,
                'rayons' => $client->rayons->pluck('id')->values(),
                'inscritLe' => $client->created_at?->toDateString(),
                'nombreCommandes' => $client->commandes()->count(),
            ],
            'enfants' => $client->enfants
                ->sortBy('date_naissance')
                ->values()
                ->map(fn (Enfant $enfant): array => [
                    'id' => $enfant->id,
                    'dateNaissance' => $enfant->date_naissance->toDateString(),
                    'age' => (int) $enfant->date_naissance->diffInYears(now()),
                ]),
            'magasins' => Magasin::query()->orderBy('ville')->get(['id', 'ville', 'adresse'])
                ->map(fn (Magasin $magasin): array => [
                    'id' => $magasin->id,
                    'ville' => $magasin->ville,
                    'adresse' => $magasin->adresse,
                ]),
            'rayons' => Rayon::query()->orderBy('nom_rayon')->get(['id', 'nom_rayon'])
                ->map(fn (Rayon $rayon): array => ['id' => $rayon->id, 'nom' => $rayon->nom_rayon]),
        ]);
    }

    /**
     * Enregistre les informations personnelles, le magasin par défaut et les
     * sports pratiqués.
     */
    public function update(ProfilRequest $request): RedirectResponse
    {
        /** @var Client $client */
        $client = $request->user('web');
        $donnees = $request->validated();

        DB::transaction(function () use ($client, $donnees): void {
            $client->update([
                'nom' => $donnees['nom'],
                'prenom' => $donnees['prenom'],
                'email' => $donnees['email'],
                'telephone' => $donnees['telephone'] ?? null,
                'adresse' => $donnees['adresse'],
                'code_postal' => $donnees['code_postal'],
                'ville' => $donnees['ville'],
                'date_naissance' => $donnees['date_naissance'] ?? null,
                'magasin_id' => $donnees['magasin_id'] ?? null,
            ]);

            $client->rayons()->sync($donnees['rayons'] ?? []);
        });

        return back()->with('status', 'Vos informations ont été enregistrées.');
    }
}
