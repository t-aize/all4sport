# Comptes de démonstration

Ces comptes n'existent qu'après avoir lancé les seeders :

```powershell
php artisan migrate:fresh --seed
```

Le mot de passe de **tous** les comptes générés par le seeder est `password`.

## Back-office (collaborateurs)

- URL de connexion : `/backoffice/login`
- Guard : `employe` (séparé de celui des clients)
- Ce ne sont pas des administrateurs : chaque collaborateur a un rôle qui limite ses droits.

| Identifiant      | Mot de passe | Rôle           | Droits                                                                     |
| ---------------- | ------------ | -------------- | -------------------------------------------------------------------------- |
| `operateur`      | `password`   | Opérateur      | Produits (CRUD, stocks, photos) ; états « en préparation » et « expédiée » |
| `vendeur`        | `password`   | Vendeur        | Produits (CRUD, stocks, photos) ; état « retirée » (retrait magasin)       |
| `livreur`        | `password`   | Livreur        | État « livrée » (livraison à domicile)                                     |
| `service_client` | `password`   | Service client | État « validée »                                                           |

La gestion des produits (`/backoffice/produits`) est réservée aux rôles `operateur` et `vendeur`. Le livreur et le service client accèdent seulement au tableau de bord.

Le seeder crée aussi 6 collaborateurs supplémentaires avec un identifiant aléatoire (mot de passe `password`). Pour les retrouver : `php artisan tinker --execute 'App\Models\Employe::pluck("role", "identifiant_connexion");'`

## Clients (site public)

- URL de connexion : `/login`, inscription : `/register`
- Le seeder crée 30 clients avec un e-mail aléatoire et le mot de passe `password`.
- Pour en retrouver un : `php artisan tinker --execute 'App\Models\Client::pluck("email");'`
- Le plus simple reste de créer son propre compte via `/register`.
