<?php

namespace App\Http\Controllers;

use App\Enums\ModeLivraison;
use App\Http\Requests\PasserCommandeRequest;
use App\Models\Client;
use App\Models\LignePanier;
use App\Models\Magasin;
use App\Services\ServiceCommande;
use App\Services\ServicePanier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Validation du panier et passage de commande (processus de vente en ligne) :
 * le client choisit son mode de livraison, les stocks sont vérifiés, puis la
 * commande est ajoutée à son compte avec l'état « transmise ».
 *
 * Si le panier est vide ou qu'un stock est insuffisant, la validation est
 * annulée et le client revient au panier.
 *
 * Page React : resources/js/pages/commande/create.tsx.
 */
class CommandeController extends Controller
{
    public function __construct(
        private ServicePanier $servicePanier,
        private ServiceCommande $serviceCommande,
    ) {}

    /**
     * Affiche l'étape de livraison et de paiement.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        /** @var Client $client */
        $client = $request->user('web');
        $panier = $this->servicePanier->courant();

        if ($panier->estVide()) {
            return redirect()->route('panier.index')
                ->withErrors(['panier' => 'Votre panier est vide : ajoutez des articles avant de le valider.']);
        }

        $magasins = Magasin::query()->with('entrepot')->orderBy('ville')->get();

        return Inertia::render('commande/create', [
            'panier' => [
                'lignes' => $panier->lignes->map(fn (LignePanier $ligne): array => [
                    'reference' => $ligne->reference_produit,
                    'description' => $ligne->produit->description_produit,
                    'photo' => $ligne->produit->photos->first()?->url_photo,
                    'quantite' => $ligne->quantite,
                    'prixUnitaire' => $ligne->prix_unitaire,
                    'sousTotal' => number_format($ligne->sous_total, 2, '.', ''),
                ])->values(),
                'total' => number_format($panier->total, 2, '.', ''),
                'nombreArticles' => $panier->nombre_articles,
            ],
            'magasins' => $magasins->map(fn (Magasin $magasin): array => [
                'id' => $magasin->id,
                'ville' => $magasin->ville,
                'adresse' => $magasin->adresse,
                'disponible' => $this->panierDisponible($panier->lignes->all(), ModeLivraison::RetraitMagasin, $magasin),
            ])->values(),
            'disponibleInternet' => $this->panierDisponible($panier->lignes->all(), ModeLivraison::LivraisonDomicile, null),
            'client' => [
                'magasinId' => $client->magasin_id,
                'adresse' => $client->adresse,
                'codePostal' => $client->code_postal,
                'ville' => $client->ville,
            ],
            'entrepotExpediteur' => $this->serviceCommande->entrepotExpediteur($client)?->ville,
            'modes' => array_map(fn (ModeLivraison $mode): array => [
                'value' => $mode->value,
                'label' => $mode->libelle(),
                'frais' => number_format($mode->frais(), 2, '.', ''),
            ], ModeLivraison::cases()),
        ]);
    }

    /**
     * Vérifie les stocks et enregistre la commande.
     */
    public function store(PasserCommandeRequest $request): RedirectResponse
    {
        /** @var Client $client */
        $client = $request->user('web');
        $mode = ModeLivraison::from($request->string('mode_livraison')->toString());
        $magasin = $mode === ModeLivraison::RetraitMagasin
            ? Magasin::query()->findOrFail($request->integer('magasin_id'))
            : null;

        try {
            $commande = $this->serviceCommande->passer(
                $client,
                $this->servicePanier->courant(),
                $mode,
                $magasin,
                $request->only(['adresse', 'code_postal', 'ville']),
            );
        } catch (ValidationException $exception) {
            // Annulation de la validation du panier : retour au panier.
            return redirect()->route('panier.index')->withErrors($exception->errors());
        }

        return redirect()
            ->route('compte.commandes.show', ['commande' => $commande, 'confirmation' => 1])
            ->with('status', "Merci ! Votre commande {$commande->numero()} a bien été transmise.");
    }

    /**
     * Indique si toutes les lignes du panier sont disponibles pour un mode de
     * livraison (et un magasin pour le retrait).
     *
     * @param  array<int, LignePanier>  $lignes
     */
    private function panierDisponible(array $lignes, ModeLivraison $mode, ?Magasin $magasin): bool
    {
        foreach ($lignes as $ligne) {
            if ($ligne->quantite > $this->serviceCommande->stockDisponible($ligne->produit, $mode, $magasin)) {
                return false;
            }
        }

        return true;
    }
}
