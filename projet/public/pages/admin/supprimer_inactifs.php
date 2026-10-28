<?php
// RGPD : supprime les comptes sans connexion depuis X ans (durée fixée dans InfosSite)
require_once __DIR__ . '/../../includes/config.php';
Session::exigerRole(['gestionnaire'], '../../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::rediriger('admin.php');
}

$nb = (new UtilisateurRepository())->supprimerInactifs(InfosSite::ANNEES_CONSERVATION_INACTIF);

Session::rediriger('admin.php?msg=inactifs&nb=' . $nb);
