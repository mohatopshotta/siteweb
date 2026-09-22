<?php
session_start();
require '../../includes/config.php';

if (!isset($_SESSION['id_utilisateur']) || $_SESSION['profil'] === 'gestionnaire') {
    header('Location: ../connexion.php');
    exit;
}

// ------------------------------------------------------------
// Récupération de tous les événements + places restantes
// ------------------------------------------------------------
$stmt = $pdo->query(
    "SELECT e.*, COUNT(ie.id_utilisateur) AS places_prises
     FROM evenement e
     LEFT JOIN inscription_evenement ie ON ie.id_evenement = e.id_evenement
     GROUP BY e.id_evenement
     ORDER BY e.id_evenement DESC"
);
$evenements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Événements</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<h1>Événements</h1>

<?php if (in_array($_SESSION['profil'], ['medecin', 'partenaire'])): ?>
    <a href="creer.php">+ Créer un événement</a>
<?php endif; ?>

<?php if (empty($evenements)): ?>
    <p>Aucun événement pour le moment.</p>
<?php else: ?>
    <div class="liste-evenements">
        <?php foreach ($evenements as $evenement):
            $places_restantes = $evenement['nombre_places'] - $evenement['places_prises'];
            ?>
            <div class="carte-evenement">
                <h2><?= htmlspecialchars($evenement['titre']) ?></h2>
                <p><em><?= htmlspecialchars($evenement['type_evenement']) ?></em></p>
                <p><?= htmlspecialchars($evenement['lieu']) ?></p>
                <p>
                    <?php if ($places_restantes > 0): ?>
                        <?= $places_restantes ?> place(s) restante(s)
                    <?php else: ?>
                        Complet
                    <?php endif; ?>
                </p>
                <a href="detail.php?id=<?= $evenement['id_evenement'] ?>">Voir le détail</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</body>
</html>
