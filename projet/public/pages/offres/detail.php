<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerMembre('../../');

$id_utilisateur = Session::getIdUtilisateur();
$id_offre = (int)($_GET['id'] ?? 0);

$offre = (new OffreManager())->trouverParId($id_offre);
if ($offre === null) {
    die("NewOffre introuvable.");
}

$estAuteur = $offre->getIdAuteur() === $id_utilisateur;

$peutPostuler = Session::aLeRole(['etudiant', 'medecin']);
$dejaPostule = (new CandidatureManager())->aDejaPostule($id_utilisateur, $id_offre);

$erreur = $_GET['erreur'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($offre->getTitre()) ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<a href="index.php">&larr; Toutes les offres</a>

<h1><?= htmlspecialchars($offre->getTitre()) ?></h1>
<p>
    <?= htmlspecialchars($offre->getEntreprise()) ?> · <?= strtoupper($offre->getTypeOffre()) ?>
    <?= $offre->getSalaire() !== null ? ' · ' . number_format($offre->getSalaire(), 2, ',', ' ') . ' €' : '' ?>
    · Publiée par <?= htmlspecialchars($offre->getNomAuteur()) ?>
</p>
<p><?= nl2br(htmlspecialchars($offre->getDescription())) ?></p>

<h2>Missions</h2>
<p><?= nl2br(htmlspecialchars($offre->getMissions())) ?></p>

<?php if (isset($_GET['ok'])): ?>
    <p style="color:green;">Ta candidature a bien été envoyée.</p>
<?php endif; ?>

<?php if ($erreur): ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<?php if ($estAuteur): ?>
    <p><a href="gerer.php?id=<?= $offre->getIdOffre() ?>">Gérer cette offre et voir les candidatures</a></p>

<?php elseif (!$offre->estOuverte()): ?>
    <p><strong>Offre clôturée.</strong></p>

<?php elseif ($dejaPostule): ?>
    <p>Tu as déjà postulé à cette offre.</p>

<?php elseif ($peutPostuler): ?>
    <h2>Postuler</h2>
    <form method="post" action="postuler.php">
        <input type="hidden" name="id_offre" value="<?= $offre->getIdOffre() ?>">
        <label>Lettre de motivation</label><br>
        <textarea name="motivation" required rows="8" cols="60"></textarea><br>
        <button type="submit">Postuler</button>
    </form>
<?php endif; ?>

</body>
</html>
