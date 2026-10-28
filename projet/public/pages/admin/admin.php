<?php
require_once __DIR__ . '/../../includes/config.php';

Session::exigerRole(['gestionnaire'], '../../');

$repository = new UtilisateurRepository();

$nbValides     = $repository->compterValides();
$nbEtudiants   = $repository->compterParRole('etudiant');
$nbMedecins    = $repository->compterParRole('medecin');
$nbPartenaires = $repository->compterParRole('partenaire');
$nbEnAttente   = $repository->compterEnAttente();

$comptesEnAttente = $repository->listerEnAttente();

// RGPD : comptes à supprimer car inactifs depuis trop longtemps
$anneesConservation = InfosSite::ANNEES_CONSERVATION_INACTIF;
$nbInactifs = $repository->compterInactifs($anneesConservation);

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'valide') {
        $message = "Le compte a été validé.";
    } elseif ($_GET['msg'] === 'refuse') {
        $message = "Le compte a été refusé et supprimé.";
    } elseif ($_GET['msg'] === 'inactifs') {
        $nbSupprimes = (int)($_GET['nb'] ?? 0);
        $message = $nbSupprimes . " compte(s) inactif(s) supprimé(s).";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - HSP GDH</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<div class="admin-container">
    <h1>Tableau de bord - Administration</h1>

    <?php if ($message): ?>
        <div class="alerte alerte-succes"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

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
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($comptesEnAttente as $compte): ?>
                    <tr>
                        <td><?= htmlspecialchars($compte->getNom()) ?></td>
                        <td><?= htmlspecialchars($compte->getPrenom()) ?></td>
                        <td><?= htmlspecialchars($compte->getEmail()) ?></td>
                        <td><?= htmlspecialchars($compte->getLibelleRole()) ?></td>
                        <td class="admin-actions">
                            <form action="valider_compte.php" method="POST" class="form-inline">
                                <?= Session::champCsrf() ?>
                                <input type="hidden" name="id" value="<?= $compte->getIdUtilisateur() ?>">
                                <input type="hidden" name="action" value="valider">
                                <button type="submit" class="btn btn-valider">Valider</button>
                            </form>

                            <form action="valider_compte.php" method="POST" class="form-inline"
                                  onsubmit="return confirm('Refuser et supprimer ce compte ?');">
                                <?= Session::champCsrf() ?>
                                <input type="hidden" name="id" value="<?= $compte->getIdUtilisateur() ?>">
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

    <section class="admin-section">
        <h2>RGPD : comptes inactifs</h2>
        <p>
            Comptes sans connexion depuis plus de <?= $anneesConservation ?> ans (gestionnaires exclus) :
            <strong><?= $nbInactifs ?></strong>
        </p>

        <?php if ($nbInactifs > 0): ?>
            <form action="supprimer_inactifs.php" method="POST"
                  onsubmit="return confirm('Supprimer définitivement <?= $nbInactifs ?> compte(s) inactif(s) et toutes leurs données ?');">
                <?= Session::champCsrf() ?>
                <button type="submit" class="btn btn-refuser">Supprimer les comptes inactifs (RGPD)</button>
            </form>
        <?php else: ?>
            <p class="alerte alerte-info">Aucun compte à supprimer pour le moment.</p>
        <?php endif; ?>
    </section>

</div>
<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>