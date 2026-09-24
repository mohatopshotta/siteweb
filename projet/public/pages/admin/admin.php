<?php
// ============================================================
// pages/admin/admin.php
// Tableau de bord du module Administration
// ============================================================

// On protège la page : seul un gestionnaire connecté peut y accéder
require_once __DIR__ . '/../../includes/auth_admin.php';

// Connexion à la base de données ($pdo)
require_once __DIR__ . '/../../includes/config.php';
// ------------------------------------------------------------
// A savoir : la table "utilisateur" n'a pas de colonne "role".
// Le rôle d'un compte, on le devine en regardant dans quelle table
// fille (etudiant / medecin / partenaire) il a une ligne.
// ------------------------------------------------------------

// VUE D'ENSEMBLE : quelques compteurs simples

// Nombre total de comptes déjà validés
$nbValides = $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE valide = 1")->fetchColumn();

// Nombre d'étudiants
$nbEtudiants = $pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();

// Nombre de médecins
$nbMedecins = $pdo->query("SELECT COUNT(*) FROM medecin")->fetchColumn();

// Nombre de partenaires
$nbPartenaires = $pdo->query("SELECT COUNT(*) FROM partenaire")->fetchColumn();

// Nombre de comptes en attente de validation
$nbEnAttente = $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE valide = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - HSP GDH</title>
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>

<div class="admin-container">
    <h1>Tableau de bord - Administration</h1>

    <section class="admin-section">
        <h2>Vue d'ensemble</h2>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-nombre"><?= $nbValides ?></span>
                <span class="stat-label">Comptes validés (total)</span>
            </div>
            <div class="stat-card">
                <span class="stat-nombre"><?= $nbEtudiants ?></span>
                <span class="stat-label">Étudiants</span>
            </div>
            <div class="stat-card">
                <span class="stat-nombre"><?= $nbMedecins ?></span>
                <span class="stat-label">Médecins</span>
            </div>
            <div class="stat-card">
                <span class="stat-nombre"><?= $nbPartenaires ?></span>
                <span class="stat-label">Partenaires</span>
            </div>
            <div class="stat-card <?= $nbEnAttente > 0 ? 'stat-alerte' : '' ?>">
                <span class="stat-nombre"><?= $nbEnAttente ?></span>
                <span class="stat-label">Comptes en attente</span>
            </div>
        </div>
    </section>


</div>
</body>
</html>