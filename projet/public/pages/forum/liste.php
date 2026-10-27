<?php
require_once __DIR__ . '/../../includes/config.php';

Session::exigerMembre('../../');

// sections du forum avec leur nombre de discussions
$sections = (new SectionForumManager())->listerAvecNbPosts();
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
        <h3><?= htmlspecialchars($s->getNom()) ?></h3>
        <p><?= $s->getNbPosts() ?> discussion(s)</p>
        <p><?= htmlspecialchars($s->getDescriptionAcces()) ?></p>
        <a href="categorie.php?id=<?= $s->getIdSection() ?>">Accéder</a>
    </div>
<?php endforeach; ?>

<p><a href="mes_posts.php">Mes discussions et réponses</a></p>

</body>
</html>
