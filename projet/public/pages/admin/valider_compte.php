<?php
// Traitement des boutons Valider / Refuser du tableau de bord admin

require_once __DIR__ . '/../../includes/config.php';
Session::exigerRole(['gestionnaire'], '../../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::rediriger('admin.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

if (!$id || !in_array($action, ['valider', 'refuser'], true)) {
    Session::rediriger('admin.php');
}

$manager = new UtilisateurManager();

if ($action === 'valider') {
    // On valide le compte et on note quel gestionnaire l'a validé
    $manager->valider($id, Session::getIdUtilisateur());
    Session::rediriger('admin.php?msg=valide');
}

// La suppression cascade automatiquement sur etudiant/medecin/partenaire/gestionnaire
$manager->supprimerEnAttente($id);
Session::rediriger('admin.php?msg=refuse');
