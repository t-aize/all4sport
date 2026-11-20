# Politique de sécurité

## Portée

Ce dépôt est un **projet pédagogique archivé**, réalisé dans le cadre du BTS SIO
(option SLAM) pour le cas d'étude fictif ALL4SPORT (appel d'offre AO-03569
"Refonte du SI", Lot A - Vente). Il n'est déployé nulle part et **n'est pas
destiné à un usage en production**.

## Versions supportées

Aucune. Le projet est terminé : il ne reçoit plus de mises à jour, y compris
de sécurité.

## Signaler une vulnérabilité

Les signalements ne seront pas traités. Si vous réutilisez une partie de ce
code, vous êtes responsable de son audit et de sa mise à jour.

## Mesures de sécurité mises en œuvre (pour mémoire)

Le projet illustre les exigences de sécurité du cas d'étude :

- deux espaces d'authentification cloisonnés (clients et collaborateurs),
  mots de passe hachés, limitation des tentatives de connexion ;
- sessions chiffrées, HTTPS forcé en production, en-têtes de sécurité ;
- séparation des SI Vente et Logistique : échanges uniquement via une API
  protégée par jeton (voir [docs/api-logistique.md](docs/api-logistique.md)).

Les comptes de démonstration (mot de passe `password`) n'existent que dans
une base locale générée par les seeders.
