<?php

require_once __DIR__ . '/../../includes/auth_admin.php';
require_once __DIR__ . '/../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

if (!$id || !in_array($action, ['valider', 'refuser'], true)) {
    header('Location: admin.php');
    exit;
}

if ($action === 'valider') {
    // On valide le compte et on note quel gestionnaire l'a validé
    $stmt = $pdo->prepare("
        UPDATE utilisateur
        SET valide = 1, id_validateur = ?
        WHERE id_utilisateur = ? AND valide = 0
    ");
    $stmt->execute([$_SESSION['user_id'], $id]);
    header('Location: admin.php?msg=valide');
    exit;
}

if ($action === 'refuser') {
    // La suppression cascade automatiquement sur etudiant/medecin/partenaire/gestionnaire
    $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ? AND valide = 0");
    $stmt->execute([$id]);
    header('Location: admin.php?msg=refuse');
    exit;
}