<?php
require __DIR__ . '/../../includes/config.php';

// Lecture ouverte à tout utilisateur connecté, gestionnaire compris (il ne peut pas agir)
Session::exigerConnexion('../../');

$id_utilisateur = Session::getIdUtilisateur();
$id_offre = (int)($_GET['id'] ?? 0);

$offre = (new OffreRepository())->trouverParId($id_offre);
if ($offre === null) {
    http_response_code(404);
    die("Offre introuvable.");
}

$estAuteur = (int)$offre->getIdAuteur() === (int)$id_utilisateur;

$peutPostuler = Session::aLeRole(['etudiant', 'medecin']);
$dejaPostule = (new CandidatureRepository())->aDejaPostule($id_utilisateur, $id_offre);

// CV actuel de l'étudiant (celui de son profil), proposé avec sa candidature
$cv_actuel = null;
if (Session::aLeRole(['etudiant'])) {
    $etudiant = (new UtilisateurRepository())->trouverParId($id_utilisateur);
    $cv_actuel = $etudiant instanceof NewEtudiant ? $etudiant->getCv() : null;
}

$erreur = $_GET['erreur'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($offre->getTitre()) ?></title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<a href="index.php">&larr; Toutes les offres</a>

<h1><?= htmlspecialchars($offre->getTitre()) ?></h1>
<p>
    <?= htmlspecialchars($offre->getEntreprise()) ?> · <?= htmlspecialchars(strtoupper($offre->getTypeOffre())) ?>
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
    <form method="post" action="postuler.php" enctype="multipart/form-data">
        <?= Session::champCsrf() ?>
        <input type="hidden" name="id_offre" value="<?= $offre->getIdOffre() ?>">
        <label for="motivation">Lettre de motivation</label><br>
        <textarea id="motivation" name="motivation" required rows="8" cols="60"></textarea><br>

        <?php if (Session::aLeRole(['etudiant'])): ?>
            <label for="cv">CV (PDF, 2 Mo maximum)</label>
            <?php if ($cv_actuel): ?>
                <p>
                    Ton CV actuel sera envoyé avec ta candidature :
                    <a href="../../<?= htmlspecialchars($cv_actuel) ?>" target="_blank" rel="noopener">voir mon CV</a>.
                    Tu peux en joindre un autre ci-dessous (il remplacera celui de ton profil).
                </p>
            <?php else: ?>
                <p>Tu n'as pas encore de CV : joins-le ici pour que l'entreprise puisse le consulter.</p>
            <?php endif; ?>
            <input type="file" id="cv" name="cv" accept="application/pdf"><br>
        <?php endif; ?>

        <button type="submit">Postuler</button>
    </form>
<?php endif; ?>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>