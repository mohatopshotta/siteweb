<?php
const BDD_HOTE = 'localhost';
const BDD_NOM = 'hsp_gdh';
const BDD_UTILISATEUR = 'root';
const BDD_MOT_DE_PASSE = '';

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
