# Politique de sécurité

## Portée

Ce dépôt est un projet privé réalisé dans le cadre de l'appel d'offre
AO-03569 « Refonte du SI » (Lot A – Vente) pour l'entreprise fictive
ALL4SPORT. Il n'est pas destiné à un usage en production ni ouvert au
public.

## Versions supportées

| Version | Support sécurité   |
| ------- | ------------------ |
| main    | :white_check_mark: |
| autres  | :x:                |

Seule la branche `main` fait l'objet d'un suivi de sécurité.

## Signaler une vulnérabilité

Si vous identifiez une faille de sécurité (authentification, chiffrement
des échanges, exposition de données clients, séparation des SI vente /
logistique, etc.) :

1. **Ne créez pas d'issue publique** décrivant la faille.
2. Contactez directement un membre de l'équipe projet ou l'encadrant du
   Lot A par un canal privé (e-mail, message direct).
3. Décrivez le problème avec un maximum de détails : étapes de
   reproduction, impact potentiel, éventuelle preuve de concept.

Un retour sera fait dans un délai raisonnable pour confirmer la prise en
compte, évaluer l'impact et planifier un correctif.

## Points de vigilance spécifiques au projet

- Authentification obligatoire (clients et collaborateurs) et chiffrement
  des échanges après authentification.
- Séparation stricte des SI Vente et Logistique : aucune interconnexion
  directe, seulement des échanges de données sécurisés.
- Protection des données personnelles des fiches client (coordonnées,
  historique d'achats, enfants, sports pratiqués…).
