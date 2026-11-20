# Déploiement et environnement de production

Mise en production progressive des deux modules du Lot A (méthodologie « livraisons au fur et à mesure ») : d'abord le **site marchand** (catalogue, panier, compte client), puis le **traitement des commandes** (passage de commande, back-office, API Logistique). Les deux modules vivent dans la même application ; un module se « livre » en déployant la version qui le contient puis en passant sa recette ([recette.md](recette.md)).

## 1. Prérequis serveur

- PHP 8.5 avec les extensions `pdo_mysql`, `mbstring`, `openssl`, `intl`, `gd`, `fileinfo`, `bcmath`
- Composer 2, Node.js 22.18+ ou 24 LTS (uniquement pour compiler les assets)
- MySQL 8 / MariaDB 11, Nginx + PHP-FPM, certificat TLS
- Voir le dimensionnement dans [cahier-des-charges-infrastructure.md](cahier-des-charges-infrastructure.md)

## 2. Configuration de production (`.env`)

```dotenv
APP_NAME=ALL4SPORT
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.all4sport.local
APP_LOCALE=fr

DB_CONNECTION=mysql
DB_HOST=10.0.10.20
DB_DATABASE=all4sport_vente
DB_USERNAME=all4sport
DB_PASSWORD=********

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

LOGISTIQUE_API_TOKEN=********   # php artisan logistique:jeton
```

En production, l'application force HTTPS (`URL::forceHttps`), exige des mots de passe robustes (12 caractères, casse mixte, chiffres, symboles, non compromis) et interdit les commandes destructrices (`migrate:fresh`, `db:wipe`).

## 3. Première installation

```powershell
git clone https://github.com/t-aize/all4sport.git
cd all4sport
composer install --no-dev --optimize-autoloader
npm ci
npm run build
copy .env.example .env   # puis compléter le fichier
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

## 4. Mise à jour (chaque livraison)

```powershell
php artisan down
git pull
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
php artisan up
```

## 5. Exemple de configuration Nginx

```nginx
server {
    listen 80;
    server_name www.all4sport.local;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name www.all4sport.local;
    root /var/www/all4sport/public;

    ssl_certificate     /etc/ssl/all4sport/fullchain.pem;
    ssl_certificate_key /etc/ssl/all4sport/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    index index.php;
    client_max_body_size 20M;   # 5 photos de 4 Mo

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}
```

## 6. Tâches planifiées et files d'attente

```cron
* * * * * cd /var/www/all4sport && php artisan schedule:run >> /dev/null 2>&1
```

Le worker de files (`php artisan queue:work`) est géré par systemd ou Supervisor (envoi des emails de réinitialisation de mot de passe).

## 7. Vérifications après déploiement

- `https://www.all4sport.local/up` répond `200`
- Les en-têtes `Strict-Transport-Security` et `X-Frame-Options` sont présents
- Connexion client et collaborateur fonctionnelles
- `GET /api/v1/stocks` répond `401` sans jeton et `200` avec le jeton du Lot B
