<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerConnexion('../../');

$repo = new EvenementRepository();

$mot           = trim($_GET['q'] ?? '');
$type          = trim($_GET['type'] ?? '');
$inclurePasses = isset($_GET['passes']);

$evenements = $repo->rechercher($mot, $type, $inclurePasses);
$types      = $repo->listerTypes();
$message    = Session::consommerFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Événements</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<h1>Événements</h1>

<?php if ($message): ?>
    <p class="message-succes"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<p>
    <a href="mes_evenements.php">Mes événements</a>
    <?php if (Session::aLeRole(['medecin', 'partenaire'])): ?>
        | <a href="creer.php">+ Créer un événement</a>
    <?php endif; ?>
</p>

<form method="get" action="liste.php" class="filtres">
    <input type="search" name="q" placeholder="Rechercher (titre, lieu, description)"
           value="<?= htmlspecialchars($mot) ?>">

    <select name="type">
        <option value="">Tous les types</option>
        <?php foreach ($types as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>" <?= $t === $type ? 'selected' : '' ?>>
                <?= htmlspecialchars($t) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>
        <input type="checkbox" name="passes" value="1" <?= $inclurePasses ? 'checked' : '' ?>>
        Voir aussi les événements passés
    </label>

    <button type="submit">Filtrer</button>
    <a href="liste.php">Réinitialiser</a>
</form>

<?php if (empty($evenements)): ?>
    <p>Aucun événement ne correspond.</p>
<?php else: ?>
    <div class="liste-evenements">
        <?php foreach ($evenements as $evenement): ?>
            <div class="carte-evenement">
                <h2><?= htmlspecialchars($evenement->getTitre()) ?></h2>
                <p><em><?= htmlspecialchars($evenement->getTypeEvenement()) ?></em></p>
                <p><?= htmlspecialchars($evenement->getDateAffichage()) ?></p>
                <p><?= htmlspecialchars($evenement->getLieu()) ?></p>
                <p>
                    <?php if ($evenement->estPasse()): ?>
                        <span class="badge badge-passe">Terminé</span>
                    <?php elseif ($evenement->estComplet()): ?>
                        <span class="badge badge-complet">Complet</span>
                    <?php else: ?>
                        <span class="badge badge-dispo"><?= $evenement->getPlacesRestantes() ?> place(s) restante(s)</span>
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