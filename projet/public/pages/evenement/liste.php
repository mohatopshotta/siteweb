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
    <?php if (Session::aLeRole(['etudiant', 'medecin', 'partenaire'])): ?>
        <a href="mes_evenements.php">Mes événements</a>
    <?php endif; ?>
    <?php if (Session::aLeRole(['medecin', 'partenaire'])): ?>
        <?= Session::aLeRole(['etudiant', 'medecin', 'partenaire']) ? '|' : '' ?> <a href="creer.php">+ Créer un événement</a>
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
        <input type="checkbox"