<?php
const BDD_HOTE = 'localhost';
const BDD_NOM = 'hsp_gdh';
const BDD_UTILISATEUR = 'root';
const BDD_MOT_DE_PASSE = '';

// Adresse du site (sert aux liens des emails). À changer à la mise en ligne : https://...
const URL_SITE = 'http://localhost:8000';
// Mode développement : à mettre à false à la mise en ligne
const MODE_DEV = true;

spl_autoload_register(function (string $classe): void {
    $dossierSrc = __DIR__ . '/../../src/';
    foreach (['modele', 'traitement', 'repository'] as $sousDossier) {
        $fichier = $dossierSrc . $sousDossier . '/' . $classe . '.php';
        if (is_file($fichier)) {
            require_once $fichier;
            return;
        }
    }
});

Session::demarrer();

// Tous les formulaires envoyés en POST doivent contenir le jeton CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::verifierCsrf();
}
