<?php

require '../includes/config.php';

$connecte = isset($_SESSION['id_utilisateur']);
$role = $_SESSION['role'] ?? null;

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
    "SELECT o.id_offre, o.titre, o.type_offre, e.nom AS entreprise
     FROM offre o
     JOIN entreprise e ON e.id_entreprise = o.id_entreprise
     WHERE o.etat = 'ouvert'
     ORDER BY o.date_publication DESC
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
    <link rel="stylesheet" href="../assets/css/css.css">
</head>
<body>

<?php require __DIR__ . '/../includes/header.php'; ?>

<main>

    <section class="presentation">
        <?php if ($connecte): ?>
            <h1>Bonjour <?= htmlspecialchars($_SESSION['prenom']) ?></h1>
        <?php else: ?>
            <h1>Bienvenue sur la plateforme GDH</h1>
        <?php endif; ?>

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
            <div class="boutons">
                <a class="bouton" href="../pages/inscription.php">Créer un compte</a>
                <a class="bouton bouton-secondaire" href="../pages/connexion.php">Se connecter</a>
            </div>
        <?php elseif ($role === 'gestionnaire'): ?>
            <div class="boutons">
                <a class="bouton" href="../pages/admin/admin.php">Aller à l'administration</a>
                <a class="bouton bouton-secondaire" href="../pages/admin/valider_compte.php">Valider les comptes</a>
            </div>
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

                        <?php if (!$connecte): ?>
                            <a href="../pages/connexion.php">Connectez-vous pour vous inscrire</a>
                        <?php elseif ($role !== 'gestionnaire'): ?>
                            <a href="../pages/evenement/detail.php?id=<?= $evenement['id_evenement'] ?>">Voir le détail</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($connecte && $role !== 'gestionnaire'): ?>
                <a href="../pages/evenement/liste.php">Voir tous les événements</a>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <section class="dernieres-offres">
        <h2>Dernières offres</h2>

        <?php if (empty($offres)): ?>
            <p>Aucune offre ouverte pour le moment.</p>
        <?php else: ?>
            <div class="liste-offres">
                <?php foreach ($offres as $offre): ?>
                    <div class="carte-offre">
                        <h3><?= htmlspecialchars($offre['titre']) ?></h3>
                        <p><?= htmlspecialchars($offre['entreprise']) ?></p>
                        <p><?= strtoupper(htmlspecialchars($offre['type_offre'])) ?></p>
                        <a href="../pages/offres/detail.php?id=<?= $offre['id_offre'] ?>">Voir l'offre</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="../pages/offres/index.php">Voir toutes les offres</a>
        <?php endif; ?>
    </section>

</main>

</body>
</html>