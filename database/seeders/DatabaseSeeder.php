<?php

namespace Database\Seeders;

use App\Enums\EtatCommande;
use App\Enums\ModeLivraison;
use App\Enums\RoleEmploye;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Enfant;
use App\Models\Magasin;
use App\Models\Produit;
use App\Models\Rayon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Peuple la base avec un jeu de données de démonstration complet et cohérent :
 * - réseau : 3 entrepôts et 12 magasins du dossier de contexte (ReseauSeeder) ;
 * - catalogue : 14 rayons, 10 fournisseurs, ~110 produits avec photos et
 *   stocks (CatalogueSeeder) ;
 * - collaborateurs : un compte de démonstration par rôle + 12 autres ;
 * - clients : un client de démonstration + 80 clients avec enfants, sports
 *   pratiqués et magasin par défaut ;
 * - commandes : ~260 commandes sur 12 mois, avec leur historique d'états ;
 * - paniers en cours (PanierSeeder).
 *
 * Tous les mots de passe valent « password ». Voir docs/comptes.md.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Email du client de démonstration.
     */
    public const EMAIL_CLIENT_DEMO = 'client@all4sport.local';

    /**
     * Nombre de clients générés en plus du client de démonstration.
     */
    public const NOMBRE_CLIENTS = 80;

    /**
     * Nombre de commandes générées en plus de celles du client de démonstration.
     */
    public const NOMBRE_COMMANDES = 250;

    /**
     * Peuple la base de données de l'application.
     */
    public function run(): void
    {
        $this->call([ReseauSeeder::class, CatalogueSeeder::class]);

        $magasins = Magasin::query()->with('entrepot')->get();
        $rayons = Rayon::query()->get();
        $produits = Produit::query()->get();
        $employes = $this->seedEmployes();
        $clients = $this->seedClients($rayons, $magasins);

        $this->seedCommandes($clients, $produits, $magasins, $employes);

        $this->call(PanierSeeder::class);
    }

    /**
     * Crée les collaborateurs : un compte de démonstration par rôle (identifiant
     * = nom technique du rôle, mot de passe « password ») et 12 autres
     * collaborateurs répartis entre les rôles.
     *
     * @return Collection<int, Employe>
     */
    private function seedEmployes(): Collection
    {
        $comptesDemo = [
            RoleEmploye::Operateur->value => ['Mercier', 'Julien'],
            RoleEmploye::Vendeur->value => ['Lefebvre', 'Sarah'],
            RoleEmploye::Livreur->value => ['Garnier', 'Mehdi'],
            RoleEmploye::ServiceClient->value => ['Rousseau', 'Inès'],
        ];

        $unParRole = collect(RoleEmploye::cases())->map(fn (RoleEmploye $role) => Employe::factory()->create([
            'identifiant_connexion' => $role->value,
            'nom' => $comptesDemo[$role->value][0],
            'prenom' => $comptesDemo[$role->value][1],
            'role' => $role,
        ]));

        $autres = collect(range(1, 12))->map(fn (int $position) => Employe::factory()->create([
            'role' => RoleEmploye::cases()[$position % 4],
        ]));

        return $unParRole->merge($autres);
    }

    /**
     * Crée le client de démonstration et les autres clients, avec leurs
     * enfants (association AVOIR), leurs sports (PRATIQUER) et leur magasin
     * par défaut.
     *
     * @param  Collection<int, Rayon>  $rayons
     * @param  Collection<int, Magasin>  $magasins
     * @return Collection<int, Client>
     */
    private function seedClients(Collection $rayons, Collection $magasins): Collection
    {
        $lille = $magasins->firstWhere('ville', 'Lille') ?? $magasins->first();

        $demo = Client::factory()->create([
            'nom' => 'Martin',
            'prenom' => 'Camille',
            'email' => self::EMAIL_CLIENT_DEMO,
            'telephone' => '06 12 34 56 78',
            'adresse' => '42 rue Esquermoise',
            'code_postal' => '59000',
            'ville' => 'Lille',
            'date_naissance' => '1992-04-18',
            'magasin_id' => $lille->id,
        ]);
        $demo->rayons()->attach($rayons->whereIn('nom_rayon', ['Running', 'Natation', 'Randonnée'])->pluck('id'));
        Enfant::factory()->for($demo)->create(['date_naissance' => now()->subYears(9)->subMonths(3)]);
        Enfant::factory()->for($demo)->create(['date_naissance' => now()->subYears(6)->subMonths(7)]);

        $clients = Client::factory(self::NOMBRE_CLIENTS)->create()->each(function (Client $client) use ($rayons, $magasins): void {
            $client->update(['magasin_id' => random_int(0, 100) < 80 ? $magasins->random()->id : null]);
            $client->rayons()->attach($rayons->random(random_int(1, 3))->pluck('id'));

            if (random_int(0, 100) < 40) {
                Enfant::factory(random_int(1, 3))->for($client)->create();
            }
        });

        return $clients->prepend($demo);
    }

    /**
     * Crée les commandes et leur historique. Le client de démonstration reçoit
     * une commande dans chaque état pour illustrer le suivi.
     *
     * @param  Collection<int, Client>  $clients
     * @param  Collection<int, Produit>  $produits
     * @param  Collection<int, Magasin>  $magasins
     * @param  Collection<int, Employe>  $employes
     */
    private function seedCommandes(Collection $clients, Collection $produits, Collection $magasins, Collection $employes): void
    {
        /** @var Client $demo */
        $demo = $clients->first();
        $scenarios = [
            [EtatCommande::Transmise, ModeLivraison::LivraisonDomicile, 1],
            [EtatCommande::Validee, ModeLivraison::RetraitMagasin, 4],
            [EtatCommande::EnPreparation, ModeLivraison::LivraisonDomicile, 8],
            [EtatCommande::Expediee, ModeLivraison::RetraitMagasin, 15],
            [EtatCommande::Livree, ModeLivraison::LivraisonDomicile, 45],
            [EtatCommande::Retiree, ModeLivraison::RetraitMagasin, 120],
            [EtatCommande::Livree, ModeLivraison::LivraisonDomicile, 240],
        ];

        foreach ($scenarios as [$etatFinal, $mode, $joursAvant]) {
            $this->creerCommande($demo, $mode, $etatFinal, now()->subDays($joursAvant)->toImmutable(), $produits, $magasins, $employes);
        }

        foreach (range(1, self::NOMBRE_COMMANDES) as $position) {
            $date = CarbonImmutable::now()->subDays(random_int(0, 365))->setTime(random_int(8, 21), random_int(0, 59));
            // Les commandes récentes sont encore en cours ; les anciennes sont terminées.
            $etatFinal = $date->diffInDays(now()) > 20
                ? fake()->randomElement([EtatCommande::Livree, EtatCommande::Retiree])
                : fake()->randomElement(EtatCommande::cases());

            $this->creerCommande(
                $clients->random(),
                fake()->randomElement(ModeLivraison::cases()),
                $etatFinal,
                $date,
                $produits,
                $magasins,
                $employes,
            );
        }

        $clients->each(function (Client $client): void {
            $dernier = $client->commandes()->max('date_commande');

            $client->forceFill(['date_dernier_achat' => $dernier])->save();
        });
    }

    /**
     * Crée une commande cohérente : lignes, total, lieu d'expédition et
     * historique des états jusqu'à l'état final (adapté au mode de livraison).
     *
     * @param  Collection<int, Produit>  $produits
     * @param  Collection<int, Magasin>  $magasins
     * @param  Collection<int, Employe>  $employes
     */
    private function creerCommande(
        Client $client,
        ModeLivraison $mode,
        EtatCommande $etatFinal,
        CarbonImmutable $date,
        Collection $produits,
        Collection $magasins,
        Collection $employes,
    ): Commande {
        $magasinClient = $magasins->firstWhere('id', $client->magasin_id) ?? $magasins->random();
        $parcours = EtatCommande::parcours($mode);
        $etatFinal = match (true) {
            $etatFinal === EtatCommande::Livree && $mode === ModeLivraison::RetraitMagasin => EtatCommande::Retiree,
            $etatFinal === EtatCommande::Retiree && $mode === ModeLivraison::LivraisonDomicile => EtatCommande::Livree,
            default => $etatFinal,
        };

        $commande = Commande::query()->create([
            'date_commande' => $date->toDateString(),
            'mode_livraison' => $mode,
            'adresse_livraison' => $mode === ModeLivraison::LivraisonDomicile ? $client->adresseComplete() : null,
            'frais_livraison' => $mode->frais(),
            'montant_total' => 0,
            'etat' => $etatFinal,
            'client_id' => $client->id,
            'magasin_id' => $mode === ModeLivraison::RetraitMagasin ? $magasinClient->id : null,
            'entrepot_id' => $mode === ModeLivraison::LivraisonDomicile ? $magasinClient->entrepot_id : null,
        ]);

        $sousTotal = 0.0;

        foreach ($produits->random(random_int(1, 4)) as $produit) {
            $quantite = fake()->randomElement([1, 1, 1, 2, 2, 3]);
            $commande->produits()->attach($produit->reference_produit, [
                'quantite_commandee' => $quantite,
                'prix_unitaire_vente' => $produit->prix_vente,
            ]);
            $sousTotal += round($quantite * (float) $produit->prix_vente, 2);
        }

        $commande->update(['montant_total' => round($sousTotal + $mode->frais(), 2)]);

        $dateEtat = $date;
        $dernierEmploye = null;

        foreach (array_slice($parcours, 0, array_search($etatFinal, $parcours, true) + 1) as $etat) {
            $role = RoleEmploye::responsableDe($etat);
            $employe = $role ? $employes->where('role', $role)->random() : null;
            $dernierEmploye = $employe ?? $dernierEmploye;

            $commande->historiqueEtats()->create([
                'etat' => $etat,
                'date_etat' => $dateEtat,
                'employe_id' => $employe?->id,
            ]);

            $dateEtat = $dateEtat->addHours(random_int(2, 30));
        }

        $commande->update(['employe_id' => $dernierEmploye?->id]);

        return $commande;
    }
}
