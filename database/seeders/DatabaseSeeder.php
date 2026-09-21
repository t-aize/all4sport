<?php

namespace Database\Seeders;

use App\Enums\EtatCommande;
use App\Enums\ModeLivraison;
use App\Enums\RoleEmploye;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Enfant;
use App\Models\Entrepot;
use App\Models\Fournisseur;
use App\Models\Magasin;
use App\Models\Produit;
use App\Models\Rayon;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Peuple la base avec un jeu de données de démonstration cohérent pour
 * l'ensemble du modèle ALL4SPORT : catalogue (fournisseurs, rayons, produits,
 * photos), réseau logistique (entrepôts, magasins, stocks), acteurs (clients,
 * enfants, collaborateurs) et commandes avec leur historique d'état.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Peuple la base de données de l'application.
     */
    public function run(): void
    {
        $fournisseurs = Fournisseur::factory(5)->create();
        $rayons = Rayon::factory(10)->create();
        $entrepots = Entrepot::factory(3)->create();
        $magasins = $this->seedMagasins($entrepots);
        $employes = $this->seedEmployes();
        $clients = $this->seedClients($rayons);
        $produits = $this->seedProduits($fournisseurs, $rayons, $magasins);

        $this->seedCommandes($clients, $produits, $magasins, $entrepots, $employes);
    }

    /**
     * Crée les magasins et les rattache chacun à un entrepôt existant
     * (association DESSERVIR).
     *
     * @param  Collection<int, Entrepot>  $entrepots
     * @return Collection<int, Magasin>
     */
    private function seedMagasins(Collection $entrepots): Collection
    {
        return collect(range(1, 6))->map(fn () => Magasin::factory()->create([
            'entrepot_id' => $entrepots->random()->id,
        ]));
    }

    /**
     * Crée les collaborateurs en garantissant au moins un représentant de
     * chaque rôle, afin que l'historique des commandes puisse toujours
     * trouver un responsable pour chaque état (association ENREGISTRER).
     *
     * Ces comptes de démonstration se connectent au back-office avec un
     * identifiant égal au nom technique du rôle (« operateur », « vendeur »,
     * « livreur » ou « service_client ») et le mot de passe « password ».
     *
     * @return Collection<int, Employe>
     */
    private function seedEmployes(): Collection
    {
        $unParRole = collect(RoleEmploye::cases())
            ->map(fn (RoleEmploye $role) => Employe::factory()->create([
                'identifiant_connexion' => $role->value,
                'role' => $role,
            ]));

        return $unParRole->merge(Employe::factory(6)->create());
    }

    /**
     * Crée les clients, leurs enfants (association AVOIR) et les sports
     * qu'ils pratiquent (association PRATIQUER).
     *
     * @param  Collection<int, Rayon>  $rayons
     * @return Collection<int, Client>
     */
    private function seedClients(Collection $rayons): Collection
    {
        $clients = Client::factory(30)->create();

        $clients->each(function (Client $client) use ($rayons): void {
            $client->rayons()->attach(
                $rayons->random(random_int(1, 3))->pluck('id')
            );

            if (random_int(0, 100) < 40) {
                Enfant::factory(random_int(1, 3))->create([
                    'client_id' => $client->id,
                ]);
            }
        });

        return $clients;
    }

    /**
     * Crée les produits, leurs photos (association ILLUSTRER) et leur
     * répartition dans les stocks magasin (association STOCKER).
     *
     * @param  Collection<int, Fournisseur>  $fournisseurs
     * @param  Collection<int, Rayon>  $rayons
     * @param  Collection<int, Magasin>  $magasins
     * @return Collection<int, Produit>
     */
    private function seedProduits(Collection $fournisseurs, Collection $rayons, Collection $magasins): Collection
    {
        $produits = collect(range(1, 60))->map(fn () => Produit::factory()->create([
            'fournisseur_id' => $fournisseurs->random()->id,
            'rayon_id' => $rayons->random()->id,
        ]));

        $produits->each(function (Produit $produit) use ($magasins): void {
            $produit->photos()->createMany(
                collect(range(1, random_int(1, 5)))->map(fn (int $position) => [
                    'url_photo' => "https://picsum.photos/seed/{$produit->reference_produit}-{$position}/640/480",
                ])
            );

            $magasinsChoisis = $magasins->random(random_int(1, max(1, min(4, $magasins->count()))));
            $produit->magasins()->attach(
                $magasinsChoisis->mapWithKeys(fn (Magasin $magasin) => [
                    $magasin->id => ['quantite_stock_magasin' => random_int(0, 50)],
                ])
            );
        });

        return $produits;
    }

    /**
     * Crée les commandes, leurs lignes de commande (association CONTENIR) et
     * leur historique d'état (associations SUIVRE et ENREGISTRER), en
     * respectant la cohérence avec le mode de livraison choisi.
     *
     * @param  Collection<int, Client>  $clients
     * @param  Collection<int, Produit>  $produits
     * @param  Collection<int, Magasin>  $magasins
     * @param  Collection<int, Entrepot>  $entrepots
     * @param  Collection<int, Employe>  $employes
     */
    private function seedCommandes(Collection $clients, Collection $produits, Collection $magasins, Collection $entrepots, Collection $employes): void
    {
        foreach (range(1, 100) as $ignored) {
            $client = $clients->random();
            $modeLivraison = fake()->randomElement(ModeLivraison::cases());
            $fraisLivraison = $modeLivraison === ModeLivraison::LivraisonDomicile ? 10 : 0;

            $commande = Commande::factory()->create([
                'mode_livraison' => $modeLivraison,
                'adresse_livraison' => $modeLivraison === ModeLivraison::LivraisonDomicile
                    ? fake('fr_FR')->streetAddress()
                    : null,
                'frais_livraison' => $fraisLivraison,
                'montant_total' => 0,
                'client_id' => $client->id,
                'employe_id' => null,
                'magasin_id' => $modeLivraison === ModeLivraison::RetraitMagasin ? $magasins->random()->id : null,
                'entrepot_id' => $modeLivraison === ModeLivraison::LivraisonDomicile ? $entrepots->random()->id : null,
            ]);

            $sousTotal = $this->attacherLignesDeCommande($commande, $produits);
            $commande->update(['montant_total' => $sousTotal + $fraisLivraison]);

            $this->creerHistoriqueEtats($commande, $employes);
        }
    }

    /**
     * Attache un panier de 1 à 5 produits à la commande (association CONTENIR)
     * et retourne le sous-total de la commande.
     *
     * @param  Collection<int, Produit>  $produits
     */
    private function attacherLignesDeCommande(Commande $commande, Collection $produits): float
    {
        $lignes = $produits->random(random_int(1, 5));
        $sousTotal = 0.0;

        foreach ($lignes as $produit) {
            $quantite = random_int(1, 3);

            $commande->produits()->attach($produit->reference_produit, [
                'quantite_commandee' => $quantite,
                'prix_unitaire_vente' => $produit->prix_vente,
            ]);

            $sousTotal += round($quantite * (float) $produit->prix_vente, 2);
        }

        return round($sousTotal, 2);
    }

    /**
     * Construit l'historique d'état de la commande, du dépôt jusqu'à un état
     * final tiré aléatoirement, en confiant chaque état au collaborateur dont
     * le rôle en est responsable (association ENREGISTRER).
     *
     * @param  Collection<int, Employe>  $employes
     */
    private function creerHistoriqueEtats(Commande $commande, Collection $employes): void
    {
        $etatFinal = fake()->randomElement(EtatCommande::cases());
        $parcours = $this->parcoursJusqua($commande->mode_livraison, $etatFinal);

        $date = Carbon::instance($commande->date_commande)->copy();

        foreach ($parcours as $etat) {
            $date = $date->copy()->addHours(random_int(2, 48));

            $commande->historiqueEtats()->create([
                'etat' => $etat,
                'date_etat' => $date,
                'employe_id' => $this->employeResponsable($etat, $employes)?->id,
            ]);
        }
    }

    /**
     * Construit la liste ordonnée des états traversés par une commande, en
     * s'arrêtant à l'état final demandé et en tenant compte du mode de
     * livraison (une commande retirée en magasin ne peut pas être "livrée",
     * une commande livrée à domicile ne peut pas être "retirée").
     *
     * @return array<int, EtatCommande>
     */
    private function parcoursJusqua(ModeLivraison $modeLivraison, EtatCommande $etatFinal): array
    {
        $etatFinalPourDenouement = match (true) {
            $etatFinal === EtatCommande::Livree && $modeLivraison === ModeLivraison::RetraitMagasin => EtatCommande::Retiree,
            $etatFinal === EtatCommande::Retiree && $modeLivraison === ModeLivraison::LivraisonDomicile => EtatCommande::Livree,
            default => $etatFinal,
        };

        $ordre = [
            EtatCommande::Transmise,
            EtatCommande::Validee,
            EtatCommande::EnPreparation,
            EtatCommande::Expediee,
            $modeLivraison === ModeLivraison::RetraitMagasin ? EtatCommande::Retiree : EtatCommande::Livree,
        ];

        $position = array_search($etatFinalPourDenouement, $ordre, strict: true);

        return array_slice($ordre, 0, ($position === false ? 0 : $position) + 1);
    }

    /**
     * Détermine le rôle de collaborateur responsable d'un état donné, et en
     * retourne un au hasard. L'état "transmise" est déclenché par le paiement
     * du client, sans intervention d'un collaborateur.
     *
     * @param  Collection<int, Employe>  $employes
     */
    private function employeResponsable(EtatCommande $etat, Collection $employes): ?Employe
    {
        $role = RoleEmploye::responsableDe($etat);

        if ($role === null) {
            return null;
        }

        return $employes->where('role', $role)->random();
    }
}
