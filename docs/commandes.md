# Commandes du projet

Laravel + Inertia/React, SQLite par défaut (`DB_CONNECTION=sqlite`) : aucun serveur de base de données à installer.

## Première installation

```powershell
composer setup
```

Fait tout d'un coup : `composer install`, copie de `.env.example` vers `.env`, `key:generate`, `migrate --force`, `npm install` et `npm run build`.

Équivalent manuel :

```powershell
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
```

## Lancer le projet

```powershell
composer dev
```

Exécute `php artisan dev` (serveur + Vite). L'app est sur http://localhost:8000.

Ou séparément :

```powershell
php artisan serve
npm run dev
```

## Base de données

```powershell
php artisan migrate               # applique les migrations
php artisan migrate:status        # état des migrations
php artisan migrate:rollback      # annule le dernier batch
php artisan migrate:fresh         # supprime tout et relance les migrations
php artisan migrate:fresh --seed  # pareil, avec les seeders
php artisan db:seed               # lance les seeders seuls
```

> `migrate:fresh` efface toutes les données.

## Photos produits

Les photos sont stockées sur le disque `local`. Si elles ne s'affichent pas :

```powershell
php artisan storage:link
```

## Front

```powershell
npm run build        # build de production
npm run build:ssr    # build avec SSR
npm run check        # vérifications front
```

## Qualité et tests

```powershell
composer test                                  # config:clear, pint --test, phpstan, puis les tests
php artisan test --compact                     # tests seuls
php artisan test --compact --filter=NomDuTest  # un seul test
vendor/bin/pint --dirty                        # formate les fichiers PHP modifiés
composer lint                                  # pint sur tout le projet
composer types:check                           # phpstan
composer ci:check                              # check front + types + tests
```

## Utile

```powershell
php artisan route:list --except-vendor
php artisan optimize:clear     # vide les caches (config, routes, vues)
php artisan wayfinder:generate # régénère les routes TypeScript
php artisan queue:listen       # worker de queue (QUEUE_CONNECTION=database)
```

Erreur « Unable to locate file in Vite manifest » : lancer `npm run build` ou `composer dev`.
