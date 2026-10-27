<?php
// Page d'accueil : à placer dans projet/public/accueil/accueil.php
$racine = '../';
require $racine .'includes/config.php';
/** @var PDO $pdo */

$connecte = isset($_SESSION['id_utilisateur']);
$profil   = $_SESSION['profil'] ?? '';

// ------------------------------------------------------------
// Événements à la une (les 3 plus récents)
// ------------------------------------------------------------
$stmt = $pdo->query(
    "SELECT e.*, COUNT(ie.id_utilisateur) AS places_prises
     FROM evenement e
     LEFT JOIN inscription_evenement ie ON ie.id_evenement = e.id_evenement
     GROUP BY e.id_evenement
     ORDER BY e.id_evenement DESC
     LIMIT 3"
);
$evenements = $stmt->fetchAll();

// ------------------------------------------------------------
// Dernières offres ouvertes (les 3 plus récentes)
// ------------------------------------------------------------
$stmt = $pdo->query(
    "SELECT id_offre, titre, type_offre
     FROM offre
     WHERE etat = 'ouverte'
     ORDER BY date_publication DESC
     LIMIT 3"
);
$offres = $stmt->fetchAll();
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
=======
<?php $racine = '../'; require __DIR__ . '/../includes/header.php'; ?>
>>>>>>> 0bf18af3025613363570b37c544d953b076c683d

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
                <?php foreach ($evenements as $evenement):
                    $places_restantes = $evenement['nombre_places'] - $evenement['places_prises'];
                    ?>
                    <div class="carte-evenement">
                        <h3><?= htmlspecialchars($evenement['titre']) ?></h3>
                        <p><em><?= htmlspecialchars($evenement['type_evenement']) ?></em></p>
                        <p><?= htmlspecialchars($evenement['lieu']) ?></p>
                        <p>
                            <?php if ($places_restantes > 0): ?>
                                <?= $places_restantes ?> place(s) restante(s)
                            <?php else: ?>
                                Complet
                            <?php endif; ?>
                        </p>
                        <?php if ($connecte && $profil !== 'gestionnaire'): ?>
                            <a href="<?= $racine ?>pages/evenement/detail.php?id=<?= (int)$evenement['id_evenement'] ?>">Voir le détail</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($connecte && $profil !== 'gestionnaire'): ?>
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
                        <?= htmlspecialchars($offre['titre']) ?>
                        (<?= htmlspecialchars($offre['type_offre']) ?>)
                        <?php if ($connecte && $profil !== 'gestionnaire'): ?>
                            — <a href="<?= $racine ?>pages/offres/detail.php?id=<?= (int)$offre['id_offre'] ?>">Voir</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($connecte && $profil !== 'gestionnaire'): ?>
                <a href="<?= $racine ?>pages/offres/index.php">Voir toutes les offres &rarr;</a>
            <?php endif; ?>
        <?php endif; ?>
    </section>

</main>

</body>
</html>