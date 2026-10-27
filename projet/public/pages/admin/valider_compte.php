<?php
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
    $manager->valider($id, Session::getIdUtilisateur());
    Session::rediriger('admin.php?msg=valide');
}

$manager->supprimerEnAttente($id);
Session::rediriger('admin.php?msg=refuse');
