<?php
$racine = '../';
require $racine . 'includes/config.php';

$connecte = Session::estConnecte();
$role     = Session::getRole();

$evenements = (new EvenementManager())->listerTous(3);

$offres = (new OffreManager())->listerOuvertes(null, 3);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GDH — Hôpital Sud Paris</title>
    <link rel="stylesheet" href="<?= $racine ?>assets/css/css.css">
</head>
<body>

<header>
    <h1>La Générale des Hôpitaux (GDH)</h1>
    <p>Hôpital Sud Paris (HSP)</p>
</header>

<?php require $racine . 'includes/header.php'; ?>

<main>

    <section class="presentation">
        <h2>Bienvenue sur la plateforme GDH</h2>
        <p>
            Créée en 1954, La Générale des Hôpitaux (GDH) est le premier groupe de
            cliniques et hôpitaux privés en France, composé de neuf hôpitaux
            répartis sur le territoire. Cette plateforme connecte les médecins du
            groupe, les étudiants en médecine et les entreprises partenaires
            autour des offres, des événements et des échanges du forum.
        </p>
        <p>
            Inauguré en 1995, l'hôpital Sud Paris (HSP) est notre établissement de
            référence : chirurgie, médecine, cancérologie, maternité, imagerie
            médicale, urgences 24h/24 7j/7.
        </p>

        <?php if (!$connecte): ?>
            <p>
                <a href="<?= $racine ?>pages/connexion.php">Se connecter</a> ou
                <a href="<?= $racine ?>pages/inscription.php">créer un compte</a>
                pour accéder aux offres, aux événements et au forum.
            </p>
        <?php endif; ?>
    </section>

    <section class="actualites">
        <h2>Événements à la une</h2>

        <?php if (empty($evenements)): ?>
            <p>Aucun événement pour le moment.</p>
        <?php else: ?>
            <div class="liste-evenements">
                <?php foreach ($evenements as $evenement): ?>
                    <div class="carte-evenement">
                        <h3><?= htmlspecialchars($evenement->getTitre()) ?></h3>
                        <p><em><?= htmlspecialchars($evenement->getTypeEvenement()) ?></em></p>
                        <p><?= htmlspecialchars($evenement->getLieu()) ?></p>
                        <p>
                            <?php if (!$evenement->estComplet()): ?>
                                <?= $evenement->getPlacesRestantes() ?> place(s) restante(s)
                            <?php else: ?>
                                Complet
                            <?php endif; ?>
                        </p>
                        <?php if ($connecte && $role !== 'gestionnaire'): ?>
                            <a href="<?= $racine ?>pages/evenement/detail.php?id=<?= $evenement->getIdEvenement() ?>">Voir le détail</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($connecte && $role !== 'gestionnaire'): ?>
                <a href="<?= $racine ?>pages/evenement/liste.php">Voir tous les événements &rarr;</a>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <section class="actualites">
        <h2>Dernières offres</h2>

        <?php if (empty($offres)): ?>
            <p>Aucune offre ouverte pour le moment.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($offres as $offre): ?>
                    <li>
                        <?= htmlspecialchars($offre->getTitre()) ?>
                        (<?= htmlspecialchars($offre->getTypeOffre()) ?>)
                        <?php if ($connecte && $role !== 'gestionnaire'): ?>
                            — <a href="<?= $racine ?>pages/offres/detail.php?id=<?= $offre->getIdOffre() ?>">Voir</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($connecte && $role !== 'gestionnaire'): ?>
                <a href="<?= $racine ?>pages/offres/index.php">Voir toutes les offres &rarr;</a>
            <?php endif; ?>
        <?php endif; ?>
    </section>

</main>

</body>
</html>