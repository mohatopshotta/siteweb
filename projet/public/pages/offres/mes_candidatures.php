<?php
// ============================================================
// pages/offres/mes_candidatures.php
// Liste des candidatures envoyées par l'utilisateur connecté,
// avec leur statut : en attente, retenue ou non retenue.
// Réservé aux étudiants et aux médecins (ceux qui peuvent postuler).
// ============================================================
require __DIR__ . '/../../includes/config.php';

Session::exigerRole(['etudiant', 'medecin'], '../../');

$candidatures = (new CandidatureRepository())->listerParCandidat(Session::getIdUtilisateur());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes candidatures</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<a href="index.php">&larr; Toutes les offres</a>

<h1>Mes candidatures</h1>

<?php if (empty($candidatures)): ?>
    <p>Tu n'as envoyé aucune candidature pour le moment.</p>
    <p><a href="index.php">Voir les offres</a></p>
<?php else: ?>
    <p><?= count($candidatures) ?> candidature(s) envoyée(s).</p>

    <table>
        <thead>
            <tr>
                <th>Offre</th>
                <th>Type</th>
                <th>Entreprise</th>
                <th>Envoyée le</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($candidatures as $c): ?>
                <tr>
                    <td>
                        <a href="detail.php?id=<?= $c->getIdOffre() ?>"><?= htmlspecialchars($c->getTitreOffre() ?? '') ?></a>
                        <?php if ($c->getEtatOffre() === NewOffre::CLOTURE): ?>
                            <span class="badge badge-passe">Offre clôturée</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($c->getTypeOffre() ?? '') ?></td>
                    <td><?= htmlspecialchars($c->getEntreprise() ?? '—') ?></td>
                    <td><?= htmlspecialchars($c->getDateCandidatureFormatee()) ?></td>
                    <td>
                        <span class="badge <?= $c->getClasseBadge() ?>"><?= htmlspecialchars(ucfirst($c->getLibelleStatut())) ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

</body>
</html>
