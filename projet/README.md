# Site web GDH – Hôpital Sud Paris

Site web PHP / MySQL de mise en relation entre **étudiants en santé**, **médecins**, **partenaires** (entreprises) et le **groupe hospitalier GDH** : offres de stage et d'emploi, événements, forum, annuaires.
Projet de BTS SIO (option SLAM), réalisé en équipe.

## Équipe

| Membre | Parties travaillées (d'après l'historique Git) |
|---|---|
| Mohamed | Architecture POO, réglages généraux du projet, comptes |
| Younes | Mot de passe oublié, limite de tentatives de connexion, données de démo, accueil |
| Ilyes | Profil, forum, administration, RGPD, candidatures |
| Rayan | Offres (liste, détail, création, gestion) |
| Suli | Événements (création, inscription, annulation, export, calendrier) |

## Prérequis

- PHP 8.0 ou plus (testé avec PHP 8.3), extensions `pdo_mysql` et `fileinfo`
- MySQL ou MariaDB (testé avec MySQL 9 et MariaDB 10.11)
- Sous Windows : WAMP convient (Apache + MySQL + phpMyAdmin)

## Installation

1. **Récupérer le projet** : `git clone https://github.com/mohatopshotta/siteweb` (ou copier le dossier dans `C:\wamp64\www\`).
2. **Créer la base** : dans phpMyAdmin, créer une base `hsp_gdh` (interclassement `utf8mb4_unicode_ci`).
3. **Importer les fichiers SQL, dans cet ordre** (onglet *Importer*, base `hsp_gdh`) :

   | Ordre | Fichier | Contenu |
   |---|---|---|
   | 1 | `projet/database/hsp_gdh.sql` | Toutes les tables + le compte gestionnaire de test |
   | 2 | `projet/database/demo/1_etudiants.sql` | 5 établissements et 15 étudiants (démo) |
   | 3 | `projet/database/demo/2_medecins.sql` | 15 médecins (démo) |
   | 4 | `projet/database/demo/3_partenaires.sql` | 3 entreprises, 4 partenaires, 7 offres et 6 candidatures (démo) |
   | 5 | `projet/database/demo/4_evenements_forum.sql` | 6 événements avec inscriptions, 5 posts de forum avec réponses (démo) |

   Les fichiers de démo sont facultatifs mais **sans eux le site est vide**. Ils doivent être importés dans l'ordre (le dernier utilise les comptes des trois précédents). On peut les réimporter : ils effacent d'abord leurs anciennes données.
4. **Vérifier la connexion** à la base dans `projet/public/includes/config.php` (`BDD_HOTE`, `BDD_NOM`, `BDD_UTILISATEUR`, `BDD_MOT_DE_PASSE`). Par défaut : `localhost`, `hsp_gdh`, `root`, mot de passe vide (réglage WAMP).
5. **Lancer le site** :
   - avec le serveur intégré de PHP : `php -S localhost:8000 -t projet/public`, puis ouvrir <http://localhost:8000/>
   - ou avec WAMP : ouvrir <http://localhost/siteweb/projet/public/>

### Si ta base existe déjà (mise à jour)

Une base créée avant l'ajout de l'annulation et de l'heure de fin des événements doit recevoir ces 2 colonnes, **une seule fois** (onglet SQL de phpMyAdmin) :

```sql
ALTER TABLE evenement ADD COLUMN annule TINYINT(1) NOT NULL DEFAULT 0 AFTER nombre_places;
ALTER TABLE evenement ADD COLUMN date_fin DATETIME NULL AFTER date_evenement;
```

Sans elles, l'accueil et la liste des événements affichent une erreur. « Duplicate column name » veut dire que c'est déjà fait.

## Comptes de test

| Rôle | Email | Mot de passe |
|---|---|---|
| Gestionnaire | `admin@test.com` | `admin1234` |
| Étudiant | `lea.martin@demo-hsp.test` | `Demo-Hsp2026!x` |
| Médecin | `camille.lefevre@demo-hsp.test` | `Demo-Hsp2026!x` |
| Partenaire | `karim.benali@demo-hsp.test` | `Demo-Hsp2026!x` |

Les 15 étudiants (`lea.martin`, `lucas.petit`, …), les 15 médecins et les 4 partenaires ont le même mot de passe de démo.

## Rôles et droits

| Rôle | Peut faire |
|---|---|
| Visiteur | Voir l'accueil, la page contact, les mentions légales, la politique de confidentialité ; s'inscrire ; se connecter |
| Étudiant | Consulter et postuler aux offres, suivre ses candidatures, s'inscrire aux événements, forum, annuaire des médecins, profil et CV |
| Médecin | Événements (en créer et les gérer), forum, annuaires des médecins et des étudiants |
| Partenaire | Créer et gérer des offres, créer et gérer des événements, forum |
| Gestionnaire | Valider les comptes, supprimer les comptes inactifs, créer des offres du groupe, tout consulter en lecture seule |

Un compte créé par inscription doit être **validé par le gestionnaire** avant de pouvoir se connecter.

## Fonctionnalités par module

- **Comptes** : inscription par rôle (étudiant, médecin, partenaire), validation par le gestionnaire, connexion limitée à 5 tentatives, mot de passe oublié, changement de mot de passe, dépôt de CV (PDF).
- **Offres** : liste filtrable, détail, candidature avec motivation, suivi des candidatures, mail à l'étudiant retenu, création et clôture par le partenaire ou le gestionnaire.
- **Événements** : liste avec recherche, tri et pagination, fiche détaillée, inscription et désinscription, jauge de places, heure de fin facultative, annulation avec mail aux participants, export CSV des inscrits, ajout au calendrier (.ics).
- **Forum** : sections, posts, réponses, modification et suppression.
- **Annuaires** : médecins et étudiants.
- **RGPD** : consentement à l'inscription, mentions légales, politique de confidentialité, téléchargement de ses données (JSON), suppression des comptes inactifs.

## Structure du projet

```
projet/
├── database/      hsp_gdh.sql (structure) et demo/ (données de démonstration)
├── public/        tout ce qui est accessible depuis le navigateur
│   ├── accueil/   page d'accueil
│   ├── pages/     une page par écran (connexion, offres/, evenement/, forum/, admin/, annuaire/…)
│   ├── includes/  config.php, header.php, footer.php, page_erreur.php (communs à toutes les pages)
│   ├── assets/    css/ et js/
│   └── uploads/   CV déposés (aucun script ne peut s'y exécuter)
└── src/           le code orienté objet (voir src/README.md)
```

Documentation de l'architecture : [`projet/src/README.md`](projet/src/README.md). Cahier de tests : [`TESTS.md`](TESTS.md).

## Sécurité

- Requêtes SQL préparées (PDO) ; la seule valeur insérée dans une requête passe par une liste blanche.
- Jeton CSRF vérifié automatiquement sur tous les formulaires envoyés en POST.
- Mots de passe hachés (`password_hash`), 12 caractères minimum avec majuscule, minuscule, chiffre et symbole.
- Session : identifiant régénéré à la connexion, cookie `HttpOnly` et `SameSite=Lax`.
- Fichiers envoyés : seul un vrai PDF est accepté ; le dossier `uploads/` interdit l'exécution de scripts.
- Sorties HTML échappées avec `htmlspecialchars`.

## Avant une mise en ligne

- Mettre `MODE_DEV` à `false` dans `config.php` (en développement, les liens de réinitialisation de mot de passe sont écrits dans `projet/mails_dev.txt`).
- Changer `URL_SITE`, les identifiants de base de données, et **supprimer ou changer le compte `admin@test.com`**.
- Ne **pas** importer les fichiers de `database/demo/`.
- Compléter les informations entre crochets dans `src/modele/InfosSite.php` (mentions légales).
- Activer HTTPS (le cookie de session devient alors `secure` automatiquement).
- Les mails partent avec la fonction `mail()` de PHP : il faut un serveur de mail configuré (en local sous WAMP, ils ne partent généralement pas).
