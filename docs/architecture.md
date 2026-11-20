# Documentation technique

## Pile logicielle

Laravel 13 (PHP 8.5) · Inertia 3 + React 19 · Tailwind CSS 4 + shadcn/ui (Base UI) · Wayfinder (routes typées) · Pest 5 · Pint · PHPStan · Vite+.

## Organisation du code

| Dossier                             | Contenu                                                                    |
| ----------------------------------- | -------------------------------------------------------------------------- |
| `app/Models`                        | Modèles Eloquent (Produit, Client, Commande, Panier, LignePanier…)         |
| `app/Enums`                         | `EtatCommande` (ordre du workflow), `ModeLivraison` (frais), `RoleEmploye` |
| `app/Services/ServicePanier.php`    | Panier visiteur / client, fusion à la connexion                            |
| `app/Services/ServiceCommande.php`  | Passage de commande : vérification et décrément des stocks                 |
| `app/Services/WorkflowCommande.php` | Transitions d'état autorisées par rôle, historique                         |
| `app/Http/Controllers`              | Site public (accueil, catalogue, panier, commande)                         |
| `app/Http/Controllers/Compte`       | Espace client (profil, enfants, mot de passe, historique)                  |
| `app/Http/Controllers/BackOffice`   | Back-office (tableau de bord, commandes, produits, fournisseurs)           |
| `app/Http/Controllers/Api/V1`       | API d'échange avec le Lot B                                                |
| `app/Http/Middleware`               | Rôles, jeton API, en-têtes de sécurité, données Inertia partagées          |
| `routes/`                           | `web.php`, `auth.php`, `client.php`, `backoffice.php`, `api.php`           |
| `resources/js/pages`                | Pages React (une par écran)                                                |
| `resources/js/components`           | Composants (carte produit, badge d'état, frise de suivi, détail commande…) |
| `database/seeders`                  | `ReseauSeeder`, `CatalogueSeeder`, `PanierSeeder`, `DatabaseSeeder`        |

## Modèle de données

```
Fournisseur 1─n Produit n─1 Rayon
Produit 1─n Photo (1 à 5)
Produit n─n Magasin (magasin_produit : quantite_stock_magasin)
Magasin n─1 Entrepot (zone d'expédition)
Client 1─n Enfant · Client n─n Rayon (sports pratiqués) · Client n─1 Magasin (par défaut)
Client 1─n Commande n─n Produit (commande_produit : quantité, prix unitaire figé)
Commande 1─n HistoriqueEtat n─1 Employe
Commande n─1 Magasin (retrait) · Commande n─1 Entrepot (livraison)
Panier (client ou session) 1─n LignePanier n─1 Produit
```

- Référence produit : `3 lettres fournisseur + 3 lettres rayon + 12 chiffres` (clé primaire), générée à la création.
- Code client : `CLI + 8 chiffres`, généré à la création.
- `commandes.etat` recopie le dernier état de l'historique pour filtrer rapidement.

## Workflow de commande

```
transmise ──(service client)──▶ validée ──(opérateur)──▶ en préparation ──(opérateur)──▶ expédiée
                                                                                          │
                                         livraison à domicile ──(livreur)──▶ livrée ◀─────┤
                                         retrait magasin ─────(vendeur)──▶ retirée ◀──────┘
```

`WorkflowCommande::enregistrer()` refuse les sauts d'état, les retours en arrière, l'état d'un autre rôle, et « livrée » / « retirée » incompatibles avec le mode de livraison. Le SI Logistique peut enregistrer « en préparation » et « expédiée » via l'API.

## Conformité aux documents ALL4SPORT

| Source                    | Exigence                                                                                                                                     | Réalisation                                                                  |
| ------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| Besoins — module a        | Produits triés par rayon                                                                                                                     | `/catalogue`, tri par rayon par défaut                                       |
| Besoins — module a        | Prix, lieu de disponibilité, quantité par lieu                                                                                               | Fiche produit : Internet + chaque magasin                                    |
| Besoins — module a        | Produit identifié selon les pratiques                                                                                                        | Référence fournisseur + rayon + 12 chiffres                                  |
| Besoins — module b        | États transmise → livrée / retirée, saisis par chaque acteur                                                                                 | `WorkflowCommande`, back-office par rôle                                     |
| Besoins — module b        | Historique client : date, produits, quantités, prix unitaire, sous-total, total, lieu d'expédition, adresse                                  | `/compte/commandes`                                                          |
| Besoins — module b        | Client identifié selon les pratiques                                                                                                         | Code `CLI` + 8 chiffres                                                      |
| Besoins — infrastructure  | Cahier des charges avec le lot C                                                                                                             | [cahier-des-charges-infrastructure.md](cahier-des-charges-infrastructure.md) |
| Besoins — méthodologie    | Livraisons progressives, recette                                                                                                             | [deploiement.md](deploiement.md), [recette.md](recette.md)                   |
| Besoins — sécurité        | Authentification collaborateurs et clients, échanges chiffrés, SI disjoints                                                                  | Deux guards, HTTPS/HSTS, API à jeton                                         |
| Contexte — produit        | Fournisseur, rayon, coût HT, description, 1 à 5 photos, stock Internet, stock par magasin, lieu de stockage                                  | Modèle `Produit` + back-office                                               |
| Contexte — client         | Code, nom, adresse, email, téléphone, naissance, enfants (nombre, âge), sports, historique, dernier achat                                    | Modèle `Client` + `/compte`                                                  |
| Contexte — workflow       | Compte client avec magasin par défaut                                                                                                        | `clients.magasin_id`                                                         |
| Contexte — livraison      | Retrait gratuit, domicile 10 €                                                                                                               | `ModeLivraison::frais()`                                                     |
| Contexte — zones          | Chaque magasin dépend d'un entrepôt                                                                                                          | `magasins.entrepot_id`, entrepôt expéditeur                                  |
| Contexte — implantation   | 3 entrepôts, 12 magasins                                                                                                                     | `ReseauSeeder`                                                               |
| Logigramme vente en ligne | Navigation invité, panier, connexion, panier vide ?, mode de livraison, vérification des stocks, annulation / retour panier, ajout au compte | Panier session + fusion, `CommandeController`, `ServiceCommande`             |

## Qualité

```powershell
php artisan test --compact     # 449 tests Pest
vendor/bin/pint --test
composer types:check           # PHPStan
npm run check                  # lint + format front
npm run types:check
```
