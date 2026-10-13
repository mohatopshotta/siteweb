<?php

// ============================================================
// Protection des pages du module Administration
// A inclure en tout premier sur chaque page de pages/admin/
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Variables posées à la connexion (pages/connexion.php) :
// $_SESSION['id_utilisateur'] et $_SESSION['role']
// role = 'gestionnaire' pour un admin (table gestionnaire)
if (!isset($_SESSION['id_utilisateur']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'gestionnaire') {
    header('Location: /pages/connexion.php');
    exit;
}