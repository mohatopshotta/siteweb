<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerRole(['etudiant', 'medecin', 'gestionnaire'], '../../');

$mot = trim($_GET['q'] ?? '');
$idSpecialite = ctype_digit($_GET['specialite'] ?? '') ? (int)$_GET['specialite'] : null;

$medecins = (new UtilisateurRepository())->rechercherMedecins($mot, $idSpecialite);
$specialites = (new SpecialiteRepository())->listerTous();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Annuaire des médecins</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<h1>Annuaire des médecins</h1>

<form method="get" action="medecins.php" class="filtres">
    <input type="search" name="q" placeholder="Rechercher par nom" value="<?= htmlspecialchars($mot) ?>">

    <select name="specialite">
        <option value="">Toutes les spécialités</option>
        <?php foreach ($specialites as $s): ?>
            <option value="<?= $s->getIdSpecialite() ?>" <?= $idSpecialite === $s->getIdSpecialite() ? 'selected' : '' ?>>
                <?= htmlspecialchars($s->getLibelle()) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Rechercher</button>
    <a href="medecins.php">Réinitialiser</a>
</form>

<p><?= count($medecins) ?> médecin(s)</p>

<?php if (empty($medecins)): ?>
    <p>Aucun médecin ne correspond.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Spécialité</th>
            <th>Hôpital</th>
            <th>Établissement d'enseignement</th>
            <th>Email</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($medecins as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m->getNomComplet()) ?></td>
                <td><?= htmlspecialchars($m->getSpecialite() ?? '') ?></td>
                <td><?= htmlspecialchars($m->getHopital() ?? '') ?></td>
                <td><?= htmlspecialchars($m->getNomEtablissement() ?? '—') ?></td>
                <td><?= htmlspecialchars($m->getEmail()) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>
