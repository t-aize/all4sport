# ALL4SPORT — Lot A « Vente »

<p align="center"><img src="public/images/all4sport-banner.webp" alt="ALL4SPORT" width="560"></p>

Refonte du système d'information de vente d'**ALL4SPORT** (appel d'offre **AO-03569 « Refonte du SI »**, lot A) : un **site marchand** et un **module de traitement des commandes**, interopérables avec le Lot B « Logistique » et cadrés avec le Lot C « Systèmes et réseaux ».

## Fonctionnalités

**Site marchand**

- Accueil : rayons, nouveautés, réseau des 12 magasins
- Catalogue trié par rayon, recherche et tri ; fiche produit avec 1 à 5 photos, prix et **stock par lieu** (Internet + chaque magasin)
- Panier visiteur ou client, **fusionné à la connexion**
- Commande : retrait magasin gratuit ou livraison à domicile (10 €), **vérification des stocks**, retour au panier si indisponible
- Espace client : profil complet, magasin par défaut, sports pratiqués, enfants, mot de passe, **historique et suivi des commandes**

**Traitement des commandes (back-office)**

- Authentification des collaborateurs et rôles : service client, opérateur, livreur, vendeur
- Workflow **transmise → validée → en préparation → expédiée → livrée / retirée**, chaque état saisi par le rôle responsable, avec historique
- Tableau de bord (à traiter, chiffre d'affaires, ventes mensuelles, stocks faibles…)
- Gestion des produits (référence générée, stocks, photos) et des fournisseurs

**Interopérabilité et sécurité**

- API REST `/api/v1` pour le SI Logistique (stocks, commandes), jeton + HTTPS + limitation
- Deux espaces d'authentification cloisonnés, mots de passe hachés, sessions chiffrées, en-têtes de sécurité, HTTPS forcé en production

## Démarrage rapide

```powershell
composer setup
php artisan migrate:fresh --seed
composer dev
```

Ouvrir http://localhost:8000 — comptes de démonstration (mot de passe `password`) :

- Client : `client@all4sport.local`
- Back-office (`/backoffice`) : `service_client`, `operateur`, `livreur`, `vendeur`

## Documentation

| Document                                                                               | Contenu                                         |
| -------------------------------------------------------------------------------------- | ----------------------------------------------- |
| [docs/commandes.md](docs/commandes.md)                                                 | Commandes utiles (installation, tests, build)   |
| [docs/comptes.md](docs/comptes.md)                                                     | Comptes et jeu de données de démonstration      |
| [docs/guide-utilisateur.md](docs/guide-utilisateur.md)                                 | Guide clients et collaborateurs                 |
| [docs/architecture.md](docs/architecture.md)                                           | Documentation technique et conformité au besoin |
| [docs/api-logistique.md](docs/api-logistique.md)                                       | API d'échange avec le Lot B                     |
| [docs/recette.md](docs/recette.md)                                                     | Cahier de recette technique et fonctionnelle    |
| [docs/cahier-des-charges-infrastructure.md](docs/cahier-des-charges-infrastructure.md) | Besoins infrastructure (avec le Lot C)          |
| [docs/deploiement.md](docs/deploiement.md)                                             | Environnement de production et déploiement      |

## Qualité

```powershell
php artisan test --compact
composer types:check
npm run check
```

## Équipe

Projet réalisé par Taïze ([@t-aize](https://github.com/t-aize)) et Romain ([@romain-brl](https://github.com/romain-brl)).
