# Cahier de recette technique et fonctionnelle — Lot A « Vente »

Document de recette prévu par la méthodologie de l'AO-03569 : chaque livraison est validée en présence du demandeur (ALL4SPORT) et du candidat. Chaque cas indique l'exigence couverte, la procédure, le résultat attendu et le test automatisé qui le prouve.

**Préparation :** `php artisan migrate:fresh --seed`, puis `composer dev`. Comptes : [comptes.md](comptes.md).

## 1. Recette technique

| N°   | Contrôle                    | Commande / procédure                        | Attendu                          |
| ---- | --------------------------- | ------------------------------------------- | -------------------------------- |
| T-01 | Suite de tests automatisés  | `php artisan test --compact`                | 100 % des tests passent          |
| T-02 | Style du code PHP           | `vendor/bin/pint --test`                    | Aucune anomalie                  |
| T-03 | Analyse statique            | `composer types:check`                      | 0 erreur PHPStan                 |
| T-04 | Front : lint, format, types | `npm run check` puis `npm run types:check`  | Aucune erreur                    |
| T-05 | Build de production         | `npm run build`                             | Build généré dans `public/build` |
| T-06 | Intégration continue        | GitHub Actions (`.github/workflows/ci.yml`) | Statut global vert               |
| T-07 | En-têtes de sécurité        | `curl -I https://…/`                        | HSTS, X-Frame-Options, nosniff   |
| T-08 | API sans jeton              | `GET /api/v1/stocks` sans `Authorization`   | `401`                            |

## 2. Recette fonctionnelle — Module site marchand

| N°   | Exigence (PDF besoins / contexte)                               | Procédure                             | Attendu                                      | Test                 |
| ---- | --------------------------------------------------------------- | ------------------------------------- | -------------------------------------------- | -------------------- |
| F-01 | Présentation des produits triés par rayon                       | Ouvrir `/catalogue`                   | Produits groupés par rayon, filtre par rayon | CatalogueTest        |
| F-02 | Fiche détaillée : prix de vente                                 | Ouvrir une fiche produit              | Prix TTC affiché                             | CatalogueTest        |
| F-03 | Lieu de disponibilité (Magasin / Internet)                      | Fiche produit, bloc « Disponibilité » | Stock Internet + chaque magasin              | CatalogueTest        |
| F-04 | Quantité disponible pour chaque lieu                            | Idem                                  | Quantité par lieu, total                     | CatalogueTest        |
| F-05 | Référence produit (3 l. fournisseur + 3 l. rayon + 12 chiffres) | Créer un produit au back-office       | Référence générée, ex. `ALTFOO000000000001`  | ReferenceProduitTest |
| F-06 | 1 à 5 photos par produit                                        | Back-office : ajouter 6 photos        | Refus au-delà de 5, au moins 1 exigée        | ProduitTest          |
| F-07 | Recherche et tri                                                | Rechercher « ballon », trier par prix | Résultats filtrés et triés                   | AccueilTest          |

## 3. Recette fonctionnelle — Processus de vente en ligne (logigramme)

| N°   | Étape du logigramme                        | Procédure                              | Attendu                                                   | Test         |
| ---- | ------------------------------------------ | -------------------------------------- | --------------------------------------------------------- | ------------ |
| V-01 | Navigation non authentifiée + ajout panier | Sans compte, ajouter un article        | Panier conservé (session), compteur mis à jour            | PanierTest   |
| V-02 | Validation du panier → connexion           | Cliquer « Valider » sans être connecté | Redirection vers la connexion                             | CommandeTest |
| V-03 | Connexion → fusion du panier               | Se connecter                           | Les articles visiteur rejoignent le panier du compte      | PanierTest   |
| V-04 | Panier vide ?                              | Valider un panier vide                 | Retour au panier avec message                             | CommandeTest |
| V-05 | Remplissage / modification du panier       | Changer les quantités, retirer, vider  | Totaux recalculés                                         | PanierTest   |
| V-06 | Mode de livraison : retrait magasin        | Choisir un magasin                     | Frais 0 €, magasin par défaut proposé                     | CommandeTest |
| V-07 | Mode de livraison : domicile               | Saisir l'adresse                       | Forfait 10 €, entrepôt de la zone d'expédition            | CommandeTest |
| V-08 | Vérification des stocks (OK)               | Commander une quantité disponible      | Commande créée, stock décrémenté                          | CommandeTest |
| V-09 | Vérification des stocks (KO)               | Commander plus que le stock            | Validation annulée, retour au panier, rien n'est créé     | CommandeTest |
| V-10 | Ajout de la commande au compte client      | Après paiement                         | Commande « transmise », date de dernier achat mise à jour | CommandeTest |

