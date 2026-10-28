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

$repository = new UtilisateurRepository();

if ($action === 'valider') {
    $repository->valider($id, Session::getIdUtilisateur());
    Session::rediriger('admin.php?msg=valide');
}

$repository->supprimerEnAttente($id);
Session::rediriger('admin.php?msg=refuse');
