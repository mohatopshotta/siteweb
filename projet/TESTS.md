# Cahier de tests (recette)

Tests exécutés le **24/11/2026** sur le projet tel qu'il est dans ce dépôt, avec une base neuve : `hsp_gdh.sql` puis les 4 fichiers de `database/demo/` (voir le `README.md`).
Les tests ont été rejoués par un script qui simule un navigateur (connexion, clics, envoi de formulaires) avec chaque type de compte.

**Résultat : 47 tests sur 47 réussis**, aucune erreur PHP dans le journal du serveur.

Comptes utilisés : étudiante `lea.martin@demo-hsp.test` et étudiant `hugo.leroy@demo-hsp.test`, médecin `camille.lefevre@demo-hsp.test`, partenaire `karim.benali@demo-hsp.test` (mot de passe `Demo-Hsp2026!x`), gestionnaire `admin@test.com` (`admin1234`).


## Accès

| N° | Test | Résultat attendu | Résultat |
|---|---|---|---|
| T01 | Visiteur sur une page protégée | Redirection vers la connexion | ✅ OK |
| T02 | Connexion avec un mauvais mot de passe | Reste sur la page, message d'erreur | ✅ OK |
| T03 | Connexion étudiant correcte | Redirection vers l'accueil | ✅ OK |
| T04 | Connexion gestionnaire (admin@test.com) | Connecté, menu Administration | ✅ OK |
| T05 | Étudiant : créer un événement | Refusé (redirection) | ✅ OK |
| T06 | Médecin : créer un événement | Formulaire affiché | ✅ OK |
| T07 | Étudiant : page d'administration | Refusé (redirection) | ✅ OK |
| T08 | Gestionnaire : page d'administration | Page affichée | ✅ OK |
| T09 | Gestionnaire sur un événement | Lecture seule : pas de bouton S'inscrire | ✅ OK |

## Sécurité

| N° | Test | Résultat attendu | Résultat |
|---|---|---|---|
| T10 | Formulaire sans jeton CSRF | Refusé (403) | ✅ OK |
| T11 | Cookie de session | HttpOnly et SameSite=Lax présents | ✅ OK |
| T12 | Mot de passe faible à l'inscription | Refusé avec message | ✅ OK |

## Événements

| N° | Test | Résultat attendu | Résultat |
|---|---|---|---|
| T13 | Liste des événements à venir | 4 à venir + 1 annulé, l'événement passé est masqué | ✅ OK |
| T14 | Afficher les événements passés | L'événement passé apparaît avec « Terminé » | ✅ OK |
| T15 | Recherche « bloc » | 1 seul résultat | ✅ OK |
| T16 | Tri invalide (tentative d'injection) | Ignoré, la page s'affiche | ✅ OK |
| T17 | Fiche d'un événement | Horaire « de … à … », jauge de places | ✅ OK |
| T18 | S'inscrire à un événement | Inscription enregistrée, bouton « Se désinscrire » | ✅ OK |
| T19 | Se désinscrire | Désinscription, bouton « S'inscrire » de retour | ✅ OK |
| T20 | Événement annulé | Badge Annulé, pas de bouton S'inscrire | ✅ OK |
| T21 | Événement complet (3/3) | Badge Complet, pas de bouton S'inscrire | ✅ OK |
| T22 | Créer : heure de fin avant le début | Refusé avec message | ✅ OK |
| T23 | Créer : date dans le passé | Refusé avec message | ✅ OK |
| T24 | Créer un événement valide | Créé, le médecin en est l'organisateur | ✅ OK |
| T25 | Affichage de l'heure de fin | « de 10h00 à 12h30 » | ✅ OK |
| T26 | Gérer sans être organisateur | Page « Accès réservé » (403) | ✅ OK |
| T27 | Événement inexistant | Page « introuvable » (404) avec menu | ✅ OK |
| T28 | Ajouter au calendrier (.ics) | Fichier avec la vraie heure de fin | ✅ OK |
| T29 | Export CSV par l'organisateur | Fichier CSV avec en-têtes | ✅ OK |
| T30 | Export CSV par un non-organisateur | Refusé (403) | ✅ OK |
| T31 | Annuler un événement | Badge Annulé, inscriptions fermées | ✅ OK |
| T32 | Rétablir un événement | Inscriptions rouvertes | ✅ OK |
| T33 | Supprimer un événement | Supprimé de la base | ✅ OK |

## Offres

| N° | Test | Résultat attendu | Résultat |
|---|---|---|---|
| T34 | Liste des offres | 6 offres ouvertes, l'offre clôturée est masquée | ✅ OK |
| T35 | Fiche d'une offre | Description, missions et formulaire de candidature | ✅ OK |
| T36 | Offre déjà candidatée | Pas de second formulaire, message « déjà » | ✅ OK |
| T37 | Mes candidatures | Ma candidature avec son statut | ✅ OK |
| T38 | Partenaire : créer une offre | Formulaire affiché | ✅ OK |
| T39 | Médecin : créer une offre | Refusé (redirection) | ✅ OK |

## Forum

| N° | Test | Résultat attendu | Résultat |
|---|---|---|---|
| T40 | Liste des sections du forum | 3 sections avec lien | ✅ OK |
| T41 | Posts d'une section | Les posts de la section Étudiants | ✅ OK |
| T42 | Un post et ses réponses | Post + 2 réponses | ✅ OK |

## RGPD

| N° | Test | Résultat attendu | Résultat |
|---|---|---|---|
| T43 | Télécharger mes données | Fichier JSON avec mes informations | ✅ OK |
| T44 | Mentions légales (visiteur) | Page accessible sans connexion | ✅ OK |
| T45 | Politique de confidentialité (visiteur) | Page accessible sans connexion | ✅ OK |

## Accessibilité

| N° | Test | Résultat attendu | Résultat |
|---|---|---|---|
| T46 | Lien « Aller au contenu » | Présent en haut des pages | ✅ OK |
| T47 | Page courante dans le menu | aria-current sur « Événements » | ✅ OK |

## Ce que ces tests ne couvrent pas

À vérifier à la main avant le rendu :

| À tester | Pourquoi |
|---|---|
| Réception réelle des mails (confirmation d'inscription, annulation, candidature retenue) | En local le serveur n'envoie pas de vrais mails ; seul le déclenchement est prévu dans le code |
| Affichage sur téléphone et dans plusieurs navigateurs (Chrome, Firefox, Edge) | Les tests ne regardent pas le rendu visuel |
| Inscription complète d'un médecin et d'un partenaire, puis validation par le gestionnaire | Seul le refus d'un mot de passe faible est testé |
| Dépôt d'un CV PDF (profil et candidature), et refus d'un autre type de fichier | Nécessite l'envoi réel d'un fichier |
| Navigation au clavier (touche Tab, lien « Aller au contenu ») | Test manuel |

## Rejouer les tests à la main

1. Importer la base comme indiqué dans le `README.md`.
2. Se connecter avec le compte du tableau, puis suivre la colonne « Test » et vérifier la colonne « Résultat attendu ».
