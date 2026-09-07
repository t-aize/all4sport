# Guide de contribution

Ce dépôt est un projet **privé**, réalisé dans le cadre de l'appel
d'offre AO-03569 « Refonte du SI » (Lot A – Vente) pour l'entreprise
fictive ALL4SPORT. Les contributions sont réservées aux membres de
l'équipe projet et aux personnes explicitement autorisées.

## Avant de contribuer

- Assurez-vous d'avoir les droits d'accès au dépôt.
- Vérifiez que votre environnement local fonctionne (`composer install`,
  `npm install`, `.env` configuré).

## Workflow

1. Créez une branche depuis `main` avec un nom explicite
   (`feature/catalogue-produits`, `fix/panier-quantite`, …).
2. Faites des commits clairs et atomiques.
3. Avant d'ouvrir une pull request, vérifiez que le projet est propre :
    - `vendor/bin/pint --dirty --format agent` (formatage PHP)
    - `php artisan test --compact` (tests PHP)
    - `npm run check` (lint + format + type-check front)
4. Ouvrez une pull request vers `main` avec une description du besoin
   couvert et, si pertinent, des captures d'écran.
5. Une relecture par au moins un autre membre de l'équipe est requise
   avant fusion.

## Conventions

- Suivez les conventions déjà en place dans le code (voir `CLAUDE.md` et
  `.ai/rules` si présent).
- Toute nouvelle dépendance doit être discutée avec l'équipe avant ajout.

## Signaler un problème

Pour un bug fonctionnel, ouvrez une issue avec les étapes de
reproduction. Pour une faille de sécurité, suivez la procédure décrite
dans [`SECURITY.md`](./SECURITY.md) plutôt qu'une issue publique.
