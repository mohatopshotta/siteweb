<?php
// ============================================================
// pages/mes_donnees.php
// RGPD : droit d'accès et droit à la portabilité.
// Télécharge un fichier JSON avec toutes les données de l'utilisateur connecté.
// Lien depuis la page « Mon profil ».
// ============================================================
require __DIR__ . '/../includes/config.php';

Session::exigerConnexion('../');

$donnees = (new DonneesPersonnellesRepository())->exporter(Session::getIdUtilisateur());

if ($donnees === null) {
    Session::rediriger('profil.php');
}

$nomFichier = 'mes_donnees_hsp_' . date('Y-m-d') . '.json';

// Le navigateur télécharge le fichier au lieu de l'afficher
header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nomFichier . '"');
header('Cache-Control: no-store');

echo json_encode($donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
