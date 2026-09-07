# ALL4SPORT

## Le contexte

ALL4SPORT est une entreprise (fictive) de vente d'articles de sport, avec deux activités bien séparées :

- **La logistique** (entrepôts, stockage, transport)
- **La vente** (magasins physiques + boutique en ligne)

L'entreprise a lancé un appel d'offre (**AO-03569 « Refonte du SI »**) découpé en 3 lots :

- **Lot A – Vente** (celui qui vous concerne)
- Lot B – Logistique
- Lot C – Systèmes et réseaux

## Ce qui est demandé dans le Lot A (Vente)

Le périmètre couvre la refonte du SI pour les **magasins** et le **service de vente en ligne**, avec deux modules à livrer :

**1. Module site marchand**

- Catalogue produits classé par rayon (sport)
- Fiche produit détaillée : prix, disponibilité (stock magasin ou internet), quantité par lieu
- Chaque produit a une référence unique (3 lettres fournisseur + 3 lettres rayon + numéro auto-incrémenté sur 12 chiffres)

**2. Module de traitement des commandes**

- Suivi de commande avec 6 états : _transmise → validée → en préparation → expédiée → livrée/retirée_ (chaque état est mis à jour manuellement par un acteur différent : service client, opérateur, livreur ou vendeur)
- Historique de commande accessible au client (date, produits, quantités, prix, adresse de livraison…)
- Fiche client précise (code client « CLI » + 8 chiffres, coordonnées, enfants, sports pratiqués, historique d'achats…)

**Contraintes transverses importantes :**

- Authentification obligatoire pour les clients ET les collaborateurs
- Chiffrement des échanges après authentification
- Les 2 activités (vente/logistique) doivent rester sur des **SI séparés**, avec des échanges de données sécurisés entre eux (pas d'interconnexion directe)
- Livraisons progressives du projet + recette technique/fonctionnelle à chaque livraison

## Le processus métier (d'après le logigramme)

Le parcours client type est : connexion (ou navigation invité) → ajout d'articles au panier → validation du panier → choix du mode de livraison (retrait magasin gratuit / livraison à domicile 10€) → vérification des stocks → rattachement de la commande au compte client. Le workflow détaillé (vente en ligne vs vente en magasin) figure dans le document « Contexte ALL4SPORT ».

## Ce qu'il vous reste probablement à faire

Sans connaître l'énoncé précis de votre exercice (barème, livrables attendus), voici typiquement ce qu'un projet comme celui-ci demande, étape par étape :

1. **Analyse des besoins** : reformuler/valider les besoins fonctionnels ci-dessus (produits, clients, commandes, états)
2. **Modélisation des données** : MCD/MLD (ou modèle de classes) pour Produit, Client, Commande, LigneCommande, Magasin, Stock…
3. **Modélisation fonctionnelle** : diagrammes UML (cas d'utilisation, séquence pour le tunnel d'achat, éventuellement un diagramme d'états pour le cycle de vie de la commande)
4. **Spécifications techniques** : rédiger le cahier des charges techniques (infra, sécurité, échanges chiffrés) en lien avec le lot C
5. **Développement / prototype** du module site marchand et/ou du module commandes, selon ce qui est demandé
6. **Plan de recette** : définir les tests fonctionnels/techniques pour la livraison
