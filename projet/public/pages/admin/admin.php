<?php
// ============================================================
// pages/admin/admin.php
// Tableau de bord du module Administration
// ============================================================

// On protège la page : seul un gestionnaire connecté peut y accéder
require_once __DIR__ . '/../../includes/auth_admin.php';

// Connexion à la base de données ($pdo)
require_once __DIR__ . '/../../includes/config.php';
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

    <!-- Le reste du contenu arrive dans les prochaines étapes -->

</div>
</body>
</html>