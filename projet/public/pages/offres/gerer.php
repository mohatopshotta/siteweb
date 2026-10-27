<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerRole(['partenaire'], '../../');

$id_utilisateur = Session::getIdUtilisateur();
$id_offre = (int)($_GET['id'] ?? 0);

$offreManager = new OffreManager();
$candidatureManager = new CandidatureManager();

$offre = $offreManager->trouverParId($id_offre);
if ($offre === null) {
    die("Offre introuvable.");
}

if ($offre->getIdAuteur() !== $id_utilisateur) {
    die("Tu n'es pas l'auteur de cette offre.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'] ?? '';
    $id_candidat = (int)($_POST['id_candidat'] ?? 0);

    if ($action === 'bloquer') {
        $offreManager->changerEtat($id_offre, Offre::CLOTURE, $id_utilisateur);
    } elseif ($action === 'rouvrir') {
        $offreManager->changerEtat($id_offre, Offre::OUVERT, $id_utilisateur);
    } elseif ($action === 'retenir') {
        $candidatureManager->retenir($id_offre, $id_candidat);
    } elseif ($action === 'refuser') {
        $candidatureManager->refuser($offre, $id_candidat);
    }

    Session::rediriger('gerer.php?id=' . $id_offre);
}

$candidats = $candidatureManager->listerParOffre($id_offre);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer l'offre</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<h1><?= htmlspecialchars($offre->getTitre()) ?></h1>
<p>
    <?= htmlspecialchars($offre->getEntreprise()) ?> ·
    État : <strong><?= $offre->estOuverte() ? 'ouverte' : 'bloquée' ?></strong> ·
    <a href="detail.php?id=<?= $id_offre ?>">voir l'offre</a>
</p>

<form method="post">
    <?php if ($offre->estOuverte()): ?>
        <button type="submit" name="action" value="bloquer">Bloquer l'offre</button>
    <?php else: ?>
        <button type="submit" name="action" value="rouvrir">Rouvrir l'offre</button>
    <?php endif; ?>
</form>

<h2>Candidatures (<?= count($candidats) ?>)</h2>

<?php if (empty($candidats)): ?>
    <p>Aucune candidature pour le moment.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Candidat</th>
            <th>Email</th>
            <th>Lettre de motivation</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($candidats as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c->getNomCandidat()) ?></td>
                <td><?= htmlspecialchars($c->getEmail()) ?></td>
                <td><?= nl2br(htmlspecialchars($c->getMotivation())) ?></td>
                <td><?= htmlspecialchars($c->getLibelleStatut()) ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="id_candidat" value="<?= $c->getIdUtilisateur() ?>">
                        <button type="submit" name="action" value="retenir">Retenir</button>
                        <button type="submit" name="action" value="refuser">Refuser</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</body>
</html>