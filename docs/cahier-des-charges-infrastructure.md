# Cahier des charges technique — Infrastructure (établi avec le Lot C)

Besoins en infrastructure système et réseau des modules du Lot A « Vente », à valider conjointement avec le Lot C « Systèmes et réseaux ».

## 1. Architecture applicative

| Élément                | Choix                                                                       |
| ---------------------- | --------------------------------------------------------------------------- |
| Langage                | PHP 8.5 (Laravel 13)                                                        |
| Front-end              | React 19 + Inertia 3, compilé par Vite (fichiers statiques)                 |
| Base de données        | MySQL 8 / MariaDB 11 en production (SQLite en développement)                |
| Sessions, cache, files | Base de données (drivers `database`) ou Redis                               |
| Serveur web            | Nginx + PHP-FPM                                                             |
| Modules livrés         | Site marchand (public), back-office (collaborateurs), API `/api/v1` (Lot B) |

## 2. Deux SI disjoints

```
           Internet (HTTPS 443)
                  │
           ┌──────┴──────┐  DMZ Vente
           │ Reverse proxy│  (TLS, WAF)
           └──────┬──────┘
                  │
     ┌────────────┴────────────┐   LAN Vente
     │ Serveur applicatif Vente │──── Base de données Vente (3306, réseau privé)
     └────────────┬────────────┘
                  │ HTTPS + jeton (API /api/v1)
     ┌────────────┴────────────┐   SI Logistique (Lot B) — réseau séparé
     │ Applications Logistique  │
     └─────────────────────────┘
```

- Aucune base de données partagée : la Logistique n'accède qu'à l'API, en HTTPS, avec un jeton.
- La base de données Vente n'est joignable que depuis le serveur applicatif (pare-feu).
- Les magasins (12 sites) accèdent au back-office via Internet en HTTPS ou via VPN site-à-site fourni par le Lot C.

## 3. Dimensionnement indicatif

| Ressource          | Production                    | Recette          |
| ------------------ | ----------------------------- | ---------------- |
| Serveur applicatif | 2 vCPU, 4 Go RAM, 40 Go SSD   | 1 vCPU, 2 Go RAM |
| Base de données    | 2 vCPU, 4 Go RAM, 50 Go SSD   | Mutualisée       |
| Stockage photos    | 20 Go (disque `public` ou S3) | 5 Go             |
| Bande passante     | 100 Mbit/s                    | —                |

Volumétrie : ~1 000 à 10 000 produits (5 photos max), quelques milliers de clients, 200 collaborateurs.

## 4. Réseau et flux

| Source                | Destination         | Port     | Protocole            |
| --------------------- | ------------------- | -------- | -------------------- |
| Internet              | Reverse proxy Vente | 443      | HTTPS                |
| Internet              | Reverse proxy Vente | 80       | Redirection vers 443 |
| Reverse proxy         | Serveur applicatif  | 443/8080 | HTTPS/HTTP interne   |
| Serveur applicatif    | Base de données     | 3306     | TCP (TLS recommandé) |
| SI Logistique (Lot B) | API Vente `/api/v1` | 443      | HTTPS + Bearer       |
| Serveur applicatif    | Serveur SMTP        | 587      | SMTP + STARTTLS      |

## 5. Sécurité (exigences « Sécurité du SI »)

- **Chiffrement** : certificat TLS (Let's Encrypt ou AC interne), TLS 1.2 minimum, HSTS activé par l'application.
- **Authentification** : clients (email + mot de passe) et collaborateurs (identifiant + mot de passe) sur deux espaces séparés ; mots de passe hachés (bcrypt) ; limitation des tentatives de connexion.
- **Sessions** : cookies `Secure`, `HttpOnly`, `SameSite=Lax`, session chiffrée (`SESSION_ENCRYPT=true`).
- **Secrets** : `.env` hors du dépôt Git, droits `600` ; jeton API Logistique généré par `php artisan logistique:jeton`.
- **Sauvegardes** : sauvegarde quotidienne chiffrée de la base et des photos, rétention 30 jours, test de restauration mensuel.
- **Supervision** : URL de santé `/up`, journaux applicatifs centralisés, alertes sur erreurs 5xx.
- **Mises à jour** : correctifs de sécurité OS et PHP mensuels ; dépendances suivies par Dependabot.

## 6. Environnements

| Environnement | Usage                                    | Données                        |
| ------------- | ---------------------------------------- | ------------------------------ |
| Développement | Postes des développeurs (`composer dev`) | Jeu de démonstration (seeders) |
| Recette       | Validation avec ALL4SPORT                | Jeu de démonstration           |
| Production    | Exploitation                             | Données réelles                |

Voir [deploiement.md](deploiement.md) pour la mise en production.
