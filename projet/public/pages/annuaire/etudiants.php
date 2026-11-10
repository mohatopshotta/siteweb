<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerRole(['medecin', 'gestionnaire'], '../../');

$mot = trim($_GET['q'] ?? '');
$etudiants = (new UtilisateurRepository())->rechercherEtudiants($mot);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Annuaire des étudiants</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<h1>Annuaire des étudiants</h1>

<form method="get" action="etudiants.php" class="filtres">
    <input type="search" name="q" placeholder="Rechercher (nom, prénom, formation)" value="<?= htmlspecialchars($mot) ?>">
    <button type="submit">Rechercher</button>
    <a href="etudiants.php">Réinitialiser</a>
</form>

<p><?= count($etudiants) ?> étudiant(s)</p>

<?php if (empty($etudiants)): ?>
    <p>Aucun étudiant ne correspond.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Formation</th>
            <th>Établissement</th>
            <th>Email</th>
            <th>CV</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($etudiants as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e->getNomComplet()) ?></td>
                <td><?= htmlspecialchars($e->getFormation()) ?></td>
                <td><?= htmlspecialchars($e->getNomEtablissement() ?? '') ?></td>
                <td><?= htmlspecialchars($e->getEmail()) ?></td>
                <td>
                    <?php if ($e->getCv()): ?>
                        <a href="../../<?= htmlspecialchars($e->getCv()) ?>" target="_blank" rel="noopener">Voir le CV</a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>
