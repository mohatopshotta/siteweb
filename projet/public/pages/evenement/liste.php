<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerMembre('../../');

$evenements = (new EvenementRepository())->listerTous();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Événements</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<h1>Événements</h1>

<?php if (Session::aLeRole(['medecin', 'partenaire'])): ?>
    <a href="creer.php">+ Créer un événement</a>
<?php endif; ?>

<?php if (empty($evenements)): ?>
    <p>Aucun événement pour le moment.</p>
<?php else: ?>
    <div class="liste-evenements">
        <?php foreach ($evenements as $evenement): ?>
            <div class="carte-evenement">
                <h2><?= htmlspecialchars($evenement->getTitre()) ?></h2>
                <p><em><?= htmlspecialchars($evenement->getTypeEvenement()) ?></em></p>
                <p><?= htmlspecialchars($evenement->getLieu()) ?></p>
                <p>
                    <?php if (!$evenement->estComplet()): ?>
                        <?= $evenement->getPlacesRestantes() ?> place(s) restante(s)
                    <?php else: ?>
                        Complet
                    <?php endif; ?>
                </p>
                <a href="detail.php?id=<?= $evenement->getIdEvenement() ?>">Voir le détail</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>