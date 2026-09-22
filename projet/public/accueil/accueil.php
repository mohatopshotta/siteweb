<?php
session_start();
require '../includes/config.php';

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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>GDH — Hôpital Sud Paris</title>
    <link rel="stylesheet" href="../assets/css/css.css">
</head>
<body>

<header>
    <h1>La Générale des Hôpitaux (GDH)</h1>
    <nav>
        <?php if (isset($_SESSION['id_utilisateur'])): ?>
            <a href="../pages/profil.php">Mon profil</a>
            <a href="../pages/deconnexion.php">Déconnexion</a>
        <?php else: ?>
            <a href="../pages/connexion.php">Connexion</a>
            <a href="../pages/inscription.php">Inscription</a>
        <?php endif; ?>
    </nav>
</header>

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
                    <a href="../pages/evenements/detail.php?id=<?= $evenement['id_evenement'] ?>">Voir le détail</a>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="../pages/evenements/liste.php">Voir tous les événements &rarr;</a>
    <?php endif; ?>
</section>

</body>
</html>
