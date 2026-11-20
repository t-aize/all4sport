# Comptes de démonstration

Ces comptes n'existent qu'après avoir lancé les seeders :

```powershell
php artisan migrate:fresh --seed
```

Le mot de passe de **tous** les comptes générés par le seeder est `password`.

## Back-office (collaborateurs)

- URL de connexion : `/backoffice/login`
- Guard : `employe` (séparé de celui des clients)
- Chaque collaborateur a un rôle qui limite ses droits.

| Identifiant      | Nom            | Rôle           | Droits                                                                                   |
| ---------------- | -------------- | -------------- | ---------------------------------------------------------------------------------------- |
| `service_client` | Inès Rousseau  | Service client | Commandes : état "validée"                                                               |
| `operateur`      | Julien Mercier | Opérateur      | Commandes : "en préparation", "expédiée" ; produits (CRUD, stocks, photos), fournisseurs |
| `livreur`        | Mehdi Garnier  | Livreur        | Commandes : "livrée" (livraison à domicile)                                              |
| `vendeur`        | Sarah Lefebvre | Vendeur        | Commandes : "retirée" (retrait magasin) ; produits, fournisseurs                         |

Tous les collaborateurs consultent les commandes et le tableau de bord. Le seeder crée aussi 12 collaborateurs supplémentaires (3 par rôle) avec un identifiant aléatoire : `php artisan tinker --execute 'App\Models\Employe::pluck("role", "identifiant_connexion");'`

## Clients (site public)

- URL de connexion : `/login`, inscription : `/register`

| Email                    | Mot de passe | Profil                                                                                                             |
| ------------------------ | ------------ | ------------------------------------------------------------------------------------------------------------------ |
| `client@all4sport.local` | `password`   | Camille Martin, magasin par défaut Lille, 2 enfants, 3 sports, une commande dans chaque état, panier de 3 articles |

Le seeder crée aussi 80 clients (email aléatoire, mot de passe `password`) : `php artisan tinker --execute 'App\Models\Client::pluck("email");'`

## API Logistique (Lot B)

Pas de compte : un jeton partagé. Générer avec `php artisan logistique:jeton` puis le placer dans `LOGISTIQUE_API_TOKEN` (`.env`). Voir [api-logistique.md](api-logistique.md).

## Jeu de données

| Donnée       | Volume                                                                                            |
| ------------ | ------------------------------------------------------------------------------------------------- |
| Entrepôts    | 3 : Le Havre (Nord-Ouest), Lyon (Centre-Est), Marseille (Sud)                                     |
| Magasins     | 12 : Lille, Arras, Amiens, Paris, Reims, Metz, Rennes, Tours, Lyon, Bordeaux, Toulouse, Nice      |
| Rayons       | 14 sports (Football, Running, Cyclisme, Natation, Tennis, Basketball, Randonnée, Ski, Fitness...) |
| Fournisseurs | 10                                                                                                |
| Produits     | 105, avec 1 à 5 visuels, lieu de stockage, stock Internet et stock dans 4 à 12 magasins           |
| Commandes    | 257 sur 12 mois, avec leur historique d'états complet                                             |
| Paniers      | 11 paniers en cours                                                                               |
