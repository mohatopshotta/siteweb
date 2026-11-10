<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerMembre('../../');

$evenements = (new EvenementRepository())->listerPourUtilisateur(Session::getIdUtilisateur());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes événements</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<a href="liste.php">&larr; Tous les événements</a>

<h1>Mes événements</h1>

<?php if (empty($evenements)): ?>
    <p>Tu n'es inscrit(e) à aucun événement pour le moment.</p>
<?php else: ?>
    <div class="liste-evenements">
        <?php foreach ($evenements as $evenement): ?>
            <div class="carte-evenement">
                <h2><?= htmlspecialchars($evenement->getTitre()) ?></h2>
                <p><em><?= htmlspecialchars($evenement->getTypeEvenement()) ?></em></p>
                <p><?= htmlspecialchars($evenement->getDateAffichage()) ?></p>
                <p><?= htmlspecialchars($evenement->getLieu()) ?></p>
                <p>
                    <?php if ($evenement->estOrganisateur()): ?>
                        <span class="badge badge-orga">Organisateur</span>
                    <?php else: ?>
                        <span class="badge badge-dispo">Inscrit(e)</span>
                    <?php endif; ?>
                    <?php if ($evenement->estPasse()): ?>
                        <span class="badge badge-passe">Terminé</span>
                    <?php endif; ?>
                </p>
                <a href="detail.php?id=<?= $evenement->getIdEvenement() ?>">Voir le détail</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>x²