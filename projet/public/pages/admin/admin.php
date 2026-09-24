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
// LISTE DES COMPTES EN ATTENTE
// On récupère juste les infos de base de la table utilisateur.
$stmt = $pdo->query("
    SELECT id_utilisateur, nom, prenom, email
    FROM utilisateur
    WHERE valide = 0
    ORDER BY id_utilisateur DESC
");
$comptesEnAttente = $stmt->fetchAll();

// Pour chaque compte en attente, on cherche son rôle avec 3 petites requêtes.
// C'est moins optimisé qu'un gros JOIN, mais beaucoup plus simple à lire.
foreach ($comptesEnAttente as $i => $compte) {
    $id = $compte['id_utilisateur'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM etudiant WHERE id_utilisateur = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $comptesEnAttente[$i]['role'] = 'Étudiant';
        continue; // on passe au compte suivant
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM medecin WHERE id_utilisateur = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $comptesEnAttente[$i]['role'] = 'Médecin';
        continue;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM partenaire WHERE id_utilisateur = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $comptesEnAttente[$i]['role'] = 'Partenaire';
        continue;
    }

    // Si aucune des 3 tables ne correspond, c'est un gestionnaire
    $comptesEnAttente[$i]['role'] = 'Gestionnaire';
}
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
    <section class="admin-section">
        <h2>Comptes en attente de validation</h2>

        <?php if (empty($comptesEnAttente)): ?>
            <p class="alerte alerte-info">Aucun compte en attente pour le moment.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($comptesEnAttente as $compte): ?>
                    <tr>
                        <td><?= htmlspecialchars($compte['nom']) ?></td>
                        <td><?= htmlspecialchars($compte['prenom']) ?></td>
                        <td><?= htmlspecialchars($compte['email']) ?></td>
                        <td><?= htmlspecialchars($compte['role']) ?></td>
                        <td class="admin-actions">
                            <!-- Chaque bouton envoie un POST vers valider_compte.php -->
                            <form action="valider_compte.php" method="POST" class="form-inline">
                                <input type="hidden" name="id" value="<?= $compte['id_utilisateur'] ?>">
                                <input type="hidden" name="action" value="valider">
                                <button type="submit" class="btn btn-valider">Valider</button>
                            </form>

                            <form action="valider_compte.php" method="POST" class="form-inline"
                                  onsubmit="return confirm('Refuser et supprimer ce compte ?');">
                                <input type="hidden" name="id" value="<?= $compte['id_utilisateur'] ?>">
                                <input type="hidden" name="action" value="refuser">
                                <button type="submit" class="btn btn-refuser">Refuser</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>


</div>
</body>
</html>