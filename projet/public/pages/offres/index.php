<?php
require_once __DIR__ . '/../../includes/config.php';

Session::exigerMembre('../../');

$racine = '../../';

$offreRepository = new OffreRepository();

$type = $_GET['type'] ?? '';
$offres = $offreRepository->listerOuvertes($type);

$offres_entreprise = [];
if (Session::aLeRole(['partenaire'])) {
    $partenaire = (new UtilisateurRepository())->trouverParId(Session::getIdUtilisateur());
    $offres_entreprise = $offreRepository->listerParEntreprise($partenaire->getIdEntreprise());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos offres d'emploi</title>
    <link rel="stylesheet" href="<?= $racine ?>assets/css/css.css">
</head>

<body>

<?php require __DIR__ . '/../../includes/header.php'; ?>

<div class="container">

    <header class="entete">
        <h1>Découvrez toutes nos offres !</h1>
        <p>
            Vous êtes à la recherche d'un stage, d'une alternance
            ou d'un emploi ? Consultez toutes nos opportunités et
            choisissez l'offre qui correspond à votre projet professionnel.
        </p>
        <p>Votre prochaine opportunité commence ici !</p>
    </header>

    <?php if (Session::aLeRole(['partenaire'])): ?>
        <section class="filtre">
            <h2>Offres de mon entreprise</h2>
            <?php if (empty($offres_entreprise)): ?>
                <p>Aucune offre publiée par ton entreprise.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($offres_entreprise as $o): ?>
                        <li>
                            <a href="detail.php?id=<?= $o->getIdOffre() ?>"><?= htmlspecialchars($o->getTitre()) ?></a>
                            (<?= $o->estOuverte() ? 'ouverte' : 'clôturée' ?>)
                            — publiée par <?= htmlspecialchars($o->getNomAuteur()) ?>
                            <?php if ($o->getIdAuteur() === Session::getIdUtilisateur()): ?>
                                — <a href="gerer.php?id=<?= $o->getIdOffre() ?>">Gérer</a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <a href="creer.php">+ Créer une offre</a>
        </section>
    <?php endif; ?>

    <section class="filtre">
        <h2>Rechercher une offre</h2>

        <form method="get">
            <select name="type" onchange="this.form.submit()">
                <option value="">Tous les types de contrats</option>

                <?php foreach (NewOffre::TYPES as $t): ?>
                    <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>>
                        <?= strtoupper($t) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </section>

    <h2>Nos offres disponibles</h2>
    <p><?= count($offres) ?> offre(s) disponible(s)</p>

    <?php if (empty($offres)): ?>

        <div class="vide">
            <h3>Aucune offre disponible</h3>
            <p>De nouvelles opportunités seront bientôt proposées.</p>
        </div>

    <?php else: ?>

        <div class="offres">
            <?php foreach ($offres as $o): ?>
                <article class="offre">

                    <h2>
                        <a href="detail.php?id=<?= $o->getIdOffre() ?>">
                            <?= htmlspecialchars($o->getTitre()) ?>
                        </a>
                    </h2>

                    <p class="entreprise"><?= htmlspecialchars($o->getEntreprise()) ?></p>

                    <span class="badge"><?= strtoupper(htmlspecialchars($o->getTypeOffre())) ?></span>

                    <?php if ($o->getSalaire() !== null): ?>
                        <p>Salaire : <?= number_format($o->getSalaire(), 2, ',', ' ') ?> €</p>
                    <?php endif; ?>

                    <p>Publiée le <?= date('d/m/Y', strtotime($o->getDatePublication())) ?></p>

                    <a href="detail.php?id=<?= $o->getIdOffre() ?>">Voir les détails et postuler</a>

                </article>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/' . $racine . 'includes/footer.php'; ?>
</body>
</html>