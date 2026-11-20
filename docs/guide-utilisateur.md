# Guide utilisateur

## Clients — site marchand

### Parcourir le catalogue

- **Accueil** : rayons (sports), nouveautés et liste des 12 magasins.
- **Catalogue** (`/catalogue`) : filtre par rayon, recherche (nom, référence, sport) et tri (rayon, prix, nouveautés).
- **Fiche produit** : photos (1 à 5), prix, référence, et disponibilité **par lieu** : stock Internet et stock de chaque magasin.

### Panier

- Ajoutez des articles avec ou sans compte. Sans compte, le panier est conservé dans votre session et **rejoint votre compte dès la connexion** (ou l'inscription).
- Modifiez les quantités, retirez un article ou videz le panier.

### Passer commande

1. **Valider mon panier** (connexion requise).
2. Choisir le **mode de livraison** :
    - **Retrait en magasin** — gratuit. Votre magasin par défaut est présélectionné ; la disponibilité de chaque magasin est indiquée.
    - **Livraison à domicile** — forfait de 10 €. L'adresse de votre compte est proposée ; la commande part de l'entrepôt de votre zone.
3. Accepter les CGV puis **Payer** (paiement simulé dans la version de démonstration).
4. Les stocks sont vérifiés : si un article manque, la validation est annulée et vous revenez au panier avec le détail.

### Mon compte (`/compte`)

- Informations personnelles et adresse complète, **magasin par défaut**, **sports pratiqués**, **enfants** (âge calculé automatiquement), mot de passe.
- **Mes commandes** : historique et détail de chaque commande (date, produits, quantités, prix unitaire, sous-total, total, lieu d'expédition, adresse de livraison) avec la frise de suivi.

## Collaborateurs — back-office (`/backoffice`)

Connexion avec votre identifiant (voir [comptes.md](comptes.md) pour la démonstration).

| Rôle           | Ce que vous faites                                                                      |
| -------------- | --------------------------------------------------------------------------------------- |
| Service client | Valide les commandes **transmises** → « validée »                                       |
| Opérateur      | Prépare puis expédie → « en préparation », « expédiée » ; gère produits et fournisseurs |
| Livreur        | Confirme la livraison à domicile → « livrée »                                           |
| Vendeur        | Confirme le retrait en magasin → « retirée » ; gère produits et fournisseurs            |

- **Tableau de bord** : commandes à traiter par vous, chiffre d'affaires, panier moyen, commandes par état, ventes des 6 derniers mois, meilleurs rayons, stocks faibles.
- **Commandes** : l'onglet « À traiter » liste celles qui attendent votre rôle. Ouvrez une commande puis cliquez **Passer à « … »**. L'historique garde la date et le collaborateur de chaque état.
- **Produits** : création (référence générée automatiquement), stock Internet, stock par magasin, lieu de stockage (ex. `M2D3.5`), 1 à 5 photos.
- **Fournisseurs** : le code (3 lettres) est proposé à partir du nom et figé dès qu'un produit l'utilise.