## 4. Recette fonctionnelle — Module de traitement des commandes

| N°   | Exigence                                                                                         | Procédure                                      | Attendu                                        | Test                    |
| ---- | ------------------------------------------------------------------------------------------------ | ---------------------------------------------- | ---------------------------------------------- | ----------------------- |
| C-01 | « transmise » : commande validée et payée                                                        | Passer commande                                | État transmise, sans collaborateur             | CommandeTest            |
| C-02 | « validée » par le service client                                                                | `service_client` → bouton « Passer à Validée » | État validée, historique avec le collaborateur | BackOffice/CommandeTest |
| C-03 | « en préparation » / « expédiée » (opérateur)                                                    | `operateur`                                    | États enregistrés dans l'ordre                 | BackOffice/CommandeTest |
| C-04 | « livrée » par le livreur                                                                        | `livreur`, commande à domicile expédiée        | État livrée                                    | BackOffice/CommandeTest |
| C-05 | « retirée » par le vendeur                                                                       | `vendeur`, retrait magasin expédié             | État retirée                                   | BackOffice/CommandeTest |
| C-06 | Un rôle ne fait que ses états                                                                    | `operateur` tente « validée »                  | Refus                                          | BackOffice/CommandeTest |
| C-07 | Pas de saut d'état                                                                               | Passer de validée à expédiée                   | Refus                                          | BackOffice/CommandeTest |
| C-08 | Historique client                                                                                | `/compte/commandes`                            | Liste des commandes du client uniquement       | HistoriqueCommandesTest |
| C-09 | Détail : date, produits, quantités, prix unitaire, sous-total, total, lieu d'expédition, adresse | Ouvrir une commande                            | Toutes les informations affichées              | HistoriqueCommandesTest |
| C-10 | Confidentialité                                                                                  | Ouvrir la commande d'un autre client           | 404                                            | SecuriteTest            |

## 5. Recette — Fichier client et sécurité

| N°   | Exigence                                           | Procédure                                   | Attendu                       | Test                          |
| ---- | -------------------------------------------------- | ------------------------------------------- | ----------------------------- | ----------------------------- |
| S-01 | Code client « CLI » + 8 chiffres                   | S'inscrire                                  | Code `CLI00000082` attribué   | ClientTest                    |
| S-02 | Nom, adresse complète, email, téléphone, naissance | `/compte`                                   | Modifiables                   | ProfilTest                    |
| S-03 | Nombre et âge des enfants                          | Ajouter / retirer un enfant                 | Âge calculé                   | ProfilTest                    |
| S-04 | Sports pratiqués                                   | Cocher des sports                           | Enregistrés                   | ProfilTest                    |
| S-05 | Magasin par défaut                                 | Choisir un magasin                          | Proposé au retrait            | ProfilTest / CommandeTest     |
| S-06 | Authentification des collaborateurs                | `/backoffice` sans être connecté            | Redirection vers la connexion | BackOffice/AuthenticationTest |
| S-07 | Authentification des clients                       | `/compte` sans être connecté                | Redirection vers `/login`     | ProfilTest                    |
| S-08 | Espaces cloisonnés                                 | Client connecté → `/backoffice`             | Refusé                        | SecuriteTest                  |
| S-09 | Mots de passe hachés                               | Lire la base                                | Hash bcrypt, jamais en clair  | SecuriteTest                  |
| S-10 | Échanges Vente ↔ Logistique sécurisés et chiffrés  | Voir [api-logistique.md](api-logistique.md) | Jeton + HTTPS + limitation    | LogistiqueApiTest             |

## Procès-verbal

| Livraison                       | Date | Résultat (OK / réserves) | Signature ALL4SPORT | Signature candidat |
| ------------------------------- | ---- | ------------------------ | ------------------- | ------------------ |
| Module site marchand            |      |                          |                     |                    |
| Module traitement des commandes |      |                          |                     |                    |
| API d'échange Lot B             |      |                          |                     |                    |
