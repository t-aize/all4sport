# API d'échange avec le Lot B "Logistique"

Les deux activités d'ALL4SPORT (vente et logistique) forment **deux SI disjoints** : ils échangent des données, sans interconnexion (pas de base partagée). Cette API REST, exposée par le SI Vente, est la seule porte d'entrée du SI Logistique.

- Préfixe : `/api/v1` - format JSON (`Accept: application/json`)
- Code : [routes/api.php](../routes/api.php), [app/Http/Controllers/Api/V1](../app/Http/Controllers/Api/V1)
- Tests : [tests/Feature/Api/LogistiqueApiTest.php](../tests/Feature/Api/LogistiqueApiTest.php)

## Sécurité

| Mesure           | Détail                                                                                                                |
| ---------------- | --------------------------------------------------------------------------------------------------------------------- |
| Authentification | Jeton partagé, en-tête `Authorization: Bearer <jeton>` (middleware `AuthentifierSystemeLogistique`)                   |
| Comparaison      | En temps constant (`hash_equals`) ; aucun jeton configuré = tout est refusé                                           |
| Chiffrement      | HTTPS obligatoire en production (réponse `426` sinon) + en-tête HSTS                                                  |
| Limitation       | 60 requêtes / minute / IP (limiteur `api`)                                                                            |
| Minimisation     | Les commandes exposent le code client et l'adresse de livraison, jamais l'email, le téléphone ni la date de naissance |

Générer un jeton puis le copier dans `.env` (`LOGISTIQUE_API_TOKEN=`) et le transmettre au Lot B par un canal sûr :

```powershell
php artisan logistique:jeton
```

## Points d'accès

| Méthode | URL                           | Rôle                                                         |
| ------- | ----------------------------- | ------------------------------------------------------------ |
| GET     | `/api/v1/stocks`              | Stocks paginés (`rayon`, `modifie_depuis`, `par_page`)       |
| GET     | `/api/v1/stocks/{reference}`  | Stock d'un produit                                           |
| PUT     | `/api/v1/stocks/{reference}`  | Met à jour stock Internet, lieu de stockage, stocks magasins |
| GET     | `/api/v1/commandes`           | Commandes d'un état (par défaut `validee` : à préparer)      |
| GET     | `/api/v1/commandes/{id}`      | Détail d'une commande                                        |
| POST    | `/api/v1/commandes/{id}/etat` | Enregistre `en_preparation` puis `expediee`                  |

### Exemple : synchronisation d'un stock

```http
PUT /api/v1/stocks/ALTFOO000000000001
Authorization: Bearer <jeton>
Content-Type: application/json

{
  "quantite_stock_internet": 150,
  "lieu_stockage": "M2D3.5",
  "stocks_magasins": [
    { "magasin_id": 1, "quantite_stock_magasin": 30 }
  ]
}
```

Le lieu de stockage suit l'identification des cellules d'entrepôt : module `M1`-`M6`, rangée `A`-`L`, section `1`-`12`, étagère `1`-`8` (ex. `M2D3.5`).

### Exemple : cycle logistique d'une commande

```http
GET  /api/v1/commandes?etat=validee
POST /api/v1/commandes/42/etat   {"etat": "en_preparation"}
POST /api/v1/commandes/42/etat   {"etat": "expediee"}
```

Le workflow est contrôlé côté Vente (`WorkflowCommande`) : impossible de sauter un état, et les états `validee`, `livree` et `retiree` restent réservés aux collaborateurs du back-office (`422` sinon).

## Codes de réponse

| Code  | Signification                                    |
| ----- | ------------------------------------------------ |
| `200` | Succès                                           |
| `401` | Jeton absent ou invalide                         |
| `404` | Produit ou commande inconnu                      |
| `422` | Données invalides ou transition d'état interdite |
| `426` | Requête HTTP en clair refusée (production)       |
| `429` | Trop de requêtes                                 |
