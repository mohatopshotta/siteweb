<?php
require __DIR__ . '/../../includes/config.php';

// réservé aux membres (pas au gestionnaire)
Session::exigerMembre('../../');

$manager = new EvenementManager();
$id_utilisateur = Session::getIdUtilisateur();
$id_evenement = (int)($_GET['id'] ?? 0);

$evenement = $manager->trouverParId($id_evenement);
if ($evenement === null) {
    die("Événement introuvable.");
}

$erreur = '';

// ------------------------------------------------------------
// Inscription / désinscription
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inscrire'])) {
        $erreur = $manager->inscrire($id_utilisateur, $evenement);
    } elseif (isset($_POST['desinscrire'])) {
        $manager->retirerParticipant($id_utilisateur, $id_evenement);
    }

    if ($erreur === '') {
        Session::rediriger('detail.php?id=' . $id_evenement);
    }
}

// null = pas inscrit ; sinon participant ou organisateur
$inscription = $manager->trouverInscription($id_utilisateur, $id_evenement);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($evenement->getTitre()) ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<a href="liste.php">&larr; Tous les événements</a>

<h1><?= htmlspecialchars($evenement->getTitre()) ?></h1>
<p><em><?= htmlspecialchars($evenement->getTypeEvenement()) ?></em></p>

<p><strong>Lieu :</strong> <?= htmlspecialchars($evenement->getLieu()) ?></p>
<p><?= nl2br(htmlspecialchars($evenement->getDescription())) ?></p>

<?php if ($evenement->getElementsRequis() !== null): ?>
    <h2>Éléments requis</h2>
    <p><?= nl2br(htmlspecialchars($evenement->getElementsRequis())) ?></p>
<?php endif; ?>

<p>
    <strong>Places :</strong>
    <?= $evenement->getPlacesPrises() ?> / <?= $evenement->getNombrePlaces() ?>
    <?php if ($evenement->estComplet()): ?>
        — Complet
    <?php else: ?>
        — <?= $evenement->getPlacesRestantes() ?> place(s) restante(s)
    <?php endif; ?>
</p>

<?php if ($erreur): ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<?php if ($inscription !== null && $inscription->estOrganisateur()): ?>
    <p>Tu es organisateur de cet événement.</p>
    <a href="gerer.php?id=<?= $evenement->getIdEvenement() ?>">Gérer l'événement et les inscrits</a>

<?php elseif ($inscription !== null): ?>
    <p>Tu es inscrit(e) à cet événement.</p>
    <form method="post">
        <button type="submit" name="desinscrire" onclick="return confirm('Se désinscrire de cet événement ?');">
            Se désinscrire
        </button>
    </form>

<?php elseif (!$evenement->estComplet()): ?>
    <form method="post">
        <button type="submit" name="inscrire">S'inscrire</button>
    </form>
<?php endif; ?>

</body>
</html>
