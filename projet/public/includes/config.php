<?php
// ============================================================
// Fichier inclus en premier sur toutes les pages.
//  1. paramètres de connexion à la base
//  2. chargement automatique des classes (dossier projet/src/)
//  3. démarrage de la session
// Les pages n'utilisent plus $pdo : elles passent par les classes Manager.
// ============================================================

// ---------- 1. Base de données ----------
const BDD_HOTE = 'localhost';
const BDD_NOM = 'hsp_gdh';
const BDD_UTILISATEUR = 'root';
const BDD_MOT_DE_PASSE = '';

// ---------- 2. Autoload ----------
// Quand une page fait "new OffreManager()", PHP appelle cette fonction
// qui va chercher le fichier src/Manager/OffreManager.php (ou Entite/, Core/).
spl_autoload_register(function (string $classe): void {
    $dossierSrc = __DIR__ . '/../../src/';
    foreach (['Core', 'Entite', 'Manager'] as $sousDossier) {
        $fichier = $dossierSrc . $sousDossier . '/' . $classe . '.php';
        if (is_file($fichier)) {
            require_once $fichier;
            return;
        }
    }
});

// ---------- 3. Session ----------
Session::demarrer();
