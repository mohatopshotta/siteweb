<?php

// ============================================================
// Protection des pages du module Administration
// A inclure en tout premier sur chaque page de pages/admin/
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Convention attendue du module Authentification :
// $_SESSION['user_id'] et $_SESSION['role'] posés à la connexion
// role = 'gestionnaire' pour un admin (table gestionnaire)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'gestionnaire') {
    header('Location: /pages/connexion.php');
    exit;
}