<?php
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION['id_utilisateur']) || $_SESSION['profil'] === 'gestionnaire') {
    header('Location: ../connexion.php');
    exit;
}

$sections = $pdo->query(
    "SELECT s.id_section, s.nom, COUNT(p.id_post) AS nb_posts
     FROM section_forum s
     LEFT JOIN post p ON p.id_section = s.id_section
     GROUP BY s.id_section, s.nom
     ORDER BY s.id_section"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forum</title>
    <link rel="stylesheet" href="../../assets/css/css.css">
</head>
<body>

<?php $racine = '../../'; require __DIR__ . '/../../includes/header.php'; ?>

<h1>Forum</h1>
<p>Choisissez une section pour consulter ou créer une discussion.</p>

<?php foreach ($sections as $s): ?>
    <div class="carte-forum">
        <h3><?= htmlspecialchars($s['nom']) ?></h3>
        <p><?= (int)$s['nb_posts'] ?> discussion(s)</p>
        <?php if ((int)$s['id_section'] === 2): ?>
            <p>Réservée aux médecins et partenaires</p>
        <?php elseif ((int)$s['id_section'] === 3): ?>
            <p>Ouverte aux étudiants et médecins (création réservée aux étudiants)</p>
        <?php else: ?>
            <p>Ouverte à tous les membres</p>
        <?php endif; ?>
        <a href="categorie.php?id=<?= (int)$s['id_section'] ?>">Accéder</a>
    </div>
<?php endforeach; ?>

<p><a href="mes_posts.php">Mes discussions et réponses</a></p>

</body>
</html>
